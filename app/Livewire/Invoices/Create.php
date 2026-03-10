<?php

namespace App\Livewire\Invoices;

use Livewire\Component;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Project;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $client_id = '';
    public $project_id = '';
    public $invoice_number = '';
    public $issue_date = '';
    public $due_date = '';
    public $status = 'draft';
    public $notes = '';
    public $payment_terms = '';

    public $items = [
        ['description' => '', 'quantity' => 1, 'unit_price' => 0]
    ];

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

    public function mount()
    {
        $this->issue_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        // Simple auto-increment placeholder
        $lastInvoice = Invoice::where('organization_id', Auth::user()->organization_id)->latest()->first();
        $nextNum = $lastInvoice ? ((int) str_replace('INV-', '', $lastInvoice->invoice_number)) + 1 : 1;
        $this->invoice_number = 'INV-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
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

    public function save()
    {
        $this->validate();

        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $invoice = Invoice::create([
            'organization_id' => Auth::user()->organization_id,
            'client_id' => $this->client_id,
            'project_id' => $this->project_id ?: null,
            'invoice_number' => $this->invoice_number,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'subtotal' => $subtotal,
            'tax_amount' => 0, // Placeholder
            'discount_amount' => 0, // Placeholder
            'total_amount' => $subtotal,
            'paid_amount' => 0,
            'notes' => $this->notes,
            'payment_terms' => $this->payment_terms,
        ]);

        foreach ($this->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        session()->flash('success', 'Invoice created successfully.');

        return redirect()->route('invoices.index');
    }

    public function render()
    {
        $clients = Client::where('organization_id', Auth::user()->organization_id)->get();
        $projects = Project::where('organization_id', Auth::user()->organization_id)->get();

        return view('livewire.invoices.create', [
            'clients' => $clients,
            'projects' => $projects,
        ]);
    }
}
