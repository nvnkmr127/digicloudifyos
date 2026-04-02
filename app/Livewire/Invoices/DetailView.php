<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;

class DetailView extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice->load(['client', 'project', 'items']);
    }

    public function render()
    {
        return view('livewire.invoices.detail-view');
    }
}
