<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::withoutGlobalScopes()->with('tenant', 'user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('gateway_payment_id', 'like', "%{$search}%")
                  ->orWhere('gateway_order_id', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function ($tq) use ($search) {
                      $tq->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($tenantId = $request->input('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        $payments = $query->latest()->paginate(25)->withQueryString();

        $totalRevenue = Payment::withoutGlobalScopes()->where('status', 'success')->sum('amount');
        $totalPending = Payment::withoutGlobalScopes()->where('status', 'pending')->sum('amount');
        $totalFailed = Payment::withoutGlobalScopes()->where('status', 'failed')->count();
        $totalRefunded = Payment::withoutGlobalScopes()->where('status', 'refunded')->sum('amount');
        $totalCount = Payment::withoutGlobalScopes()->count();

        $tenants = Tenant::orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.payments.index', compact(
            'payments', 'totalRevenue', 'totalPending', 'totalFailed', 'totalRefunded', 'totalCount', 'tenants'
        ));
    }

    public function refund(Request $request, $id)
    {
        $payment = Payment::withoutGlobalScopes()->findOrFail($id);

        if ($payment->status !== 'success') {
            return redirect()->back()->with('error', 'Only successful payments can be refunded.');
        }

        $oldStatus = $payment->status;
        $payment->update([
            'status' => 'refunded',
            'notes' => 'Refunded by Admin at ' . now(),
        ]);

        AuditLog::create([
            'tenant_id' => $payment->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'admin_refund_payment',
            'model_type' => Payment::class,
            'model_id' => $payment->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'refunded', 'amount' => $payment->amount],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Payment #' . $payment->id . ' has been marked as refunded.');
    }

    public function export(Request $request)
    {
        $query = Payment::withoutGlobalScopes()->with('tenant', 'user');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $payments = $query->latest()->get();

        AuditLog::create([
            'tenant_id' => null,
            'user_id' => auth()->id(),
            'action' => 'export_payments',
            'model_type' => Payment::class,
            'model_id' => null,
            'new_values' => ['format' => 'csv', 'count' => $payments->count()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="admin_payments_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Tenant', 'User', 'Amount (INR)', 'Gateway', 'Order ID', 'Payment ID', 'Type', 'Status', 'Date']);
            foreach ($payments as $p) {
                fputcsv($handle, [
                    $p->id,
                    $p->tenant?->company_name ?? 'N/A',
                    $p->user?->name ?? 'N/A',
                    $p->amount,
                    $p->gateway,
                    $p->gateway_order_id,
                    $p->gateway_payment_id,
                    $p->type,
                    $p->status,
                    $p->created_at,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}