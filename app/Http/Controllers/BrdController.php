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
            'project_id'   => ['required', 'exists:projects,id'],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'objective'    => ['nullable', 'string'],
            'scope'        => ['nullable', 'string'],
            'stakeholders' => ['nullable', 'string'],
            'priority'     => ['required', Rule::enum(BrdPriority::class)],
        ];
    }

    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_brds'), 403);
        $canViewAll = auth()->user()->hasPermission('view_all_brds');
        $tab        = ($canViewAll && $request->get('tab') === 'all') ? 'all' : 'mine';

        $query = Brd::with(['project', 'creator', 'approver']);

        if ($tab === 'mine') {
            $query->where('created_by', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $brds     = $query->latest()->paginate(15)->withQueryString();
        $projects = Project::orderBy('name')->get();
        $statuses = BrdStatus::cases();
        $priorities = BrdPriority::cases();
        $canApprove = auth()->user()->hasPermission('approve_brds');
        $canExport  = auth()->user()->hasPermission('export_brds');

        return view('brds.index', compact(
            'brds', 'projects', 'statuses', 'priorities', 'tab', 'canViewAll', 'canApprove', 'canExport'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_brds'), 403);
        $data = $request->validate($this->validationRules());

        Brd::create(array_merge($data, [
            'created_by' => auth()->id(),
            'status'     => BrdStatus::Pending->value,
        ]));

        return back()->with('success', 'BRD created and submitted for approval.');
    }

    public function update(Request $request, Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('edit_brds'), 403);
        abort_if($brd->status === BrdStatus::Approved, 403, 'Approved BRDs cannot be edited.');

        $data = $request->validate($this->validationRules());

        $brd->update(array_merge($data, [
            'updated_by'        => auth()->id(),
            'status'            => BrdStatus::Pending->value,
            'approved_by'       => null,
            'approved_at'       => null,
            'rejection_reason'  => null,
        ]));

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
        $brd->load(['project', 'creator', 'approver']);

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
