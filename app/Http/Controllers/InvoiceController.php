<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('view_invoices'), 403);
        $query = Invoice::with(['client', 'project', 'createdBy', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhere('title', 'like', '%' . $request->search . '%');
            });
        }

        $invoices       = $query->latest('issue_date')->paginate(15)->withQueryString();
        $clients        = Client::orderBy('company_name')->get();
        $projects       = Project::orderBy('name')->get();
        $statuses       = InvoiceStatus::cases();
        $paymentMethods = PaymentMethod::cases();

        return view('invoices.index', compact('invoices', 'clients', 'projects', 'statuses', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('create_invoices'), 403);
        $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'client_id'           => ['required', 'exists:clients,id'],
            'project_id'          => ['nullable', 'exists:projects,id'],
            'issue_date'          => ['required', 'date'],
            'due_date'            => ['required', 'date', 'after_or_equal:issue_date'],
            'tax_rate'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'currency'            => ['nullable', 'string', 'size:3'],
            'description'         => ['nullable', 'string'],
            'notes'               => ['nullable', 'string'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity'    => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
        ]);

        $taxRate  = (float) ($request->tax_rate ?? 0);
        $subtotal = 0;

        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . str_pad(Invoice::withTrashed()->count() + 1, 5, '0', STR_PAD_LEFT),
            'client_id'      => $request->client_id,
            'project_id'     => $request->project_id,
            'title'          => $request->title,
            'description'    => $request->description,
            'issue_date'     => $request->issue_date,
            'due_date'       => $request->due_date,
            'status'         => InvoiceStatus::Draft->value,
            'tax_rate'       => $taxRate,
            'currency'       => $request->currency ?? 'USD',
            'notes'          => $request->notes,
            'created_by'     => auth()->id(),
            'subtotal'       => 0,
            'tax_amount'     => 0,
            'total'          => 0,
        ]);

        foreach ($request->items as $item) {
            $qty    = (float) $item['quantity'];
            $price  = (float) $item['unit_price'];
            $amount = round($qty * $price, 2);
            $subtotal += $amount;

            $invoice->items()->create([
                'description' => $item['description'],
                'quantity'    => $qty,
                'unit_price'  => $price,
                'amount'      => $amount,
            ]);
        }

        $taxAmount = round($subtotal * $taxRate / 100, 2);
        $invoice->update([
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'total'      => $subtotal + $taxAmount,
        ]);

        return back()->with('success', 'Invoice created.');
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_unless(auth()->user()->hasPermission('edit_invoices'), 403);
        $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'client_id'   => ['required', 'exists:clients,id'],
            'project_id'  => ['nullable', 'exists:projects,id'],
            'issue_date'  => ['required', 'date'],
            'due_date'    => ['required', 'date', 'after_or_equal:issue_date'],
            'status'      => ['required', Rule::enum(InvoiceStatus::class)],
            'tax_rate'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'currency'    => ['nullable', 'string', 'size:3'],
            'description' => ['nullable', 'string'],
            'notes'       => ['nullable', 'string'],
        ]);

        $invoice->update($request->only(
            'title', 'client_id', 'project_id', 'issue_date', 'due_date',
            'status', 'tax_rate', 'currency', 'description', 'notes'
        ));

        return back()->with('success', 'Invoice updated.');
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        abort_unless(auth()->user()->hasPermission('record_invoice_payment'), 403);
        $request->validate([
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'method'       => ['required', Rule::enum(PaymentMethod::class)],
            'reference'    => ['nullable', 'string', 'max:255'],
            'notes'        => ['nullable', 'string'],
        ]);

        Payment::create(array_merge(
            $request->only('amount', 'payment_date', 'method', 'reference', 'notes'),
            ['invoice_id' => $invoice->id, 'recorded_by' => auth()->id()]
        ));

        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => InvoiceStatus::Paid->value]);
        }

        return back()->with('success', 'Payment recorded.');
    }

    public function destroy(Invoice $invoice)
    {
        abort_unless(auth()->user()->hasPermission('delete_invoices'), 403);
        $invoice->delete();

        return back()->with('success', 'Invoice deleted.');
    }
}
