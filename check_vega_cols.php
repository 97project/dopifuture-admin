<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

$cols = Schema::connection('vega_db')->getColumnListing('users');
print_r($cols);

$count = 0;
// We only want to update users who already exist in Vega.
// Let's get all Dopifuture users.
$users = User::with('schools')->get();
foreach ($users as $user) {
    if (!$user->email) continue;
    
    // Check if user exists in vega_db
    $vegaUser = DB::connection('vega_db')->table('users')->where('email', $user->email)->first();
    
    if ($vegaUser) {
        // Prepare the language and school_name fields
        $language = $user->locale ?? 'tr';
        $schoolName = $user->schools->first()->name ?? null;
        
        $updateData = [];
        if (in_array('language', $cols) && $vegaUser->language !== $language) {
            $updateData['language'] = $language;
        }
        if (in_array('school_name', $cols) && $vegaUser->school_name !== $schoolName) {
            $updateData['school_name'] = $schoolName;
        }
        
        if (!empty($updateData)) {
            DB::connection('vega_db')->table('users')->where('id', $vegaUser->id)->update($updateData);
            $count++;
            echo "Updated Vega ID {$vegaUser->id} (Email: {$vegaUser->email}) -> " . json_encode($updateData) . "\n";
        }
    }
}
echo "Total existing Vega users updated: $count\n";
