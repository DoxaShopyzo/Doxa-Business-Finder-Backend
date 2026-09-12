<?php
namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Requests\AssignLeadRequest;
use App\Http\Resources\LeadResource;
use App\Http\Resources\LeadListResource;
use App\Http\Resources\ActivityResource;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadAssignment;
use App\Notifications\LeadAssignedNotification;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(
        protected LeadService $leadService
    ) {}
    
    public function index(Request $request)
    {
        $query = Lead::with(['assignedTo', 'createdBy'])
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->priority, fn($q, $v) => $q->where('priority', $v))
            ->when($request->assigned_to, fn($q, $v) => $q->where('assigned_to', $v))
            ->when($request->source, fn($q, $v) => $q->where('source', $v))
            ->when($request->date_from, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->score_min, fn($q, $v) => $q->where('opportunity_score', '>=', (int) $v))
            ->when($request->score_max, fn($q, $v) => $q->where('opportunity_score', '<=', (int) $v))
            ->when($request->search, function($q, $v) {
                $q->where(function($q) use ($v) {
                    $q->where('business_name', 'like', "%{$v}%")
                      ->orWhere('phone', 'like', "%{$v}%")
                      ->orWhere('email', 'like', "%{$v}%");
                });
            });
        
        // Sales executive only sees own leads
        if (auth()->user()->hasRole('sales_executive')) {
            $query->where('assigned_to', auth()->id());
        }
        
        $leads = $query->orderByDesc('created_at')->paginate($request->per_page ?? 20);
        
        return LeadListResource::collection($leads);
    }

    public function pipeline(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $statuses = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
        
        $leads = Lead::where('tenant_id', $tenantId)
            ->with(['assignedTo', 'createdBy'])
            ->orderByDesc('opportunity_score')
            ->get();

        $pipeline = [];
        foreach ($statuses as $status) {
            $filtered = $leads->where('status', $status)->values();
            $pipeline[$status] = [
                'count' => $filtered->count(),
                'leads' => LeadListResource::collection($filtered),
            ];
        }

        return response()->json(['data' => $pipeline]);
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:new,contacted,qualified,proposal,negotiation,won,lost',
        ]);

        $oldStatus = $lead->status;
        $lead->update(['status' => $validated['status']]);

        LeadActivity::create([
            'tenant_id' => auth()->user()->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'status_change',
            'description' => "Status changed from {$oldStatus} to {$validated['status']}",
            'metadata' => ['old_status' => $oldStatus, 'new_status' => $validated['status']],
        ]);

        return new LeadResource($lead->fresh()->load('createdBy', 'assignedTo'));
    }
    
    public function store(CreateLeadRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();
        
        // Check for duplicate
        if (!empty($validated['phone']) || !empty($validated['email']) || !empty($validated['place_reference'])) {
            $duplicate = Lead::where('tenant_id', $user->tenant_id)
                ->where(function($q) use ($validated) {
                    if (!empty($validated['phone'])) $q->orWhere('phone', $validated['phone']);
                    if (!empty($validated['email'])) $q->orWhere('email', $validated['email']);
                    if (!empty($validated['place_reference'])) $q->orWhere('place_reference', $validated['place_reference']);
                })->first();
            
            if ($duplicate) {
                return response()->json([
                    'message' => 'A lead with similar details already exists.',
                    'existing_lead' => new LeadResource($duplicate),
                ], 409);
            }
        }
        
        $validated['tenant_id'] = $user->tenant_id;
        $validated['created_by'] = $user->id;
        $lead = Lead::create($validated);
        
        // Log activity
        LeadActivity::create([
            'tenant_id' => $user->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => 'system',
            'description' => 'Lead created',
        ]);
        
        return (new LeadResource($lead->load('createdBy')))
            ->response()
            ->setStatusCode(201);
    }
    
    public function show(Lead $lead)
    {
        return new LeadResource($lead->load(['createdBy', 'assignedTo', 'activities.user', 'followups', 'quotations']));
    }
    
    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $validated = $request->validated();
        $oldStatus = $lead->status;
        
        $lead->update($validated);
        
        // Log status change
        if (isset($validated['status']) && $oldStatus !== $validated['status']) {
            LeadActivity::create([
                'tenant_id' => auth()->user()->tenant_id,
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'type' => 'status_change',
                'description' => "Status changed from {$oldStatus} to {$validated['status']}",
                'metadata' => ['old_status' => $oldStatus, 'new_status' => $validated['status']],
            ]);
        }
        
        return new LeadResource($lead->fresh()->load('createdBy', 'assignedTo'));
    }
    
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return response()->json(null, 204);
    }
    
    public function assign(AssignLeadRequest $request, Lead $lead)
    {
        $validated = $request->validated();
        $user = auth()->user();
        
        $lead->update(['assigned_to' => $validated['assigned_to']]);
        
        LeadAssignment::create([
            'tenant_id' => $user->tenant_id,
            'lead_id' => $lead->id,
            'assigned_by' => $user->id,
            'assigned_to' => $validated['assigned_to'],
            'notes' => $validated['notes'] ?? null,
        ]);
        
        LeadActivity::create([
            'tenant_id' => $user->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => 'assignment',
            'description' => 'Lead assigned to ' . \App\Models\User::find($validated['assigned_to'])?->name,
        ]);
        
        // Notify assignee
        $assignee = \App\Models\User::find($validated['assigned_to']);
        $assignee?->notify(new LeadAssignedNotification($lead, $user->name));
        
        return new LeadResource($lead->fresh()->load('assignedTo'));
    }
    
    public function addActivity(Request $request, Lead $lead)
    {
        $request->validate([
            'type' => 'required|string|in:call,note,meeting,email,whatsapp',
            'description' => 'required|string|max:2000',
            'metadata' => 'nullable|array',
        ]);
        
        $activity = LeadActivity::create([
            'tenant_id' => auth()->user()->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => $request->type,
            'description' => $request->description,
            'metadata' => $request->metadata,
        ]);
        
        return new ActivityResource($activity);
    }
    
    public function activities(Lead $lead)
    {
        $activities = $lead->activities()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);
            
        return ActivityResource::collection($activities);
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $leads = Lead::where('tenant_id', $user->tenant_id)->get();

        // Audit log the export
        \App\Models\AuditLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'export',
            'model_type' => Lead::class,
            'model_id' => null,
            'new_values' => ['format' => 'csv', 'count' => $leads->count()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads_' . date('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($leads) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Business Name', 'Category', 'Location', 'Phone', 'Email', 'Website', 'Status', 'Priority', 'Opportunity Score', 'Created At']);
            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->id,
                    $lead->business_name,
                    $lead->category,
                    $lead->location,
                    $lead->phone,
                    $lead->email,
                    $lead->website,
                    $lead->status,
                    $lead->priority,
                    $lead->opportunity_score,
                    $lead->created_at,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}