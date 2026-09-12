<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'admin@doxainfoplus.com')->first();
if ($user) {
    $user->password = \Illuminate\Support\Facades\Hash::make('password');
    $user->save();
    echo 'Password reset successfully for admin@doxainfoplus.com\n';
}

$user2 = \App\Models\User::where('email', 'admin@doxainfotech.com')->first();
if ($user2) {
    $user2->password = \Illuminate\Support\Facades\Hash::make('password');
    $user2->save();
    echo 'Password reset successfully for admin@doxainfotech.com\n';
}
