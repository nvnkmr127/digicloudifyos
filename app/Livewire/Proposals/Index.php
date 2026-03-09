<?php

namespace App\Livewire\Proposals;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $invoices = \App\Models\Invoice::with(['client', 'project'])->orderByDesc('issue_date')->get();

        $drafts = $invoices->where('status', 'draft')->count();
        $sent = $invoices->where('status', 'sent')->count();
        $paid = $invoices->where('status', 'paid')->count();
        $overdue = $invoices->where('status', 'overdue')->count();

        return view('livewire.proposals.index', [
            'invoices' => $invoices,
            'drafts' => $drafts,
            'sent' => $sent,
            'paid' => $paid,
            'overdue' => $overdue
        ]);
    }
}
