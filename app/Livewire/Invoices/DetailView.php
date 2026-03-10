<?php

namespace App\Livewire\Invoices;

use Livewire\Component;

use App\Models\Invoice;

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
