<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly Request $request) {}

    public function collection()
    {
        $query = Expense::with(['category', 'paidBy', 'project'])
            ->orderBy('expense_date', 'desc');

        if ($this->request->filled('from')) {
            $query->where('expense_date', '>=', $this->request->from);
        }
        if ($this->request->filled('to')) {
            $query->where('expense_date', '<=', $this->request->to);
        }
        if ($this->request->filled('employee_id')) {
            $query->where('paid_by', $this->request->employee_id);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['Date', 'Title', 'Category', 'Project', 'Paid By', 'Amount', 'Status'];
    }

    public function map($expense): array
    {
        return [
            $expense->expense_date?->format('Y-m-d'),
            $expense->title,
            $expense->category?->name ?? '—',
            $expense->project?->name ?? '—',
            $expense->paidBy?->name ?? '—',
            number_format((float) $expense->amount, 2),
            $expense->status->value ?? $expense->status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
