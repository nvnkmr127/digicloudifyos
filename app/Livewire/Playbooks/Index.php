<?php

namespace App\Livewire\Playbooks;

use App\Models\ClientPlaybookRun;
use App\Models\PlaybookTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $category = '';

    public function render()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $user->can('view-analytics')) {
            abort(403);
        }

        $templates = PlaybookTemplate::where('organization_id', $user->organization_id)
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $recentRuns = ClientPlaybookRun::where('organization_id', $user->organization_id)
            ->with(['client', 'template'])
            ->orderByDesc('run_date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('livewire.playbooks.index', [
            'templates' => $templates,
            'recentRuns' => $recentRuns,
        ]);
    }
}
