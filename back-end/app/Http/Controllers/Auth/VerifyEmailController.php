<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email verified successfully']);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }
        
        $redirect = rtrim((string) config('app.frontend_url'), '/').'/dashboard?verified=1';
        return redirect()->to($redirect);
    }
}
