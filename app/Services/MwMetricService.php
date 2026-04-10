<?php

namespace App\Services;

use App\Models\MissionWay\RefMetricDefinition;
use App\Models\MissionWay\RefSimulationMetricBand;
use App\Models\MissionWay\RefMetricBandCategory;
use Illuminate\Support\Collection;

/**
 * Port of NestJS SimulationPathService.enrichMetrics() — L171-L267
 *
 * Resolves raw metric JSON (from mw_simulation_sessions.final_metrics)
 * into enriched structures with icons, colors, band categories.
 */
class MwMetricService
{
    private ?Collection $definitions = null;

    /**
     * Enrich a session's final_metrics JSON to display-ready metric array.
     *
     * Input:  {"health":{"base":100,"change":-25,"current":75}, "resource":{...}, ...}
     *   or:   {"health":75, "resource":60, ...}
     *
     * Output: [
     *   ['key'=>'health', 'name'=>'Health Point', 'current'=>75, 'base'=>100,
     *    'change'=>-25, 'icon'=>'❤️', 'color'=>'#EF4444', 'trend'=>'down',
     *    'categoryKey'=>'moderate', 'categoryColor'=>'#F59E0B'],
     *   ...
     * ]
     */
    /**
     * Enrich a session's final_metrics JSON to display-ready metric array.
     *
     * Input:  [{key:"health", base:100, current:75, change:-25}, ...]  (array-of-objects from API)
     *   or:   {"health":{base:100, current:75, change:-25}, ...}        (associative)
     *   or:   {"health":75, ...}                                        (scalar)
     *
     * Output: [['key'=>'health', 'name'=>'Health Point', 'current'=>75, ...], ...]
     */
    public function enrichSessionMetrics(?array $finalMetrics, ?int $versionId = null): array
    {
        if (!$finalMetrics || empty($finalMetrics)) {
            return [];
        }

        // Normalize: if array-of-objects (numeric keys with 'key' inside), re-key by key
        $normalized = $this->normalizeMetrics($finalMetrics);
        if (empty($normalized)) return [];

        $definitions = $this->getDefinitions();

        $bands = $versionId
            ? RefSimulationMetricBand::where('simulation_version_id', $versionId)
                ->with(['metricDefinition', 'category'])
                ->get()
            : collect();

        $result = [];
        foreach ($normalized as $key => $metricData) {
            // Map 'compliance' → 'adaptation' for UI consistency
            $displayKey = ($key === 'compliance') ? 'adaptation' : $key;

            // Normalize: value can be array {base, change, current} or scalar
            if (is_array($metricData)) {
                $current = $metricData['current'] ?? $metricData['value'] ?? 0;
                $base = $metricData['base'] ?? 100;
                $change = $metricData['change'] ?? 0;
            } else {
                $current = is_numeric($metricData) ? (float) $metricData : 0;
                $base = 100;
                $change = 0;
            }

            $def = $definitions->get($key) ?? $definitions->get($displayKey);

            // Band matching: find band where current falls within [minValue, maxValue]
            $matchedBand = $bands->first(function ($band) use ($key, $current) {
                $bandKey = $band->metricDefinition?->key ?? $band->metricDefinition?->metric_key ?? $band->metric_key;
                return $bandKey === $key
                    && $current >= $band->min_value
                    && $current <= $band->max_value;
            });

            $result[] = [
                'key'           => $displayKey,
                'name'          => $metricData['name'] ?? $def?->name ?? (ucfirst($displayKey) . ' Point'),
                'current'       => round($current),
                'base'          => round($base),
                'change'        => round($change),
                'icon'          => $metricData['icon'] ?? $def?->icon ?? '📊',
                'color'         => $metricData['color'] ?? $def?->color ?? '#6B7280',
                'unitLabel'     => $metricData['unitLabel'] ?? $def?->unit_label ?? null,
                'trend'         => $change >= 0 ? 'up' : 'down',
                'categoryKey'   => $metricData['categoryKey'] ?? $matchedBand?->category?->key ?? null,
                'categoryColor' => $metricData['categoryColor'] ?? $matchedBand?->category?->color ?? null,
                'categoryLabel' => $matchedBand?->category?->label ?? null,
            ];
        }

        return $result;
    }

    /**
     * Normalize metric data from any format to associative {key => data}.
     *
     * Handles:
     *   [{key:"health", current:75, ...}, ...]  → {health: {current:75,...}, ...}
     *   {health: {current:75}, ...}              → passthrough
     *   {health: 75, ...}                        → passthrough
     */
    private function normalizeMetrics(array $metrics): array
    {
        // Check if it's array-of-objects (numeric keys, each item has 'key')
        if (isset($metrics[0]) && is_array($metrics[0]) && isset($metrics[0]['key'])) {
            $normalized = [];
            foreach ($metrics as $item) {
                $key = $item['key'] ?? null;
                if (!$key) continue;
                $normalized[$key] = $item;
            }
            return $normalized;
        }

        return $metrics;
    }

    /**
     * Extract a specific metric value from enriched array by key.
     *
     * @param array  $enriched  Result of enrichSessionMetrics()
     * @param string $key       e.g. 'health', 'resource', 'ethics', 'adaptation'
     * @param string $field     Field to extract: 'current', 'trend', 'icon', 'color', etc.
     */
    public function getMetricValue(array $enriched, string $key, string $field = 'current'): mixed
    {
        foreach ($enriched as $metric) {
            if (($metric['key'] ?? '') === $key) {
                return $metric[$field] ?? null;
            }
        }
        return null;
    }

    /**
     * Shortcut: extract all 4 standard metric values as flat array.
     * Returns ['health' => 75, 'resource' => 60, 'ethics' => 85, 'adaptation' => 90]
     */
    public function getAllMetricValues(array $enriched, string $field = 'current'): array
    {
        $result = [];
        foreach (['health', 'resource', 'ethics', 'adaptation'] as $key) {
            $result[$key] = $this->getMetricValue($enriched, $key, $field);
        }
        return $result;
    }

    /**
     * Compute aggregated metrics from multiple sessions' final_metrics.
     * Returns averaged enriched metrics.
     */
    public function aggregateSessionMetrics(Collection $sessions, ?int $versionId = null): array
    {
        $accumulator = [];
        $count = 0;

        foreach ($sessions as $session) {
            $fm = $session->final_metrics ?? [];
            if (empty($fm)) continue;

            // Normalize from any format
            $normalized = $this->normalizeMetrics($fm);
            if (empty($normalized)) continue;

            $count++;
            foreach (['health', 'resource', 'ethics', 'compliance', 'adaptation', 'overall'] as $mk) {
                if (!isset($normalized[$mk])) continue;
                $val = is_array($normalized[$mk])
                    ? ($normalized[$mk]['current'] ?? 0)
                    : ($normalized[$mk] ?? 0);

                // Map compliance → adaptation
                $displayKey = ($mk === 'compliance') ? 'adaptation' : $mk;
                $accumulator[$displayKey][] = $val;
            }
        }

        if ($count === 0) return [];

        $averaged = [];
        foreach ($accumulator as $key => $values) {
            $averaged[$key] = [
                'current' => count($values) > 0 ? round(array_sum($values) / count($values)) : 0,
                'base'    => 100,
                'change'  => 0,
            ];
        }

        return $this->enrichSessionMetrics($averaged, $versionId);
    }

    /**
     * Lazy-load metric definitions keyed by 'key' field.
     */
    private function getDefinitions(): Collection
    {
        if ($this->definitions === null) {
            $all = RefMetricDefinition::all();
            // Key by 'key' column first, fallback to 'metric_key' for legacy rows
            $this->definitions = $all->keyBy(fn($d) => $d->key ?? $d->metric_key ?? 'unknown');
        }
        return $this->definitions;
    }
}
