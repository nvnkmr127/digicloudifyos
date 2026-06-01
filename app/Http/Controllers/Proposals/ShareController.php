<?php

namespace App\Http\Controllers\Proposals;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function show(Request $request, string $proposal)
    {
        $proposal = Proposal::query()
            ->with('client')
            ->findOrFail($proposal);

        return view('proposals.share', [
            'proposal' => $proposal,
        ]);
    }
}
