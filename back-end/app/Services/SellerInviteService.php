<?php

namespace App\Services;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SellerCredentialsMail;
use App\Jobs\SendSellerInvite;

class SellerInviteService
{
    public function ensureUserAndInvite(Seller $seller): void
    {
        if ( $seller->user_id || !$seller->email) return;

        $plainPassword = bin2hex(random_bytes(5));
        $user = User::create([
            'name' => $seller->name,
            'email' => $seller->email,
            'password' => Hash::make($plainPassword),
        ]);

        if (! $user->hasRole('sellers')) {
            $user->assignRole('sellers');
        }

        $seller->user()->associate($user);
        $seller->saveQuietly();

        $token = Password::broker()->createToken($user);
        $frontendBase = env('FRONTEND_URL', config('app.url'));
        $link = rtrim($frontendBase, '/') . "/password-reset" . "?token=" . urlencode($token) . "&email=" . urlencode($user->email);

        try {
            Log::info('SellerInviteService: dispatching SendSellerInvite', ['email' => $user->email]);
            SendSellerInvite::dispatch($user->name, $user->email, $link);
        } catch (\Throwable $e) {
            Log::error('SellerInviteService: dispatch failed', ['err' => $e->getMessage()]);
            try {
                Log::info('SellerInviteService: trying Bus::dispatchSync fallback', ['email' => $user->email]);
                Bus::dispatchSync(new SendSellerInvite($user->name, $user->email, $link));
            } catch (\Throwable $e2) {
                Log::error('SellerInviteService: Bus::dispatchSync failed', ['err' => $e2->getMessage()]);
                try {
                    Log::info('SellerInviteService: trying direct Mail::send fallback', ['email' => $user->email]);
                    Mail::to($user->email)->send(new SellerCredentialsMail($user->name, $user->email, $link));
                } catch (\Throwable $e3) {
                    Log::error('SellerInviteService: Mail::send failed', ['err' => $e3->getMessage()]);
                }
            }
        }
    }
}
