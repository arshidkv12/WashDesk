<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * List all customers with optional search
     */
    public function index(Request $request)
    { 
        $customers = Customer::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Customers/Index', [
            'filters' => $request->only(['search']),
            'customers' => $customers,
        ]);
    }

    /**
     * Show form to create a new customer
     */
    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    /**
     * Store a new customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|min:10|max:10|unique:customers,phone',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'address' => 'nullable|string|max:500',
        ]);

        $customer = Customer::create($validated);

        Inertia::flash([
            'message' => 'Customer created successfully',
            'type' => 'success'
        ]);

        if(!empty($request->pageUrl)){
            return redirect($request->pageUrl . '/?customer_id=' . $customer->id);
        }

        return redirect()
            ->route('customers.index');
    }

    /**
     * Show a single customer
     */
    public function show(Customer $customer)
    {
        // Get customer with formatted created_at
        $customerData = [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
            'created_at_formatted' => $customer->created_at->format('M d, Y'),
        ];
        
        // dd($customer->jobCards()->count());
        $stats = [
            'job_cards_count' => $customer->jobCards()->count(),
            // 'invoices_count' => $customer->invoices()->count(),
            // 'total_invoiced' => $customer->invoices()->sum('total_amount') ?? 0,
            // 'pending_invoices' => $customer->invoices()->where('status', 'pending')->count(),
            // 'paid_invoices' => $customer->invoices()->where('status', 'paid')->count(),
            // 'overdue_invoices' => $customer->invoices()->where('status', 'overdue')->count(),
        ];
        
        // Get recent job cards (optional - for a recent activity section)
        $recentJobCards = $customer->jobCards()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($jobCard) => [
                'id' => $jobCard->id,
                'job_number' => $jobCard->job_number,
                'title' => $jobCard->title,
                'status' => $jobCard->status,
                'created_at' => $jobCard->created_at->format('M d, Y'),
            ]);
        
        // Get recent invoices (optional)
        // $recentInvoices = $customer->invoices()
        //     ->latest()
        //     ->limit(5)
        //     ->get()
        //     ->map(fn($invoice) => [
        //         'id' => $invoice->id,
        //         'invoice_number' => $invoice->invoice_number,
        //         'total_amount' => $invoice->total_amount,
        //         'status' => $invoice->status,
        //         'due_date' => $invoice->due_date->format('M d, Y'),
        //     ]);
        
        return Inertia::render('Customers/Show', [
            'customer' => $customerData,
            'stats' => $stats,
            // 'recentJobCards' => $recentJobCards,
            // 'recentInvoices' => $recentInvoices,
        ]);
    }
    

    /**
     * Show form to edit a customer
     */
    public function edit(Customer $customer)
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update a customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string|max:500',
        ]);

        $customer->update($validated);

        Inertia::flash([
            'message' => 'Customer updated successfully',
            'type' => 'success'
        ]);

        return redirect()
            ->route('customers.show', $customer);
    }

    /**
     * Delete a customer
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();


        Inertia::flash([
            'message' => 'Customer deleted successfully',
            'type' => 'success'
        ]);

        return redirect()
            ->route('customers.index');
    }

    /**
     * Search
     */
    public function search(Request $request)
    {
        $q = $request->get('q');

        return Customer::query()
            ->when($q, fn ($query) =>
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
            )
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'value' => $c->id,
                'label' => "{$c->name} - {$c->phone}",
        ]);
    }
}
