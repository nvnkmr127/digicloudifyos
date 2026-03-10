<?php

namespace App\Livewire\Invoices;

use Livewire\Component;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $search = '';

    public function delete($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        session()->flash('success', 'Invoice deleted successfully.');
    }

    public function render()
    {
        $invoices = Invoice::where('organization_id', Auth::user()->organization_id)
            ->with(['client', 'project'])
            ->when($this->search, function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.invoices.index', [
            'invoices' => $invoices
        ]);
    }
}
