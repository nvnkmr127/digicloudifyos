<?php

namespace App\Livewire\Users;

use Livewire\Component;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Edit extends Component
{
    public User $user;
    public $full_name;
    public $email;
    public $role;
    public $status;
    public $password;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->full_name = $user->full_name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->status = $user->status;
    }

    public function update()
    {
        $this->validate([
            'full_name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'role' => 'required|in:ADMIN,MANAGER,VIEWER',
            'status' => 'required|in:ACTIVE,INACTIVE',
            'password' => 'nullable|min:8',
        ]);

        $data = [
            'full_name' => $this->full_name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $this->user->update($data);

        session()->flash('success', 'User updated successfully.');

        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.edit');
    }
}
