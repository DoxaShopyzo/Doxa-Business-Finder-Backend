<?php
namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFollowUpRequest;
use App\Http\Requests\UpdateFollowUpRequest;
use App\Http\Resources\FollowUpResource;
use App\Models\FollowUp;
use App\Models\LeadActivity;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $query = FollowUp::with(['lead', 'user'])
            ->where('user_id', auth()->id());
        
        match($request->filter) {
            'today' => $query->whereDate('followup_date', today()),
            'upcoming' => $query->where('followup_date', '>', today())->where('status', 'scheduled'),
            'overdue' => $query->where('followup_date', '<', today())->where('status', 'scheduled'),
            'completed' => $query->where('status', 'completed'),
            default => null,
        };
        
        $followups = $query->orderBy('followup_date')->paginate(20);
        return FollowUpResource::collection($followups);
    }
    
    public function store(CreateFollowUpRequest $request)
    {
        $validated = $request->validated();
        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'scheduled';
        
        $followup = FollowUp::create($validated);
        
        LeadActivity::create([
            'tenant_id' => auth()->user()->tenant_id,
            'lead_id' => $validated['lead_id'],
            'user_id' => auth()->id(),
            'type' => 'system',
            'description' => 'Follow-up scheduled for ' . $followup->followup_date,
        ]);
        
        return (new FollowUpResource($followup->load('lead')))
            ->response()
            ->setStatusCode(201);
    }
    
    public function update(UpdateFollowUpRequest $request, FollowUp $followup)
    {
        $validated = $request->validated();
        
        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }
        
        $followup->update($validated);
        
        if (isset($validated['outcome'])) {
            LeadActivity::create([
                'tenant_id' => auth()->user()->tenant_id,
                'lead_id' => $followup->lead_id,
                'user_id' => auth()->id(),
                'type' => $followup->type ?? 'call',
                'description' => $validated['outcome'],
            ]);
        }
        
        return new FollowUpResource($followup->fresh()->load('lead'));
    }
    
    public function show(FollowUp $followup)
    {
        return new FollowUpResource($followup->load(['lead', 'user']));
    }
    
    public function destroy(FollowUp $followup)
    {
        $followup->delete();
        return response()->json(null, 204);
    }
}