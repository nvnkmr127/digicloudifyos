<?php

namespace App\Livewire\Users;

use Livewire\Component;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $search = '';

    public function delete($id)
    {
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        if ($id === Auth::id()) {
            session()->flash('error', 'You cannot delete yourself.');
            return;
        }

        $user = User::where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);
        $user->delete();

        session()->flash('success', 'User deleted successfully.');
    }

    public function render()
    {
        if (! Auth::user()?->isAdmin()) {
            abort(403);
        }

        $users = User::where('organization_id', Auth::user()->organization_id)
            ->when($this->search, function ($query) {
                $query->where(function ($nestedQuery) {
                    $nestedQuery->where('full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('full_name')
            ->get();

        return view('livewire.users.index', [
            'users' => $users
        ]);
    }
}
