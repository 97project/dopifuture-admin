<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Connectors\MissionWayConnector;
use App\Connectors\WayStartupConnector;

class AuditUserSync extends Command
{
    protected $signature = 'audit:users';
    protected $description = 'Audits the user mapping and data parity between Portal, MissionWay, and WayStartup APIs';

    public function handle()
    {
        $this->info("🚀 Starting 3-way User Sync Audit...");
        $users = User::all()->keyBy('id');
        $this->comment("1. Found " . $users->count() . " local Portal Users.");

        $mwConnector = app(MissionWayConnector::class);
        $wsConnector = app(WayStartupConnector::class);

        // Fetch MW Players
        $this->comment("2. Fetching all Mission Way Players from Backend API...");
        $mwPlayers = [];
        $page = 1;
        do {
            $resp = $mwConnector->getPlayers(['page' => $page, 'limit' => 100]);
            $batch = $resp['data'] ?? $resp;
            if (!is_array($batch) || empty($batch)) break;
            foreach ($batch as $p) {
                if (isset($p['id'])) {
                    $mwPlayers[$p['id']] = $p;
                }
            }
            $page++;
        } while (is_array($batch) && count($batch) >= 100);
        $this->comment("   Fetched " . count($mwPlayers) . " Mission Way Players.");

        // Fetch WS Members
        $this->comment("3. Fetching all Way Startup Members from Backend API...");
        $wsMembers = [];
        $resp = $wsConnector->getMembers(['limit' => 1000]);
        $batch = is_array($resp) ? $resp : [];
        foreach ($batch as $m) {
            if (isset($m['id'])) {
                $wsMembers[$m['id']] = $m;
            }
        }
        $this->comment("   Fetched " . count($wsMembers) . " Way Startup Members.");

        $this->newLine();
        $this->info("🔍 Cross-referencing Accounts...");

        $tableData = [];
        
        $mwEmailMap = collect($mwPlayers)->keyBy(fn($p) => strtolower($p['email'] ?? ''));
        $wsEmailMap = collect($wsMembers)->keyBy(fn($m) => strtolower($m['email'] ?? ''));
        $mwUserIdMap = collect($mwPlayers)->keyBy(fn($p) => (int) ($p['userId'] ?? 0));
        $wsUserIdMap = collect($wsMembers)->keyBy(fn($m) => (int) ($m['userId'] ?? 0));

        $validMwCount = 0;
        $validWsCount = 0;
        $mismatchMwCount = 0;
        $mismatchWsCount = 0;

        foreach ($users as $id => $user) {
            $email = strtolower($user->email);

            // MW Check
            $mwStatus = "🔴 Missing";
            $mwFoundByEmail = $mwEmailMap->get($email);
            $mwFoundById = $mwUserIdMap->get($id);

            if ($mwFoundById && $mwFoundByEmail && $mwFoundById['id'] === $mwFoundByEmail['id']) {
                $mwStatus = "🟢 Perfect Sync (pg.id: {$mwFoundById['id']})";
                $validMwCount++;
            } elseif ($mwFoundByEmail) {
                $mwUserIdGiven = $mwFoundByEmail['userId'] ?? 'null';
                $mwStatus = "🟡 ID Mismatch (Expected: {$id}, Backend expects: {$mwUserIdGiven})";
                $mismatchMwCount++;
            } elseif ($mwFoundById) {
                $mwStatus = "🟡 Email Mismatch (Expected: {$email}, Backend has: {$mwFoundById['email']})";
                $mismatchMwCount++;
            }

            // WS Check
            $wsStatus = "🔴 Missing";
            $wsFoundByEmail = $wsEmailMap->get($email);
            $wsFoundById = $wsUserIdMap->get($id);

            if ($wsFoundById && $wsFoundByEmail && $wsFoundById['id'] === $wsFoundByEmail['id']) {
                $wsStatus = "🟢 Perfect Sync (pg.id: {$wsFoundById['id']})";
                $validWsCount++;
            } elseif ($wsFoundByEmail) {
                $wsUserIdGiven = $wsFoundByEmail['userId'] ?? 'null';
                $wsStatus = "🟡 ID Mismatch (Expected: {$id}, Backend expects: {$wsUserIdGiven})";
                $mismatchWsCount++;
            } elseif ($wsFoundById) {
                $wsStatus = "🟡 Email Mismatch (Expected: {$email}, Backend has: {$wsFoundById['email']})";
                $mismatchWsCount++;
            }

            $tableData[] = [
                $id,
                $user->email,
                $mwStatus,
                $wsStatus
            ];
        }

        $this->table(
            ['Portal ID', 'Portal Email', 'Mission Way Status', 'Way Startup Status'],
            $tableData
        );

        $this->newLine();
        $this->info("📈 Summary Report");
        $this->line("Portal Users: " . $users->count());
        $this->line("Mission Way: {$validMwCount} Perfect, {$mismatchMwCount} Mismatch, " . ($users->count() - $validMwCount - $mismatchMwCount) . " Missing");
        $this->line("Way Startup: {$validWsCount} Perfect, {$mismatchWsCount} Mismatch, " . ($users->count() - $validWsCount - $mismatchWsCount) . " Missing");

        return 0;
    }
}
