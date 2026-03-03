<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Imports comprehensive world location data from dr5hn/countries-states-cities-database.
 *
 * Data source: https://github.com/dr5hn/countries-states-cities-database
 * Files: database/data/countries.json, states.json, cities.json.gz
 *
 * Totals: ~250 countries, ~5,000 states, ~150,000 cities
 */
class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $dataPath = database_path('data');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->command->info('Importing countries...');
        $this->importCountries("{$dataPath}/countries.json");

        $this->command->info('Importing states...');
        $this->importStates("{$dataPath}/states.json");

        $this->command->info('Importing cities (this may take a while)...');
        $this->importCities("{$dataPath}/cities.json.gz");

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Done! Location data imported successfully.');
    }

    private function importCountries(string $file): void
    {
        $countries = json_decode(file_get_contents($file), true);
        $this->command->info('  Found ' . count($countries) . ' countries');

        DB::table('countries')->truncate();

        $chunks = array_chunk($countries, 50);
        foreach ($chunks as $chunk) {
            $rows = [];
            foreach ($chunk as $c) {
                $rows[] = [
                    'id' => $c['id'],
                    'name' => $c['name'],
                    'code' => $c['iso2'],
                    'iso3' => $c['iso3'] ?? null,
                    'phonecode' => $c['phonecode'] ?? null,
                    'native' => $c['native'] ?? null,
                    'emoji' => $c['emoji'] ?? null,
                ];
            }
            DB::table('countries')->insert($rows);
        }

        $this->command->info('  ✓ ' . count($countries) . ' countries imported');
    }

    private function importStates(string $file): void
    {
        $states = json_decode(file_get_contents($file), true);
        $this->command->info('  Found ' . count($states) . ' states');

        // Get valid country IDs to skip orphan states
        $validCountryIds = DB::table('countries')->pluck('id')->flip()->toArray();

        DB::table('states')->truncate();

        $chunks = array_chunk($states, 200);
        $imported = 0;
        $bar = $this->command->getOutput()->createProgressBar(count($chunks));

        foreach ($chunks as $chunk) {
            $rows = [];
            foreach ($chunk as $s) {
                if (!isset($validCountryIds[$s['country_id']])) {
                    continue;
                }
                $rows[] = [
                    'id' => $s['id'],
                    'country_id' => $s['country_id'],
                    'name' => $s['name'],
                    'state_code' => $s['state_code'] ?? null,
                ];
            }
            if (!empty($rows)) {
                DB::table('states')->insert($rows);
                $imported += count($rows);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("  ✓ {$imported} states imported");
    }

    private function importCities(string $file): void
    {
        ini_set('memory_limit', '1G');

        // Decompress gz to temp file to avoid double-memory usage
        $tempFile = tempnam(sys_get_temp_dir(), 'cities_');
        $gz = gzopen($file, 'rb');
        $out = fopen($tempFile, 'wb');
        if (!$gz || !$out) {
            $this->command->error('  ✗ Could not open cities files');
            return;
        }
        while (!gzeof($gz)) {
            fwrite($out, gzread($gz, 1024 * 256));
        }
        gzclose($gz);
        fclose($out);

        $this->command->info('  Decompressed to temp file (' . round(filesize($tempFile) / 1024 / 1024) . ' MB)');

        $cities = json_decode(file_get_contents($tempFile), true);
        @unlink($tempFile);

        if (!$cities) {
            $this->command->error('  ✗ Could not parse cities JSON');
            return;
        }

        $total = count($cities);
        $this->command->info("  Found {$total} cities");

        // Get valid state IDs to skip orphans
        $validStateIds = DB::table('states')->pluck('id')->flip()->toArray();

        DB::table('cities')->truncate();

        $imported = 0;
        $batch = [];
        $batchSize = 500;
        $bar = $this->command->getOutput()->createProgressBar($total);

        foreach ($cities as $c) {
            if (!isset($validStateIds[$c['state_id']])) {
                $bar->advance();
                continue;
            }
            $batch[] = [
                'id' => $c['id'],
                'state_id' => $c['state_id'],
                'name' => $c['name'],
            ];
            if (count($batch) >= $batchSize) {
                DB::table('cities')->insert($batch);
                $imported += count($batch);
                $batch = [];
            }
            $bar->advance();
        }
        if (!empty($batch)) {
            DB::table('cities')->insert($batch);
            $imported += count($batch);
        }

        unset($cities);
        $bar->finish();
        $this->command->newLine();
        $this->command->info("  ✓ {$imported} cities imported");
    }
}
