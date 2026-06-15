<?php

namespace App\Exports;

use App\Models\EmployeeAttendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly int $userId,
        private readonly int $month,
        private readonly int $year,
    ) {}

    public function collection()
    {
        return EmployeeAttendance::where('user_id', $this->userId)
            ->whereYear('time_date', $this->year)
            ->whereMonth('time_date', $this->month)
            ->orderBy('time_date')
            ->get();
    }

    public function headings(): array
    {
        return ['Date', 'Time', 'Type', 'Notes'];
    }

    public function map($record): array
    {
        return [
            $record->time_date?->format('Y-m-d'),
            $record->time_date?->format('H:i'),
            $record->type->value ?? $record->type,
            $record->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
