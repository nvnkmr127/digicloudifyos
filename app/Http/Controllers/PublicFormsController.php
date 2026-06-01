<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PublicFormsController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $form = Form::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $key = (string) $request->query('k', '');
        if ($key === '' || ! hash_equals((string) $form->public_key, $key)) {
            abort(404);
        }

        return view('public.forms.show', [
            'form' => $form,
            'key' => $key,
        ]);
    }

    public function submit(Request $request, string $slug)
    {
        $form = Form::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $key = (string) $request->input('k', '');
        if ($key === '' || ! hash_equals((string) $form->public_key, $key)) {
            abort(404);
        }

        $fields = is_array($form->fields) ? $form->fields : [];

        $rules = ['k' => 'required|string'];
        $payload = [];

        foreach ($fields as $field) {
            $name = (string) ($field['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $type = (string) ($field['type'] ?? 'text');
            $required = (bool) ($field['required'] ?? false);

            $fieldRules = [];
            $fieldRules[] = $required ? 'required' : 'nullable';

            if ($type === 'email') {
                $fieldRules[] = 'email';
                $fieldRules[] = 'max:255';
            } else {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:2000';
            }

            $rules[$name] = implode('|', $fieldRules);
            $payload[$name] = $request->input($name);
        }

        $validated = $request->validate($rules);

        $finalPayload = [];
        foreach ($payload as $name => $value) {
            $finalPayload[$name] = $validated[$name] ?? null;
        }

        FormSubmission::create([
            'form_id' => $form->id,
            'organization_id' => $form->organization_id,
            'payload' => $finalPayload,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
            'referer' => Str::limit((string) $request->headers->get('referer', ''), 255, ''),
            'submitted_at' => Carbon::now(),
        ]);

        return redirect()->to("/f/{$form->slug}?k={$key}")->with('success', 'Submitted successfully.');
    }
}
