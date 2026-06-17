<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'superadmin@momdigital.io')->first();
if ($user) {
    $user->password = Hash::make('WorkPilot@2026');
    $user->save();
    echo "Password updated successfully for superadmin@momdigital.io\n";
} else {
    echo "User not found\n";
}
