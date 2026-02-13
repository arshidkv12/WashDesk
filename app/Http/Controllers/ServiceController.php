<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('sku', 'like', '%' . $request->search . '%')
                ->orWhere('barcode', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->has('status') && !is_null($request->status)) {
            $query->where('status', $request->status);
        }
    
        $statusOptions = [
            ['value' => '1', 'label' => 'Active'],
            ['value' => '0', 'label' => 'Inactive'],
        ];

        $allowedSorts = [
            'id',
            'created_at',
            'updated_at',
            'quantity',
        ];

        $sortBy  = request('sort_by', 'id');
        $sortDir = request('sort_dir', 'desc');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        $services = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(25);

        return Inertia::render('Services/Index', [
            'services' => $services,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'status']),
            'statusOptions' => $statusOptions,
            'sort_by' => $sortBy, 
            'sort_dir' => $sortDir
        ]);
    }


    public function create(Request $request){
        return Inertia::render('Services/Create');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'sku'         => 'nullable|string',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'barcode'     => 'nullable|string|unique:services,barcode',
            'price'       => 'required|numeric|min:0',
            'tax'         => 'nullable|numeric|min:0',
            'quantity'    => 'nullable|integer|min:0',
            'status'      => 'boolean',
        ],
        [
            'status.boolean' => 'Please select status',
        ]);

        Service::create($data);

        Inertia::flash([
            'message' => 'Service created successfully',
            'type' => 'success'
        ]);

        return redirect()
            ->route('services.index');
    }


    public function show(Service $service)
    {
        return Inertia::render('Services/Show', 
            ['service' => $service]
        );
    }

    /**
     * Edit job card
     */
    public function edit(Service $service)
    {
        return Inertia::render('Services/Edit', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'sku'         => 'nullable|string',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'barcode'     => 'nullable|string|unique:services,barcode,' . $service->id,
            'price'       => 'sometimes|numeric|min:0',
            'tax'         => 'nullable|numeric|min:0',
            'quantity'    => 'sometimes|integer|min:0',
            'status'      => 'boolean',
        ],[
            'status.boolean' => 'Please select status',
        ]);

        $service->update($validated);

        Inertia::flash([
            'message' => 'Service updated successfully',
            'type' => 'success'
        ]);

        return redirect()
            ->route('services.index');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        Inertia::flash([
            'message' => 'Service deleted successfully',
            'type' => 'success'
        ]);

        return redirect()
            ->route('services.index');
    }

    /**
     * Search
     */
    public function search(Request $request)
    {
        $q = $request->get('q');

        return Service::query()
            ->select('id', 'name', 'sku', 'price', 'quantity', 'tax')
            ->when($q, fn ($query) =>
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', '=', $q)
            )
            ->where('quantity', '>', 0)
            ->where('status', 1)
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->makeHidden(['image_url', 'created_at_formatted']);
    }
}
