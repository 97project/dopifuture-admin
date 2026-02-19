<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: Consolidate duplicate licenses per school into one
        $duplicates = DB::table('licenses')
            ->select('school_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('school_id')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $licenses = DB::table('licenses')
                ->where('school_id', $dup->school_id)
                ->orderByDesc('seat_count')
                ->get();

            // Keep the first (largest seat_count), merge data from others
            $keep = $licenses->first();
            $others = $licenses->slice(1);

            $totalSeats = $licenses->sum('seat_count');
            $totalUsed = $licenses->sum('used_seats');

            // Update the keeper with consolidated values
            DB::table('licenses')
                ->where('id', $keep->id)
                ->update([
                    'seat_count' => $totalSeats,
                    'used_seats' => $totalUsed,
                    'notes' => 'Consolidated school license',
                ]);

            // Move any purchases from other licenses to the keeper
            $otherIds = $others->pluck('id')->toArray();
            if (!empty($otherIds)) {
                DB::table('license_purchases')
                    ->whereIn('license_id', $otherIds)
                    ->update(['license_id' => $keep->id]);

                // Delete the duplicates
                DB::table('licenses')->whereIn('id', $otherIds)->delete();
            }
        }

        // Step 2: Add unique index to prevent future duplicates
        Schema::table('licenses', function (Blueprint $table) {
            $table->unique('school_id');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropUnique(['school_id']);
        });
    }
};
