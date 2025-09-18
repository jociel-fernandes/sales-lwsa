<?php

namespace App\Http\Controllers\Api;

use App\Models\Sale;
use App\Models\Seller;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleStoreRequest;
use App\Http\Requests\SaleUpdateRequest;
use App\Services\SettingsService;
use App\Services\CommissionCalculator;
use App\Http\Resources\SaleResource;

class SaleController extends Controller
{
    public function __construct(
        private SettingsService $settings,
        private CommissionCalculator $commissionCalc,
    ) {
        $this->middleware('auth:sanctum');
    }

    protected function authorizeUser(Request $request)
    {
        $user = $request->user();
        if (!$user) return false;
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('sellers')) return true;
        if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('manage.sales')) return true;
        return false;
    }

    public function index(Request $request)
    {
        if (!$this->authorizeUser($request)) return response()->json(['message' => 'Unauthorized.'], 403);

        $query = Sale::with('seller');

        $user = $request->user();
        if ($user && $user->hasRole('sellers')) {
            $seller = Seller::where('user_id', $user->id)->first();
            if (!$seller) return response()->json(['message' => 'Seller record not found for user.'], 422);
            $query->where('seller_id', $seller->id);
        } else {
            if ($request->filled('seller_id')) {
                $query->where('seller_id', $request->seller_id);
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $from = $request->get('date_from');
            $to = $request->get('date_to');
            if ($from && $to) {
                $query->whereBetween('date', [$from, $to]);
            } elseif ($from) {
                $query->whereDate('date', '>=', $from);
            } elseif ($to) {
                $query->whereDate('date', '<=', $to);
            }
        }

        $page = max(1, (int)$request->get('page', 1));
        $paginator = $query->orderBy('date', 'desc')->paginate(15, ['*'], 'page', $page);
        $paginator->setCollection(SaleResource::collection($paginator->getCollection()->load('seller'))->collection);
        return response()->json($paginator);
    }

    public function store(SaleStoreRequest $request)
    {
        if (!$this->authorizeUser($request)) return response()->json(['message' => 'Unauthorized.'], 403);
        $data = $request->only(['seller_id','date','value','description']);
        $user = $request->user();

        // If the authenticated user is a seller, ensure seller_id is their own seller record
        if ($user->hasRole('sellers')) {
            $seller = Seller::where('user_id', $user->id)->first();
            if (!$seller) return response()->json(['message' => 'Seller record not found for user.'], 422);
            $data['seller_id'] = $seller->id;
        }

    $sale = Sale::create($data);
    return response()->json(new SaleResource($sale->load('seller')), 201);
    }

    public function show(Sale $sale, Request $request)
    {
        if (!$this->authorizeUser($request)) return response()->json(['message' => 'Unauthorized.'], 403);
    return response()->json(new SaleResource($sale->load('seller')));
    }

    public function update(SaleUpdateRequest $request, Sale $sale)
    {
        if (!$this->authorizeUser($request)) return response()->json(['message' => 'Unauthorized.'], 403);

        $data = $request->only(['seller_id','date','value','description']);
        $user = $request->user();

        if ($user->hasRole('sellers')) {
            $seller = Seller::where('user_id', $user->id)->first();
            if (!$seller) return response()->json(['message' => 'Seller record not found for user.'], 422);
            // sellers may only update their own sales
            if ($sale->seller_id !== $seller->id) return response()->json(['message' => 'Unauthorized.'], 403);
            // enforce seller_id to their own
            $data['seller_id'] = $seller->id;
        }

    $sale->update($data);
    $fresh = $sale->fresh()->load('seller');
    return response()->json(new SaleResource($fresh));
    }

    public function destroy(Sale $sale, Request $request)
    {
        if (!$this->authorizeUser($request)) return response()->json(['message' => 'Unauthorized.'], 403);
        $user = $request->user();
        if ($user->hasRole('sellers')) {
            $seller = Seller::where('user_id', $user->id)->first();
            if (!$seller || $sale->seller_id !== $seller->id) return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $sale->delete();
        return response()->json(['message' => 'Sale deleted.']);
    }

    // Commission handling centralizado em SaleResource
}
