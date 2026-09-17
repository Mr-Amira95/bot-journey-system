<?php

namespace App\Http\Controllers;

use App\Enums\BrdPriority;
use App\Enums\BrdStatus;
use App\Models\Brd;
use App\Models\Project;
use App\Notifications\BrdStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Mpdf\Mpdf;

class BrdController extends Controller
{
    private function validationRules(): array
    {
        return [
            'project_id'              => ['required', 'exists:projects,id'],
            'department'              => ['nullable', 'string', 'max:255'],
            'title'                   => ['required', 'string', 'max:255'],
            'description'             => ['required', 'string'],
            'objective'               => ['nullable', 'string'],
            'scope'                   => ['nullable', 'string'],
            'as_is_workflow'          => ['nullable', 'string'],
            'as_is_pain_points'       => ['nullable', 'string'],
            'as_is_existing_systems'  => ['nullable', 'string'],
            'to_be_workflow'          => ['nullable', 'string'],
            'to_be_benefits'          => ['nullable', 'string'],
            'kpis'                    => ['nullable', 'string'],
            'priority'                => ['required', Rule::enum(BrdPriority::class)],
            'stakeholders'                  => ['nullable', 'array'],
            'stakeholders.*.name'           => ['nullable', 'string', 'max:255'],
            'stakeholders.*.role'           => ['nullable', 'string', 'max:255'],
            'stakeholders.*.department'     => ['nullable', 'string', 'max:255'],
            'stakeholders.*.responsibility' => ['nullable', 'string'],
        ];
    }

    private function stakeholderRows(array $data): \Illuminate\Support\Collection
    {
        return collect($data['stakeholders'] ?? [])
            ->filter(fn ($row) => trim($row['name'] ?? '') !== '')
            ->values();
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_brds'), 403);
        $canViewAll = auth()->user()->hasPermission('view_all_brds');
        $tab        = ($canViewAll && $request->get('tab') === 'all') ? 'all' : 'mine';

        $query = Brd::with(['project', 'creator', 'approver', 'stakeholders']);

        if ($tab === 'mine') {
            $query->where('created_by', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $brds       = $query->latest()->paginate(15)->withQueryString();
        $projects   = Project::orderBy('name')->get();
        $statuses   = BrdStatus::cases();
        $priorities = BrdPriority::cases();
        $canApprove = auth()->user()->hasPermission('approve_brds');
        $canEditBrd = auth()->user()->hasPermission('edit_brds');
        $canExport  = auth()->user()->hasPermission('export_brds');

        $editBrd = null;
        if ($canEditBrd && $request->filled('edit')) {
            $editBrd = Brd::with('stakeholders')->find($request->get('edit'));
            if ($editBrd && $tab === 'mine' && $editBrd->created_by !== auth()->id()) {
                $editBrd = null;
            }
        }

        return view('brds.index', compact(
            'brds', 'projects', 'statuses', 'priorities', 'tab', 'canViewAll', 'canApprove', 'canExport', 'editBrd'
        ));
    }

    public function show(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('view_brds'), 403);
        $canViewAll = auth()->user()->hasPermission('view_all_brds');
        abort_unless($canViewAll || $brd->created_by === auth()->id(), 403);

        $brd->load(['project', 'creator', 'updater', 'approver', 'tasks', 'stakeholders']);

        $canApprove = auth()->user()->hasPermission('approve_brds');
        $canEditBrd = auth()->user()->hasPermission('edit_brds');
        $canDeleteBrd = auth()->user()->hasPermission('delete_brds');
        $canExport  = auth()->user()->hasPermission('export_brds');

        return view('brds.show', compact('brd', 'canApprove', 'canEditBrd', 'canDeleteBrd', 'canExport'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_brds'), 403);
        $data = $request->validate($this->validationRules());

        $stakeholders = $this->stakeholderRows($data);
        unset($data['stakeholders']);

        $brd = Brd::create(array_merge($data, [
            'created_by' => auth()->id(),
            'status'     => BrdStatus::Pending->value,
        ]));

        foreach ($stakeholders as $row) {
            $brd->stakeholders()->create($row);
        }

        return back()->with('success', 'BRD created and submitted for approval.');
    }

    public function update(Request $request, Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('edit_brds'), 403);
        abort_if($brd->status === BrdStatus::Approved, 403, 'Approved BRDs cannot be edited.');

        $data = $request->validate($this->validationRules());

        $stakeholders = $this->stakeholderRows($data);
        unset($data['stakeholders']);

        $brd->update(array_merge($data, [
            'updated_by'        => auth()->id(),
            'status'            => BrdStatus::Pending->value,
            'approved_by'       => null,
            'approved_at'       => null,
            'rejection_reason'  => null,
        ]));

        $brd->stakeholders()->delete();
        foreach ($stakeholders as $row) {
            $brd->stakeholders()->create($row);
        }

        return back()->with('success', 'BRD updated and resubmitted for approval.');
    }

    public function approve(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('approve_brds'), 403);
        abort_if($brd->created_by === auth()->id(), 403, 'You cannot approve your own BRD.');

        $brd->update([
            'status'      => BrdStatus::Approved->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $brd->load('project', 'creator');
        $brd->creator?->notify(new BrdStatusNotification($brd));

        return back()->with('success', 'BRD approved.');
    }

    public function reject(Request $request, Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('approve_brds'), 403);
        abort_if($brd->created_by === auth()->id(), 403, 'You cannot reject your own BRD.');

        $request->validate([
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $brd->update([
            'status'            => BrdStatus::Rejected->value,
            'approved_by'       => auth()->id(),
            'approved_at'       => now(),
            'rejection_reason'  => $request->rejection_reason,
        ]);

        $brd->load('project', 'creator');
        $brd->creator?->notify(new BrdStatusNotification($brd));

        return back()->with('success', 'BRD rejected.');
    }

    public function destroy(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('delete_brds'), 403);
        $brd->delete();

        return back()->with('success', 'BRD deleted.');
    }

    public function export(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('export_brds'), 403);
        $brd->load(['project', 'creator', 'approver', 'stakeholders']);

        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode'             => 'utf-8',
            'format'           => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
            'default_font'     => 'dejavusans',
            'tempDir'          => $tempDir,
        ]);

        $mpdf->WriteHTML(view('brds.pdf', ['brd' => $brd])->render());

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="brd-' . $brd->id . '.pdf"',
        ]);
    }
}
