<?php
namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $deals = Deal::with(['lead', 'quotation', 'createdBy'])
            ->orderByDesc('created_at')
            ->paginate(20);
            
        return response()->json($deals);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'deal_value' => 'required|numeric|min:0',
            'service' => 'nullable|string',
            'closing_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        
        $user = auth()->user();
        
        $deal = Deal::create([
            'tenant_id' => $user->tenant_id,
            'lead_id' => $request->lead_id,
            'quotation_id' => $request->quotation_id,
            'deal_value' => $request->deal_value,
            'service' => $request->service,
            'closing_date' => $request->closing_date ?? now(),
            'notes' => $request->notes,
            'created_by' => $user->id,
            'payment_status' => 'pending',
        ]);
        
        // Update lead status to WON
        Lead::where('id', $request->lead_id)->update([
            'status' => 'won',
            'won_value' => $request->deal_value,
        ]);
        
        LeadActivity::create([
            'tenant_id' => $user->tenant_id,
            'lead_id' => $request->lead_id,
            'user_id' => $user->id,
            'type' => 'system',
            'description' => "Deal closed! Value: ₹{$request->deal_value}",
        ]);
        
        return response()->json($deal->load('lead'), 201);
    }
}