<?php

namespace App\Livewire\Media;

use App\Models\CreativeAsset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public $upload;
    public $search = '';

    protected $rules = [
        'upload' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,pdf,doc,docx,zip,csv|max:51200', // 50MB max
    ];

    public function updatedUpload()
    {
        $this->validate();

        $path = $this->upload->store('creative-assets', 'public');

        CreativeAsset::create([
            'organization_id' => Auth::user()->organization_id,
            'name' => $this->upload->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $this->upload->getMimeType(),
            'file_size' => $this->upload->getSize(),
        ]);

        $this->upload = null;
        session()->flash('success', 'Asset uploaded successfully.');
    }

    public function delete($id)
    {
        $asset = CreativeAsset::findOrFail($id);
        Storage::disk('public')->delete($asset->file_path);
        $asset->delete();
        session()->flash('success', 'Asset deleted.');
    }

    public function render()
    {
        $assets = CreativeAsset::where('organization_id', Auth::user()->organization_id)
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate(12);

        return view('livewire.media.index', [
            'assets' => $assets
        ])->layout('layouts.app');
    }
}
