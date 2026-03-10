<?php

namespace App\Livewire\Users;

use Livewire\Component;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Create extends Component
{
    public $full_name = '';
    public $email = '';
    public $role = 'MANAGER';
    public $status = 'ACTIVE';
    public $password = '';

    protected $rules = [
        'full_name' => 'required|min:3',
        'email' => 'required|email|unique:users,email',
        'role' => 'required|in:ADMIN,MANAGER,VIEWER',
        'status' => 'required|in:ACTIVE,INACTIVE',
        'password' => 'required|min:8',
    ];

    public function save()
    {
        $this->validate();

        User::create([
            'organization_id' => Auth::user()->organization_id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'password' => Hash::make($this->password),
        ]);

        session()->flash('success', 'User created successfully.');

        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.create');
    }
}
