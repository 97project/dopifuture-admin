<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Fix used_seats to match actual student count (not all users).
     * License = student capacity; teachers/admins don't consume seats.
     */
    public function up(): void
    {
        $licenses = DB::table('licenses')->get();

        foreach ($licenses as $license) {
            $studentCount = DB::table('school_user')
                ->where('school_id', $license->school_id)
                ->where('role', 'student')
                ->count();

            DB::table('licenses')
                ->where('id', $license->id)
                ->update(['used_seats' => $studentCount]);
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
