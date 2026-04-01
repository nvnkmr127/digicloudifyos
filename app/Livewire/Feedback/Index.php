<?php

namespace App\Livewire\Feedback;

use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showCreateModal = false;

    // Create Form
    public $comment = '';
    public $rating = 5;
    public $entity_type = 'general';
    public $entity_id = null;

    protected $rules = [
        'comment' => 'required|string|min:5',
        'rating' => 'required|integer|min:1|max:5',
        'entity_type' => 'required|string',
    ];

    public function createFeedback()
    {
        $this->validate();

        Feedback::create([
            'organization_id' => Auth::user()->organization_id,
            'user_id' => Auth::id(),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id ?: null,
            'status' => 'PENDING',
        ]);

        $this->reset(['comment', 'rating', 'entity_type', 'entity_id', 'showCreateModal']);
        session()->flash('success', 'Thank you for your intelligence pulse. Our team is analyzing your feedback.');
    }

    public function archive($id)
    {
        Feedback::where('organization_id', Auth::user()->organization_id)->findOrFail($id)->update(['status' => 'ARCHIVED']);
    }

    public function render()
    {
        $feedbackItems = Feedback::where('organization_id', Auth::user()->organization_id)
            ->where('status', '!=', 'ARCHIVED')
            ->when($this->search, fn($q) => $q->where('comment', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate(10);

        return view('livewire.feedback.index', [
            'feedbackItems' => $feedbackItems
        ])->layout('layouts.app');
    }
}
