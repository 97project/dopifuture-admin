<?php

namespace App\Helpers;

/**
 * Skor Threshold Sistemi — Refah/Denge/Kriz/Felaket
 *
 * Referans:
 *   ≥80 → Refah   🟢 (emerald)
 *   ≥60 → Denge   🔵 (blue)
 *   ≥40 → Kriz    🟡 (amber)
 *   <40 → Felaket  🔴 (red)
 */
class ThresholdHelper
{
    /**
     * Skor eşik bilgilerini döndür.
     *
     * @return array{label: string, emoji: string, color: string, bg: string, text: string, tailwind_bg: string, tailwind_text: string}
     */
    public static function resolve(float $score): array
    {
        if ($score >= 80) {
            return [
                'label'        => 'Refah',
                'emoji'        => '🟢',
                'color'        => '#22c55e',
                'bg'           => '#dcfce7',
                'text'         => '#166534',
                'tailwind_bg'  => 'bg-emerald-100',
                'tailwind_text'=> 'text-emerald-700',
            ];
        }
        if ($score >= 60) {
            return [
                'label'        => 'Denge',
                'emoji'        => '🔵',
                'color'        => '#3b82f6',
                'bg'           => '#dbeafe',
                'text'         => '#1e40af',
                'tailwind_bg'  => 'bg-blue-100',
                'tailwind_text'=> 'text-blue-700',
            ];
        }
        if ($score >= 40) {
            return [
                'label'        => 'Kriz',
                'emoji'        => '🟡',
                'color'        => '#f59e0b',
                'bg'           => '#fef3c7',
                'text'         => '#92400e',
                'tailwind_bg'  => 'bg-amber-100',
                'tailwind_text'=> 'text-amber-700',
            ];
        }
        return [
            'label'        => 'Felaket',
            'emoji'        => '🔴',
            'color'        => '#ef4444',
            'bg'           => '#fee2e2',
            'text'         => '#991b1b',
            'tailwind_bg'  => 'bg-red-100',
            'tailwind_text'=> 'text-red-700',
        ];
    }

    /**
     * Blade'de hızlı kullanım için inline-style badge HTML döndür.
     */
    public static function badge(float $score): string
    {
        $t = self::resolve($score);
        return '<span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:600;background:' . $t['bg'] . ';color:' . $t['text'] . ';">' . $t['emoji'] . ' ' . $t['label'] . '</span>';
    }

    /**
     * Tailwind badge class string döndür.
     */
    public static function tailwindBadge(float $score): string
    {
        $t = self::resolve($score);
        return '<span class="text-[10px] px-2 py-0.5 rounded-full font-medium ' . $t['tailwind_bg'] . ' ' . $t['tailwind_text'] . '">' . $t['emoji'] . ' ' . $t['label'] . '</span>';
    }

    /**
     * Sadece label döndür.
     */
    public static function label(float $score): string
    {
        return self::resolve($score)['label'];
    }

    /**
     * Sadece emoji döndür.
     */
    public static function emoji(float $score): string
    {
        return self::resolve($score)['emoji'];
    }
}
