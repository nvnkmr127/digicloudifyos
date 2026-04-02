<?php

namespace App\Livewire\ServicePackages;

use App\Models\PlaybookTemplate;
use App\Models\ServicePackage;
use App\Models\User;
use App\Services\Playbooks\PlaybookService;
use App\Services\Playbooks\ServicePackageService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public string $name = '';

    public string $industry = '';

    public string $cadence = 'monthly';

    public string $dayOfMonth = '1';

    public string $dayOfWeek = '1';

    public array $templateIds = [];

    public function mount(PlaybookService $playbooks, ServicePackageService $packages): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $user->can('manage-organization')) {
            abort(403);
        }

        $playbooks->ensureDefaults($user->organization_id);
        $packages->ensureDefaults($user->organization_id);
    }

    public function save(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $user->can('manage-organization')) {
            abort(403);
        }

        $this->validate([
            'name' => 'required|min:3',
            'industry' => 'nullable|string',
            'cadence' => 'required|in:weekly,monthly,quarterly',
        ]);

        ServicePackage::create([
            'organization_id' => $user->organization_id,
            'name' => $this->name,
            'industry' => $this->industry !== '' ? $this->industry : null,
            'cadence' => $this->cadence,
            'day_of_week' => $this->cadence === 'weekly' ? (int) $this->dayOfWeek : null,
            'day_of_month' => in_array($this->cadence, ['monthly', 'quarterly'], true) ? max(1, min(28, (int) $this->dayOfMonth)) : null,
            'is_active' => true,
            'config' => [
                'playbook_template_ids' => array_values(array_filter($this->templateIds)),
            ],
        ]);

        $this->reset(['name', 'industry', 'cadence', 'dayOfMonth', 'dayOfWeek', 'templateIds']);
        $this->cadence = 'monthly';
        $this->dayOfMonth = '1';
        $this->dayOfWeek = '1';

        session()->flash('success', 'Service package created.');
    }

    public function toggle(string $id): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $user->can('manage-organization')) {
            abort(403);
        }

        $pkg = ServicePackage::where('organization_id', $user->organization_id)->find($id);
        if (! $pkg) {
            return;
        }

        $pkg->update(['is_active' => ! $pkg->is_active]);
    }

    public function render()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $user->can('manage-organization')) {
            abort(403);
        }

        $packages = ServicePackage::where('organization_id', $user->organization_id)
            ->orderByDesc('created_at')
            ->get();

        $templates = PlaybookTemplate::where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category']);

        return view('livewire.service-packages.index', [
            'packages' => $packages,
            'templates' => $templates,
        ]);
    }
}
