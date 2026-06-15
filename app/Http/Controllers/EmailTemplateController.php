<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    private const TYPES = [
        'job_offer' => 'Job Offer',
        'nda'       => 'Non-Disclosure Agreement',
        'contract'  => 'Employment Contract',
    ];

    public function index()
    {
        abort_unless(auth()->user()->hasPermission('view_email_templates'), 403);
        $templates = collect(self::TYPES)->map(
            fn ($label, $type) => EmailTemplate::forType($type)
        );

        return view('email-templates.index', [
            'templates' => $templates,
            'types'     => self::TYPES,
        ]);
    }

    public function update(Request $request, string $type)
    {
        abort_unless(auth()->user()->hasPermission('edit_email_templates'), 403);
        abort_unless(array_key_exists($type, self::TYPES), 404);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body'    => ['required', 'string'],
        ]);

        EmailTemplate::forType($type)->update($validated);

        return back()->with('success', 'Email template updated.');
    }
}
