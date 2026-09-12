<?php
namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}
    
    public function index()
    {
        $stats = $this->dashboardService->getStats(auth()->user()->tenant_id);
        return new DashboardResource((object) $stats);
    }
    
    public function leads(Request $request)
    {
        $data = $this->dashboardService->getLeadFunnel(
            auth()->user()->tenant_id,
            $request->from,
            $request->to
        );
        return response()->json(['data' => $data]);
    }
    
    public function sales(Request $request)
    {
        $data = $this->dashboardService->getSalesPipeline(
            auth()->user()->tenant_id,
            $request->from,
            $request->to
        );
        return response()->json(['data' => $data]);
    }
    
    public function usage(Request $request)
    {
        $data = $this->dashboardService->getUsageStats(
            auth()->user()->tenant_id,
            $request->from,
            $request->to
        );
        return response()->json(['data' => $data]);
    }
    
    public function staff()
    {
        $data = $this->dashboardService->getStaffPerformance(
            auth()->user()->tenant_id
        );
        return response()->json(['data' => $data]);
    }
}