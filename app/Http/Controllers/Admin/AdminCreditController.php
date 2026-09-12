<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditWallet;
use App\Models\CreditTransaction;
use App\Models\Tenant;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCreditController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditWallet::withoutGlobalScopes()->with('tenant');

        if ($search = $request->input('search')) {
            $query->whereHas('tenant', function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $wallets = $query->latest()->paginate(20)->withQueryString();

        $totalBalance = CreditWallet::withoutGlobalScopes()->sum('balance');
        $totalEarned = CreditWallet::withoutGlobalScopes()->sum('total_earned');
        $totalSpent = CreditWallet::withoutGlobalScopes()->sum('total_spent');

        $tenants = Tenant::orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.credits.index', compact('wallets', 'totalBalance', 'totalEarned', 'totalSpent', 'tenants'));
    }

    public function transactions(Request $request, $tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $wallet = CreditWallet::withoutGlobalScopes()->where('tenant_id', $tenantId)->first();

        $query = CreditTransaction::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with('user');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $transactions = $query->latest()->paginate(25)->withQueryString();

        return view('admin.credits.transactions', compact('tenant', 'wallet', 'transactions'));
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'type' => 'required|in:credit,debit',
            'credits' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $tenantId = $request->tenant_id;
        $type = $request->type;
        $credits = (int) $request->credits;
        $description = 'Admin Adjustment: ' . $request->description;

        DB::transaction(function () use ($tenantId, $type, $credits, $description) {
            $wallet = CreditWallet::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                $wallet = CreditWallet::withoutGlobalScopes()->create([
                    'tenant_id' => $tenantId,
                    'balance' => 0,
                    'total_earned' => 0,
                    'total_spent' => 0,
                ]);
            }

            $balanceBefore = $wallet->balance;

            if ($type === 'credit') {
                $wallet->balance += $credits;
                $wallet->total_earned += $credits;
            } else {
                $wallet->balance = max(0, $wallet->balance - $credits);
                $wallet->total_spent += $credits;
            }

            $wallet->save();

            CreditTransaction::withoutGlobalScopes()->create([
                'tenant_id' => $tenantId,
                'wallet_id' => $wallet->id,
                'type' => $type === 'credit' ? 'admin_credit' : 'admin_debit',
                'credits' => $credits,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
                'description' => $description,
            ]);
        });

        // Audit log
        AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => auth()->id(),
            'action' => 'admin_credit_adjustment',
            'model_type' => CreditWallet::class,
            'model_id' => null,
            'new_values' => ['type' => $type, 'credits' => $credits, 'reason' => $description],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.credits.index')
            ->with('success', ucfirst($type) . ' of ' . $credits . ' credits applied successfully.');
    }

    public function export(Request $request)
    {
        $wallets = CreditWallet::withoutGlobalScopes()->with('tenant')->get();

        AuditLog::create([
            'tenant_id' => null,
            'user_id' => auth()->id(),
            'action' => 'export_credits',
            'model_type' => CreditWallet::class,
            'model_id' => null,
            'new_values' => ['format' => 'csv', 'count' => $wallets->count()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="admin_credit_wallets_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($wallets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tenant ID', 'Company Name', 'Email', 'Balance', 'Total Earned', 'Total Spent', 'Last Updated']);
            foreach ($wallets as $w) {
                fputcsv($handle, [
                    $w->tenant_id,
                    $w->tenant?->company_name ?? 'N/A',
                    $w->tenant?->email ?? 'N/A',
                    $w->balance,
                    $w->total_earned,
                    $w->total_spent,
                    $w->updated_at,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}