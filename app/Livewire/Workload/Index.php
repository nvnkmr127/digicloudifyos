<?php

namespace App\Livewire\Workload;

use App\Models\User;
use App\Services\WorkloadAnalysisService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $period = 'current_week';

    public function render(WorkloadAnalysisService $service)
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $data = $service->getTeamWorkload($user->organization_id, $this->period);

        return view('livewire.workload.index', [
            'data' => $data,
        ]);
    }
}
