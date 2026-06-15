<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

class DocumentService
{
    public function generateEmployeeDocuments(Employee $employee): void
    {
        $employee->load(['user', 'department', 'manager.user']);

        $data = $this->buildDocumentData($employee);

        $documents = [
            'job_offer' => ['view' => 'documents.job-offer', 'filename' => 'job-offer.pdf'],
            'nda'       => ['view' => 'documents.nda',       'filename' => 'nda.pdf'],
            'contract'  => ['view' => 'documents.contract',  'filename' => 'contract.pdf'],
        ];

        foreach ($documents as $key => $config) {
            $existing = $employee->user->attachments()->where('key', $key)->first();
            if ($existing) {
                Storage::disk('public')->delete($existing->attachment_path);
                $existing->delete();
            }

            $html = view($config['view'], $data)->render();

            $mpdf = $this->makeMpdf();
            $mpdf->WriteHTML($html);
            $content = $mpdf->Output('', 'S');

            $path = 'employee-attachments/' . $employee->user_id . '/' . $config['filename'];
            Storage::disk('public')->put($path, $content);

            $employee->user->attachments()->create([
                'key'             => $key,
                'attachment_path' => $path,
            ]);
        }
    }

    private function makeMpdf(): Mpdf
    {
        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
            'default_font'      => 'dejavusans',
            'tempDir'           => $tempDir,
        ]);
    }

    private function buildDocumentData(Employee $employee): array
    {
        $salary     = $employee->salary     ? '$' . number_format((float) $employee->salary, 2) . ' / month' : null;
        $hourlyRate = $employee->hourly_rate ? '$' . number_format((float) $employee->hourly_rate, 2) . ' / hr'  : null;

        $logoPath = public_path('logo.png');
        $logoSrc  = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        return [
            'employee_name'   => $employee->user->name,
            'employee_email'  => $employee->user->email,
            'position'        => $employee->position,
            'department'      => $employee->department?->name ?? '—',
            'hire_date'           => $employee->hire_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'hire_date_plus_year' => $employee->hire_date?->copy()->addYear()->format('Y-m-d') ?? now()->addYear()->format('Y-m-d'),
            'employment_type' => ucwords(str_replace('_', ' ', $employee->type->value)),
            'salary'          => $salary,
            'salary_amount'   => $employee->salary ? number_format((float) $employee->salary, 2) : null,
            'hourly_rate'     => $hourlyRate,
            'manager'         => $employee->manager?->user->name,
            'date'            => now()->format('Y-m-d'),
            'logo_src'        => $logoSrc,
        ];
    }
}
