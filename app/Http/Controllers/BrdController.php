<?php

namespace App\Http\Controllers;

use App\Enums\BrdPriority;
use App\Enums\BrdStatus;
use App\Models\Brd;
use App\Models\BrdAttachment;
use App\Models\Project;
use App\Notifications\BrdStatusNotification;
use App\Services\AiCanvasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Mpdf\Mpdf;
use Throwable;

class BrdController extends Controller
{
    private const ATTACHMENT_MIMES = 'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip';

    private function validationRules(): array
    {
        return [
            'project_id'              => ['required', 'exists:projects,id'],
            'department'              => ['nullable', 'string', 'max:255'],
            'direct_manager'          => ['nullable', 'string', 'max:255'],
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
            'attachments'                   => ['nullable', 'array'],
            'attachments.*'                 => ['file', 'max:20480', 'mimes:' . self::ATTACHMENT_MIMES],
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

        return view('brds.index', compact(
            'brds', 'projects', 'statuses', 'priorities', 'tab', 'canViewAll', 'canApprove', 'canEditBrd', 'canExport'
        ));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('create_brds'), 403);
        $projects = Project::orderBy('name')->get();

        return view('brds.create', compact('projects'));
    }

    public function edit(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('edit_brds'), 403);
        abort_if($brd->status === BrdStatus::Approved, 403, 'Approved BRDs cannot be edited.');

        $canViewAll = auth()->user()->hasPermission('view_all_brds');
        abort_unless($canViewAll || $brd->created_by === auth()->id(), 403);

        $brd->load(['stakeholders', 'attachments.user']);
        $projects = Project::orderBy('name')->get();

        return view('brds.edit', compact('brd', 'projects'));
    }

    public function show(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('view_brds'), 403);
        $canViewAll = auth()->user()->hasPermission('view_all_brds');
        abort_unless($canViewAll || $brd->created_by === auth()->id(), 403);

        $brd->load(['project', 'creator', 'updater', 'approver', 'tasks', 'stakeholders', 'attachments.user']);

        $canApprove = auth()->user()->hasPermission('approve_brds');
        $canEditBrd = auth()->user()->hasPermission('edit_brds');
        $canDeleteBrd = auth()->user()->hasPermission('delete_brds');
        $canExport  = auth()->user()->hasPermission('export_brds');
        $canShare   = auth()->user()->hasPermission('share_brds');

        return view('brds.show', compact('brd', 'canApprove', 'canEditBrd', 'canDeleteBrd', 'canExport', 'canShare'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_brds'), 403);
        $data = $request->validate($this->validationRules());

        $stakeholders = $this->stakeholderRows($data);
        $attachments  = $data['attachments'] ?? [];
        unset($data['stakeholders'], $data['attachments']);

        $brd = Brd::create(array_merge($data, [
            'created_by' => auth()->id(),
            'status'     => BrdStatus::Pending->value,
        ]));

        foreach ($stakeholders as $row) {
            $brd->stakeholders()->create($row);
        }

        foreach ($attachments as $file) {
            $this->createAttachment($brd, $file);
        }

        return redirect()->route('brds.show', $brd)->with('success', 'BRD created and submitted for approval.');
    }

    public function update(Request $request, Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('edit_brds'), 403);
        abort_if($brd->status === BrdStatus::Approved, 403, 'Approved BRDs cannot be edited.');

        $data = $request->validate($this->validationRules());

        $stakeholders = $this->stakeholderRows($data);
        $attachments  = $data['attachments'] ?? [];
        unset($data['stakeholders'], $data['attachments']);

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

        foreach ($attachments as $file) {
            $this->createAttachment($brd, $file);
        }

        return redirect()->route('brds.show', $brd)->with('success', 'BRD updated and resubmitted for approval.');
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

        return redirect()->route('brds.index')->with('success', 'BRD deleted.');
    }

    public function enableShare(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('share_brds'), 403);

        if (! $brd->share_token) {
            $brd->update(['share_token' => Str::random(40)]);
        }

        return back()->with('success', 'Public link enabled.');
    }

    public function regenerateShare(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('share_brds'), 403);
        $brd->update(['share_token' => Str::random(40)]);

        return back()->with('success', 'Public link regenerated. The previous link no longer works.');
    }

    public function disableShare(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('share_brds'), 403);
        $brd->update(['share_token' => null]);

        return back()->with('success', 'Public link disabled.');
    }

    public function publicShow(string $token)
    {
        $brd = Brd::where('share_token', $token)->firstOrFail();
        $brd->load(['project', 'creator', 'stakeholders', 'attachments']);

        return view('brds.public-show', compact('brd'));
    }

    public function destroyAttachment(Brd $brd, BrdAttachment $attachment)
    {
        abort_unless(auth()->user()->hasPermission('edit_brds'), 403);
        abort_if($brd->status === BrdStatus::Approved, 403, 'Approved BRDs cannot be edited.');
        abort_if($attachment->brd_id !== $brd->id, 403);

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    private function createAttachment(Brd $brd, $file): BrdAttachment
    {
        $path = $file->store('brd-attachments/' . $brd->id, 'public');

        return BrdAttachment::create([
            'brd_id'    => $brd->id,
            'user_id'   => auth()->id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $this->attachmentType($file->getClientOriginalExtension()),
            'file_size' => $file->getSize(),
        ]);
    }

    private function attachmentType(string $extension): string
    {
        $extension = strtolower($extension);

        return match (true) {
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) => 'photo',
            $extension === 'pdf'                                             => 'pdf',
            in_array($extension, ['doc', 'docx'], true)                      => 'word',
            in_array($extension, ['xls', 'xlsx'], true)                      => 'excel',
            in_array($extension, ['ppt', 'pptx'], true)                      => 'powerpoint',
            default                                                          => 'file',
        };
    }

    public function export(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('export_brds'), 403);
        $brd->load(['project', 'creator', 'approver', 'stakeholders']);

        $pdf = $this->renderPdf('brds.pdf', ['brd' => $brd]);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="brd-' . $brd->id . '.pdf"',
        ]);
    }

    public function generateAiCanvas(Brd $brd, AiCanvasService $canvas)
    {
        abort_unless(auth()->user()->hasPermission('export_brds'), 403);
        $brd->load(['project', 'stakeholders']);

        try {
            $data = $canvas->generate($brd);
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Could not generate the AI Canvas: ' . $e->getMessage());
        }

        $brd->update([
            'ai_canvas_data'         => $data,
            'ai_canvas_generated_at' => now(),
        ]);

        return back()->with('success', 'AI Canvas generated.');
    }

    public function exportAiCanvas(Brd $brd)
    {
        abort_unless(auth()->user()->hasPermission('export_brds'), 403);
        abort_if(empty($brd->ai_canvas_data), 404, 'No AI Canvas has been generated for this BRD yet.');

        $brd->load('project');

        $pdf = $this->renderPdf('brds.canvas-pdf', ['brd' => $brd, 'canvas' => $brd->ai_canvas_data]);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="ai-canvas-brd-' . $brd->id . '.pdf"',
        ]);
    }

    private function renderPdf(string $view, array $data): string
    {
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

        $mpdf->WriteHTML(view($view, $data)->render());

        return $mpdf->Output('', 'S');
    }
}
