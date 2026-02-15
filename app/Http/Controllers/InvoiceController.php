<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($query) use ($request) {
                $query->orWhereHas('customer', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%');
                });
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $allowedSorts = [
            'id',
            'created_at',
            'updated_at',
        ];

        $sortBy  = request('sort_by', 'id');
        $sortDir = request('sort_dir', 'desc');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        $invoices = (clone $query)
            ->with('customer')
            ->orderBy($sortBy, $sortDir)
            ->paginate(25);

        // $totals = (clone $query)
        //     ->leftJoin('payments', function ($join) {
        //         $join->on('payments.invoice_id', '=', 'invoices.id')
        //             ->where('payments.status', PaymentStatus::Completed);
        //     })
        //     ->selectRaw('
        //         SUM(invoices.total_amount) as totalAmount,
        //         COALESCE(SUM(payments.amount), 0) as paidAmount
        //     ')
        //     ->first();

        $totals = (clone $query)
            ->selectRaw('
                SUM(invoices.total_amount) as totalAmount,
                SUM(invoices.paid_amount) as paidAmount
            ')
            ->first();

        $totalAmount = $totals->totalAmount ?? 0;
        $paidAmount = $totals->paidAmount ?? 0;

        return Inertia::render('Invoice/Index', [
            'invoices' => $invoices,
            'csrf_token' => csrf_token(),
            'statusOptions' => InvoiceStatus::options(),
            'filters' => $request->only(['search', 'status']),
            'sort_by' => $sortBy, 
            'sort_dir' => $sortDir,
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount
        ]);
        
    }

    public function create(Request $request){
        
        $q = $request->get('q');
        $customer_id = $request->get('customer_id');
        $customers = Customer::latest()
            ->when($q, fn ($query) =>
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
            )
            ->when($customer_id, fn ($query) =>
                $query->where('id', $customer_id)
            )
            ->limit(5)          
            ->get()             
            ->map(fn ($c) => [
                'value' => $c->id,
                'label' => "{$c->name} - {$c->phone}",
            ]);
            
        return Inertia::render('Invoice/Create', [
            'initCustomerId' => $customer_id,
            'customers' => $customers,
            'invoiceStatusOptions' => InvoiceStatus::options(),
            'csrf_token' => csrf_token()
        ]);
    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'discount_amount'   => 'nullable|numeric|min:0',
            'paid_amount'       => 'nullable|numeric|min:0',
            'status'            => 'required|string',
            'job_card_id'       => 'nullable|exists:job_cards,id',
            'notes'             => 'nullable|string',
        ]);

        $validatedItems = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_type'           => 'required|string',
            'items.*.product_id'          => 'nullable|exists:products,id',
            'items.*.service_id'          => 'nullable|exists:services,id',
            'items.*.name'                => 'required|string',
            'items.*.quantity'            => 'required|numeric|min:0',
            'items.*.unit_price'          => 'required|numeric|min:0',
            'items.*.unit'                => 'nullable|string',
            'items.*.tax_rate'            => 'nullable|numeric|min:0',
        ],[
            'items.required'              => 'Please add items'
        ]);

        $subtotal = 0;
        $taxAmount = 0;

        foreach ($validatedItems['items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $lineTotal;

            if (!empty($item['tax_rate'])) {
                $taxAmount += $lineTotal * ($item['tax_rate'] / 100);
            }
        }

        $discountAmount = $validated['discount_amount'] ?? 0;
        $totalAmount    = $subtotal + $taxAmount - $discountAmount;
        $paidAmount     = $validated['paid_amount'] ?? 0;
        
        
        $invoice = Invoice::create(array_merge($validated, [
            'subtotal'      => $subtotal,
            'tax_amount'    => $taxAmount,
            'total_amount'  => $totalAmount,
            'paid_amount'   => $paidAmount,
        ]));

        foreach ($validatedItems['items'] as $item) {
            $invoice->items()->create($item);
        }

        Inertia::flash([
            'message' => 'Invoice successfully',
            'type' => 'success'
        ]);
        
        return redirect()
            ->route('invoices.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('customer');

        return Inertia::render('Invoice/Show', [
            'invoice' => $invoice,
            'customer' => $invoice->customer,  
            'invoiceStatusOptions' => InvoiceStatus::options(),
            'invoiceItems' => $invoice->items()->get(), 
        ]);
    }

    /**
     * Edit invoice
     */
    public function edit(Invoice $invoice)
    {
        $customer = Customer::where('id', $invoice->customer_id)
            ->get()
            ->map(fn ($c) => [
                'value' => $c->id,
                'label' => "{$c->name} - {$c->phone}",
            ]);

        $customers = Customer::latest()
            ->limit(5)          
            ->get()             
            ->map(fn ($c) => [
                'value' => $c->id,
                'label' => "{$c->name} - {$c->phone}",
            ]);

        $customers = $customer
            ->merge($customers)
            ->unique('value')
            ->values();   

        return Inertia::render('Invoice/Edit', [
            'invoice' => $invoice,
            'invoiceItems' => $invoice->items()->get(), 
            'customers' => $customers,
            'invoiceStatusOptions' => InvoiceStatus::options(),
            'csrf_token' => csrf_token()
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'discount_amount'   => 'nullable|numeric|min:0',
            'paid_amount'       => 'nullable|numeric|min:0',
            'status'            => 'required|string',
            'job_card_id'       => 'nullable|exists:job_cards,id',
            'notes'             => 'nullable|string',
        ]);

        $validatedItems = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_type'   => 'required|string',
            'items.*.product_id'  => 'nullable|exists:products,id',
            'items.*.service_id'  => 'nullable|exists:services,id',
            'items.*.name'        => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.unit'        => 'nullable|string',
            'items.*.tax_rate'    => 'nullable|numeric|min:0',
        ], [
            'items.required' => 'Please add items'
        ]);

        $subtotal = 0;
        $taxAmount = 0;

        foreach ($validatedItems['items'] as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $lineTotal;

            if (!empty($item['tax_rate'])) {
                $taxAmount += $lineTotal * ($item['tax_rate'] / 100);
            }
        }

        $discountAmount = $validated['discount_amount'] ?? 0;
        $totalAmount    = $subtotal + $taxAmount - $discountAmount;
        $paidAmount     = $validated['paid_amount'] ?? 0;

        $invoice->update(array_merge($validated, [
            'subtotal'      => $subtotal,
            'tax_amount'    => $taxAmount,
            'total_amount'  => $totalAmount,
            'discount_amount'  => $discountAmount,
            'paid_amount'   => $paidAmount,
        ]));

        $invoice->items()->delete();

        foreach ($validatedItems['items'] as $item) {
            $invoice->items()->create($item);
        }

        Inertia::flash([
            'message' => 'Invoice updated successfully',
            'type' => 'success'
        ]);

        return redirect()->route('invoices.show', $invoice);
    }


    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        Inertia::flash([
            'message' => 'Invoice deleted successfully',
            'type' => 'success'
        ]);

        return redirect()
            ->route('invoices.index');
    }
    
}
