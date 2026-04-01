<?php

namespace App\Livewire\Forms;

use App\Models\Form;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public $name = '';
    public $description = '';
    public $status = 'ACTIVE';
    public $fields = []; // items: {type, name, label, placeholder, required}

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:ACTIVE,INACTIVE',
        'fields' => 'required|array|min:1',
        'fields.*.type' => 'required|string',
        'fields.*.name' => 'required|string|regex:/^[a-zA-Z0-9_]+$/',
        'fields.*.label' => 'required|string',
        'fields.*.required' => 'boolean',
    ];

    public function mount()
    {
        // Add a default name field
        $this->addField();
    }

    public function addField()
    {
        $this->fields[] = [
            'id' => uniqid(),
            'type' => 'text',
            'name' => '',
            'label' => '',
            'placeholder' => '',
            'required' => false,
        ];
    }

    public function removeField($index)
    {
        unset($this->fields[$index]);
        $this->fields = array_values($this->fields);
    }

    public function saveForm()
    {
        $this->validate();

        Form::create([
            'organization_id' => Auth::user()->organization_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'fields' => $this->fields,
        ]);

        session()->flash('success', 'Lead Capture Form initialized successfully.');

        return redirect()->route('forms.index');
    }

    public function render()
    {
        return view('livewire.forms.create')->layout('layouts.app');
    }
}
