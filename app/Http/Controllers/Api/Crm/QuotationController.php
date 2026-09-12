<?php
namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\LeadActivity;
use App\Notifications\QuotationAcceptedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $quotations = Quotation::with(['lead', 'createdBy'])
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->lead_id, fn($q, $v) => $q->where('lead_id', $v))
            ->orderByDesc('created_at')
            ->paginate(20);
            
        return QuotationResource::collection($quotations);
    }
    
    public function store(CreateQuotationRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();
        
        return DB::transaction(function () use ($validated, $user) {
            // Generate quotation number
            $lastNumber = Quotation::where('tenant_id', $user->tenant_id)
                ->max('id') ?? 0;
            $quotationNumber = 'QTN-' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
            
            // Calculate totals
            $subtotal = collect($validated['items'])->sum(function ($item) {
                return ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
            });
            
            $discountAmount = $validated['discount_amount'] ?? 0;
            $taxableAmount = $subtotal - $discountAmount;
            $gstPercent = $validated['gst_percent'] ?? 18;
            $gstAmount = round($taxableAmount * $gstPercent / 100, 2);
            $total = round($taxableAmount + $gstAmount, 2);
            
            $quotation = Quotation::create([
                'tenant_id' => $user->tenant_id,
                'lead_id' => $validated['lead_id'],
                'created_by' => $user->id,
                'quotation_number' => $quotationNumber,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'discount_type' => $validated['discount_type'] ?? 'flat',
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'total' => $total,
                'validity_days' => $validated['validity_days'] ?? 30,
                'valid_until' => now()->addDays($validated['validity_days'] ?? 30),
                'terms' => $validated['terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
            ]);
            
            // Create items
            foreach ($validated['items'] as $index => $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'service_name' => $item['service_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => $itemTotal,
                    'sort_order' => $index,
                ]);
            }
            
            LeadActivity::create([
                'tenant_id' => $user->tenant_id,
                'lead_id' => $validated['lead_id'],
                'user_id' => $user->id,
                'type' => 'system',
                'description' => "Quotation #{$quotationNumber} created (₹{$total})",
            ]);
            
            return (new QuotationResource($quotation->load('items')))
                ->response()
                ->setStatusCode(201);
        });
    }
    
    public function show(Quotation $quotation)
    {
        return new QuotationResource($quotation->load(['items', 'lead', 'createdBy']));
    }
    
    public function update(UpdateQuotationRequest $request, Quotation $quotation)
    {
        if ($quotation->status !== 'draft') {
            return response()->json(['message' => 'Only draft quotations can be edited.'], 422);
        }
        
        $quotation->update($request->validated());
        return new QuotationResource($quotation->fresh()->load('items'));
    }
    
    public function send(Quotation $quotation)
    {
        $quotation->update(['status' => 'sent', 'sent_at' => now()]);
        
        LeadActivity::create([
            'tenant_id' => auth()->user()->tenant_id,
            'lead_id' => $quotation->lead_id,
            'user_id' => auth()->id(),
            'type' => 'system',
            'description' => "Quotation #{$quotation->quotation_number} sent",
        ]);
        
        return new QuotationResource($quotation);
    }
    
    public function accept(Quotation $quotation)
    {
        $quotation->update(['status' => 'accepted', 'accepted_at' => now()]);
        
        // Notify the creator
        $quotation->createdBy?->notify(new QuotationAcceptedNotification($quotation));
        
        LeadActivity::create([
            'tenant_id' => $quotation->tenant_id,
            'lead_id' => $quotation->lead_id,
            'user_id' => auth()->id(),
            'type' => 'system',
            'description' => "Quotation #{$quotation->quotation_number} accepted!",
        ]);
        
        return new QuotationResource($quotation);
    }
    
    public function reject(Quotation $quotation)
    {
        $quotation->update(['status' => 'rejected', 'rejected_at' => now()]);
        return new QuotationResource($quotation);
    }
}