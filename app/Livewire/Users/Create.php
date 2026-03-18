<?php

namespace App\Livewire\Users;

use Livewire\Component;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Create extends Component
{
    public $full_name = '';
    public $email = '';
    public $role = 'ANALYST';
    public $status = 'ACTIVE';
    public $password = '';

    public function save()
    {
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        $this->validate([
            'full_name' => 'required|min:3',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->where(function ($query) {
                    return $query->where('organization_id', Auth::user()->organization_id);
                }),
            ],
            'role' => 'required|in:ADMIN,ANALYST,OPERATOR,VIEWER',
            'status' => 'required|in:ACTIVE,INACTIVE',
            'password' => 'required|min:8',
        ]);

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
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        return view('livewire.users.create');
    }
}
