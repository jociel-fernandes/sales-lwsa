<?php

namespace App\Http\Controllers\Api;

use App\Models\Seller;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SellerStoreRequest;
use App\Http\Requests\SellerUpdateRequest;
use App\Services\SellerInviteService;
use App\Services\SettingsService;
use App\Services\CommissionCalculator;
use App\Services\SalesSummaryService;

class SellerController extends Controller
{
    public function __construct(
        private SellerInviteService $inviteService,
    private SettingsService $settings,
    private CommissionCalculator $commissionCalc,
    private SalesSummaryService $summary,
    ) {
        $this->middleware('auth:sanctum');
    }

    protected function authorizeUser(Request $request)
    {
        $user = $request->user();
        if (!$user) return false;

        if ($user->hasRole('admin')) return true;
        $perms = $user->getAllPermissions()->pluck('name')->toArray();
        foreach ($perms as $p) {
            if (is_string($p) && str_ends_with($p, '.seller')) return true;
        }
        return false;
    }

    public function index(Request $request)
    {
        if (!$this->authorizeUser($request)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = Seller::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
        }

        $sellers = $query->paginate(10);
        return response()->json($sellers);
    }

    public function store(SellerStoreRequest $request)
    {
        if (!$this->authorizeUser($request)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $data = $request->only(['name','email','password']);
        $seller = Seller::create([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
        $this->inviteService->ensureUserAndInvite($seller);

        return response()->json($seller, 201);
    }

    public function show(Seller $seller, Request $request)
    {
        if (!$this->authorizeUser($request)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        return response()->json($seller);
    }

    public function update(SellerUpdateRequest $request, Seller $seller)
    {
        if (!$this->authorizeUser($request)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

    $data = $request->only(['name','email','password']);

        if (!empty($data['password'])) {
            $seller->temp_password = $data['password'];
        }

        $seller->update([
            'name' => $data['name'] ?? $seller->name,
            'email' => $data['email'] ?? $seller->email,
        ]);
        return response()->json($seller);
    }

    public function destroy(Seller $seller, Request $request)
    {
        if (!$this->authorizeUser($request)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            $linkedUser = $seller->user;
            if ($linkedUser) {
                if (method_exists($linkedUser, 'hasRole') && $linkedUser->hasRole('admin')) {
                    $seller->user()->dissociate();
                    $seller->save();
                } else {
                    $linkedUser->delete();
                }
            }
        } catch (\Throwable $e) {
        }

        $seller->delete();
        return response()->json(['message' => 'Seller and linked user (if any) deleted.']);
    }

    /**
     * Admin-only: resend the daily commission email to a seller on demand.
     */
    public function resendCommissionEmail(Seller $seller, Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!$seller->email) {
            return response()->json(['message' => 'Seller has no email.'], 400);
        }

        $date = $request->input('date', date('Y-m-d'));
        $ok = $this->summary->sendSellerSummary($seller, $date);
        if (! $ok) {
            return response()->json(['message' => 'Failed to send email.'], 500);
        }

        return response()->json(['message' => 'Email enqueued/sent.']);
    }

    /**
     * Return the seller record associated with the authenticated user (if any).
     */
    public function me(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['message' => 'Unauthorized.'], 403);

        $seller = Seller::where('user_id', $user->id)->first();
        if (!$seller) return response()->json(['message' => 'Seller not found.'], 404);
        return response()->json($seller);
    }
}
