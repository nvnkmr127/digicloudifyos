<?php

namespace App\Livewire\Intelligence;

use App\Models\AiInsight;
use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class InsightsFeed extends Component
{
    use WithPagination;

    public $filter = 'all';

    public $clientFilter = '';

    public $priorityFilter = '';

    public $showCompleted = false;

    public $showDismissed = false;

    protected $queryString = [
        'filter' => ['except' => 'all'],
        'clientFilter' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
    ];

    public function dismiss($id)
    {
        $insight = AiInsight::findOrFail($id);
        $insight->dismiss(auth()->id());
    }

    public function complete($id)
    {
        $insight = AiInsight::findOrFail($id);
        $insight->complete(auth()->id());
    }

    public function setFilter($value)
    {
        $this->filter = $value;
        $this->resetPage();
    }

    public function render()
    {
        $query = AiInsight::where('organization_id', auth()->user()->organization_id)
            ->with('client');

        if (! $this->showCompleted) {
            $query->active();
        }

        if ($this->clientFilter) {
            $query->where('client_id', $this->clientFilter);
        }

        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        if ($this->filter === 'opportunities') {
            $query->opportunities();
        } elseif ($this->filter === 'critical') {
            $query->byPriority('critical');
        }

        return view('livewire.intelligence.insights-feed', [
            'insights' => $query->latest('insight_date')->paginate(15),
            'clients' => Client::where('organization_id', auth()->user()->organization_id)->get(),
        ])->layout('layouts.app');
    }
}
