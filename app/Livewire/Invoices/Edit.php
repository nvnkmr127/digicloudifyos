<?php

namespace App\Livewire\Invoices;

use Livewire\Component;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Project;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    public Invoice $invoice;
    public $client_id;
    public $project_id;
    public $invoice_number;
    public $issue_date;
    public $due_date;
    public $status;
    public $notes;
    public $payment_terms;

    public $items = [];

    protected $rules = [
        'client_id' => 'required|uuid|exists:clients,id',
        'project_id' => 'nullable|uuid|exists:projects,id',
        'invoice_number' => 'required|string',
        'issue_date' => 'required|date',
        'due_date' => 'required|date',
        'status' => 'required|in:draft,sent,paid,overdue,void',
        'items.*.description' => 'required|string',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.unit_price' => 'required|numeric|min:0',
    ];

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->client_id = $invoice->client_id;
        $this->project_id = $invoice->project_id;
        $this->invoice_number = $invoice->invoice_number;
        $this->issue_date = $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('Y-m-d') : '';
        $this->due_date = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '';
        $this->status = $invoice->status;
        $this->notes = $invoice->notes;
        $this->payment_terms = $invoice->payment_terms;

        $this->items = $invoice->items->map(function ($item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ];
        })->toArray();
    }

    public function addItem()
    {
        $this->items[] = ['description' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function update()
    {
        $this->validate();

        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $this->invoice->update([
            'client_id' => $this->client_id,
            'project_id' => $this->project_id ?: null,
            'invoice_number' => $this->invoice_number,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'subtotal' => $subtotal,
            'total_amount' => $subtotal, // Assuming no tax/discount simplified
            'notes' => $this->notes,
            'payment_terms' => $this->payment_terms,
        ]);

        // Simple sync: delete old items and create new ones
        $this->invoice->items()->delete();
        foreach ($this->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $this->invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        session()->flash('success', 'Invoice updated successfully.');

        return redirect()->route('invoices.index');
    }

    public function render()
    {
        $clients = Client::where('organization_id', Auth::user()->organization_id)->get();
        $projects = Project::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.invoices.edit', [
            'clients' => $clients,
            'projects' => $projects,
        ]);
    }
}
