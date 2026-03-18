<?php

namespace App\Livewire\Users;

use Livewire\Component;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        if ($user->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }

        $this->user = $user;
        $this->full_name = $user->full_name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->status = $user->status;
    }

    public function update()
    {
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        if ($this->user->organization_id !== Auth::user()->organization_id) {
            abort(403);
        }

        $this->validate([
            'full_name' => 'required|min:3',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($this->user->id)
                    ->where(function ($query) {
                        return $query->where('organization_id', Auth::user()->organization_id);
                    }),
            ],
            'role' => 'required|in:ADMIN,ANALYST,OPERATOR,VIEWER',
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
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        return view('livewire.users.edit');
    }
}
