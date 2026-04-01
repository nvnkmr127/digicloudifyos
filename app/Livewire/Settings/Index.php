<?php

namespace App\Livewire\Settings;

use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $phone;
    public $address;
    public $logo;
    public $currentLogo;

    public function mount()
    {
        $org = Auth::user()->organization;
        $this->name = $org->name;
        $this->email = $org->email ?? '';
        $this->phone = $org->phone ?? '';
        $this->address = $org->address ?? '';
        $this->currentLogo = $org->logo_url;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'logo' => 'nullable|image|max:2048',
        ]);

        $org = Auth::user()->organization;
        
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
        ];

        if ($this->logo) {
            $data['logo_url'] = $this->logo->store('logos', 'public');
        }

        $org->update($data);

        session()->flash('message', 'Settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.settings.index')->layout('layouts.app');
    }
}
