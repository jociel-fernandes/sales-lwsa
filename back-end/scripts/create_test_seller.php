<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'test-seller@local';
$u = App\Models\User::where('email', $email)->first();
if (! $u) {
    $u = App\Models\User::create(['name' => 'Test Seller', 'email' => $email, 'password' => Illuminate\Support\Facades\Hash::make('Secret123')]);
}
if (! $u->hasRole('sellers')) {
    $u->assignRole('sellers');
}
$s = App\Models\Seller::create(['name' => 'Test Seller', 'email' => $email, 'user_id' => $u->id]);
$token = Illuminate\Support\Facades\Password::broker()->createToken($u);
echo "seller id: {$s->id}\n";
echo "user id: {$u->id}\n";
echo "reset link: " . config('app.url') . "/password-reset/{$token}?email=" . urlencode($u->email) . "\n";
