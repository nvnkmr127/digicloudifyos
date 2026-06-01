<?php

namespace App\Livewire\Clients;

use App\Models\BrandKit as BrandKitModel;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BrandKit extends Component
{
    use AuthorizesRequests;

    public Client $client;

    public string $logos = '';

    public string $colors = '';

    public string $fonts = '';

    public string $tone = '';

    public string $dos = '';

    public string $donts = '';

    public string $approvedClaims = '';

    public string $restrictedClaims = '';

    public function mount(Client $client): void
    {
        $this->client = $client;
        $this->authorize('update', $client);

        $kit = BrandKitModel::firstOrCreate([
            'organization_id' => $client->organization_id,
            'client_id' => $client->id,
        ]);

        $identity = $kit->identity ?? [];
        $voice = $kit->voice ?? [];
        $claims = $kit->claims ?? [];

        $this->logos = implode("\n", (array) ($identity['logos'] ?? []));
        $this->colors = implode("\n", (array) ($identity['colors'] ?? []));
        $this->fonts = implode("\n", (array) ($identity['fonts'] ?? []));
        $this->tone = (string) ($voice['tone'] ?? '');
        $this->dos = implode("\n", (array) ($voice['dos'] ?? []));
        $this->donts = implode("\n", (array) ($voice['donts'] ?? []));
        $this->approvedClaims = implode("\n", (array) ($claims['approved'] ?? []));
        $this->restrictedClaims = implode("\n", (array) ($claims['restricted'] ?? []));
    }

    public function save(): void
    {
        $this->authorize('update', $this->client);

        $this->validate([
            'tone' => 'nullable|string|max:255',
            'colors' => 'nullable|string', // Validated in processing
        ]);

        $kit = BrandKitModel::where('organization_id', $this->client->organization_id)
            ->where('client_id', $this->client->id)
            ->first();

        if (! $kit) {
            $kit = BrandKitModel::create([
                'organization_id' => $this->client->organization_id,
                'client_id' => $this->client->id,
            ]);
        }

        // D016: Sanitize and validate colors
        $validColors = collect($this->textToList($this->colors))
            ->filter(fn ($c) => preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $c))
            ->values()
            ->all();

        $kit->update([
            'identity' => [
                'logos' => $this->textToList($this->logos),
                'colors' => $validColors,
                'fonts' => $this->textToList($this->fonts),
            ],
            'voice' => [
                'tone' => strip_tags(trim($this->tone)),
                'dos' => $this->textToList($this->dos),
                'donts' => $this->textToList($this->donts),
            ],
            'claims' => [
                'approved' => $this->textToList($this->approvedClaims),
                'restricted' => $this->textToList($this->restrictedClaims),
            ],
        ]);

        session()->flash('success', 'Brand kit saved.');
    }

    protected function textToList(?string $text): array
    {
        $text = trim((string) $text);
        if ($text === '') {
            return [];
        }

        return collect(preg_split("/\r\n|\n|\r/", $text))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values()
            ->all();
    }

    public function render()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        return view('livewire.clients.brand-kit');
    }
}
