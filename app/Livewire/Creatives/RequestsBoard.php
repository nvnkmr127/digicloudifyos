<?php

namespace App\Livewire\Creatives;

use Livewire\Component;

class RequestsBoard extends Component
{
    public $requests = [];

    public $statusGroups = [
        'requested' => ['title' => 'Requested', 'color' => 'bg-gray-100', 'text' => 'text-gray-700'],
        'in_production' => ['title' => 'In Production', 'color' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
        'review' => ['title' => 'Review', 'color' => 'bg-purple-100', 'text' => 'text-purple-700'],
        'approved' => ['title' => 'Approved', 'color' => 'bg-green-100', 'text' => 'text-green-700'],
    ];

    public function mount()
    {
        $this->refreshRequests();
    }

    public function refreshRequests()
    {
        $allRequests = \App\Models\CreativeRequest::where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->with(['client', 'campaign', 'assignee'])
            ->orderBy('deadline', 'asc')
            ->get();

        $this->requests = collect($this->statusGroups)
            ->mapWithKeys(function ($group, $key) use ($allRequests) {
                return [
                    $key => $allRequests->where('status', $key)->values()->toArray(),
                ];
            })
            ->toArray();
    }

    public function updateStatus($requestId, $newStatus)
    {
        $request = \App\Models\CreativeRequest::where('id', $requestId)
            ->where('organization_id', \Illuminate\Support\Facades\Auth::user()->organization_id)
            ->firstOrFail();

        $request->update(['status' => $newStatus]);
        $this->refreshRequests();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Status updated',
        ]);
    }

    public function render()
    {
        return view('livewire.creatives.requests-board')->layout('layouts.app');
    }
}
