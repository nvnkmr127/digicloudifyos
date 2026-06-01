<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

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
        ['description' => '', 'quantity' => 1, 'unit_price' => 0],
    ];

    protected function rules()
    {
        return [
            'client_id' => [
                'required',
                'uuid',
                Rule::exists('clients', 'id')->where('organization_id', Auth::user()->organization_id),
            ],
            'project_id' => [
                'nullable',
                'uuid',
                Rule::exists('projects', 'id')->where('organization_id', Auth::user()->organization_id),
            ],
            'invoice_number' => 'required|string',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'status' => 'required|in:draft,sent,paid,overdue,void',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function getSubtotalProperty()
    {
        return collect($this->items)->sum(function ($item) {
            return (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
        });
    }

    public function mount()
    {
        $this->issue_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        // Simple auto-increment placeholder
        $lastInvoice = Invoice::where('organization_id', Auth::user()->organization_id)->latest()->first();
        $nextNum = $lastInvoice ? ((int) str_replace('INV-', '', $lastInvoice->invoice_number)) + 1 : 1;
        $this->invoice_number = 'INV-'.str_pad($nextNum, 4, '0', STR_PAD_LEFT);
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

        $invoice = \DB::transaction(function () {
            // Regenerate unique number at save time to avoid race conditions (B007)
            $lastInvoice = Invoice::where('organization_id', Auth::user()->organization_id)
                ->lockForUpdate()
                ->latest()
                ->first();

            $nextNum = $lastInvoice ? ((int) str_replace('INV-', '', $lastInvoice->invoice_number)) + 1 : 1;
            $this->invoice_number = 'INV-'.str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $subtotal = collect($this->items)->sum(fn ($i) => $i['quantity'] * $i['unit_price']);

            return Invoice::create([
                'organization_id' => Auth::user()->organization_id,
                'client_id' => $this->client_id,
                'project_id' => $this->project_id ?: null,
                'invoice_number' => $this->invoice_number,
                'issue_date' => $this->issue_date,
                'due_date' => $this->due_date,
                'status' => $this->status,
                'subtotal' => $subtotal,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'paid_amount' => 0,
                'notes' => $this->notes,
                'payment_terms' => $this->payment_terms,
            ]);
        });

        foreach ($this->items as $item) {
            $amount = round($item['quantity'] * $item['unit_price'], 2);
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $amount,
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
