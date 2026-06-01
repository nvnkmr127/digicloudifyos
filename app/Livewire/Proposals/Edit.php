<?php

namespace App\Livewire\Proposals;

use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Edit extends Component
{
    public Proposal $proposal;

    public string $title = '';

    public string $description = '';

    public $total_amount = 0;

    public string $status = 'draft';

    public string $valid_until = '';

    public function mount(Proposal $proposal): void
    {
        if ($proposal->organization_id !== Auth::user()->organization_id) {
            abort(404);
        }

        $this->proposal = $proposal;
        $this->title = $proposal->title;
        $this->description = (string) ($proposal->description ?? '');
        $this->total_amount = $proposal->total_amount;
        $this->status = (string) ($proposal->status ?? 'draft');
        $this->valid_until = $proposal->valid_until ? $proposal->valid_until->format('Y-m-d') : '';
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,accepted,declined,expired',
            'valid_until' => 'nullable|date',
        ]);

        $this->proposal->update([
            'title' => $this->title,
            'description' => $this->description !== '' ? $this->description : null,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'valid_until' => $this->valid_until !== '' ? $this->valid_until : null,
        ]);

        session()->flash('success', 'Proposal updated.');

        return redirect()->route('proposals.show', $this->proposal);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.proposals.edit');
    }
}
