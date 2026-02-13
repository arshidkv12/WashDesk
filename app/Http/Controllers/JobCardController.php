<?php

namespace App\Http\Controllers;

use App\Enums\JobCardStatus;
use App\Models\Customer;
use App\Models\JobCard;
use App\Models\JobCardFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class JobCardController extends Controller
{
    /**
     * List job cards
     */
    public function index(Request $request)
    { 
        $query = JobCard::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('job_no', 'like', '%' . $request->search . '%')
                ->orWhereHas('customer', function ($query) use ($request) {
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
            'job_no',
            'created_at',
            'updated_at',
            'delivery_date',
            'status',
            'total',
        ];

        $sortBy  = request('sort_by', 'id');
        $sortDir = request('sort_dir', 'desc');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
    
        $jobCards = $query
            ->with('customer')
            ->orderBy($sortBy, $sortDir)
            ->paginate(25);

        $jobCards->load('customer');

        return Inertia::render('JobCards/Index', [
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
            'jobCards' => $jobCards,
            'jobStatusOptions' => JobCardStatus::options(),
            'sort_by' => $sortBy, 
            'sort_dir' => $sortDir
        ]);
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
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

        return Inertia::render('JobCards/Create', [
            'initCustomerId' => $customer_id,
            'customers' => $customers,
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Store new job card
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'item'          => 'required|string|max:255',
            'problem'       => 'required|string',
            'delivery_date' => 'nullable|date',
            'estimated_cost'=> 'nullable|numeric',
        ], [
            'customer_id.required'   => 'Please select a customer before submitting.',
            'customer_id.exists'   => 'Please select a customer before submitting.',
            'item.required'          => 'Please provide the name of the item.',
            'item.max'               => 'The item name may not exceed 255 characters.',
            'problem.required'       => 'Please describe the problem clearly.',
            'delivery_date.date'     => 'Please enter a valid delivery date.',
            'estimated_cost.numeric' => 'Estimated cost must be a valid number.',
        ]);

        
        if ($validator->fails()) {
            Inertia::flash([
                'message' => 'Please fix the errors.',
                'type' => 'error',
                'initCustomerId' => request('customer_id')
            ]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $jobCard = JobCard::create($validator->validated());

        $files = $request->post('upload-files');
        $uploadDir = public_path('uploads');
        $destinationDir = $uploadDir . DIRECTORY_SEPARATOR . 'images';

        if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true) && !is_dir($destinationDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $destinationDir));
        }

        $now = now();

        $rows = collect((array) $files)->map(fn ($fileName) => [
            'job_id'    => $jobCard->id,
            'user_id'   => Auth::id(),
            'file_name' => $fileName,
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        JobCardFile::insert($rows);

        Inertia::flash([
            'message' => 'Job card created successfully',
            'type' => 'success'
        ]);
        
        return redirect()
            ->route('job-cards.index');
    }

    /**
     * View job card
     */
    public function show(JobCard $jobCard)
    {
        $jobCard->load('customer');
        $jobCard->load('files');

        $diffDays = null;
        if($jobCard->delivery_date){
            $delivery = Carbon::parse($jobCard->delivery_date)->startOfDay();
            $today = Carbon::today();

            $diffDays = (int) ceil(
                $today->diffInSeconds($delivery, false) / 86400
            );
        }

        return Inertia::render('JobCards/Show', [
            'jobCard' => $jobCard,
            'customer' => $jobCard->customer,  
            'jobStatusOptions' => JobCardStatus::options(),
            'deliveryDiffDays' => $diffDays,
        ]);
    }

    /**
     * Edit job card
     */
    public function edit(JobCard $jobCard)
    {
        $customer = Customer::where('id', $jobCard->customer_id)
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

        $jobCardFiles  = $jobCard->files->map(fn ($file) => [
                'source' => $file->file_name,
                'options' => ['type' => 'local']
            ]
        );

        return Inertia::render('JobCards/Edit', [
            'jobCard' => $jobCard,
            'customers' => $customers,
            'jobCardFiles' => $jobCardFiles,
            'jobStatusOptions' => JobCardStatus::options(),
            'csrf_token' => csrf_token()
        ]);
    }

    /**
     * Update job card
     */
    public function update(Request $request, JobCard $jobCard)
    {

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'item'          => 'required|string|max:255',
            'problem'       => 'required|string',
            'delivery_date' => 'nullable|date',
            'estimated_cost'=> 'nullable|numeric',
            'notes' => 'nullable|string',
            'status' => 'nullable|string',
        ], [
            'customer_id.required'   => 'Please select a customer before submitting.',
            'customer_id.exists'   => 'Please select a customer before submitting.',
            'item.required'          => 'Please provide the name of the item.',
            'item.max'               => 'The item name may not exceed 255 characters.',
            'problem.required'       => 'Please describe the problem clearly.',
            'delivery_date.date'     => 'Please enter a valid delivery date.',
            'estimated_cost.numeric' => 'Estimated cost must be a valid number.',
        ]);
        
        $jobCard->update($validated);

        $files = $request->post('upload-files');

        $existingFiles = $jobCard->files()
                        ->pluck('file_name')
                        ->toArray(); 
        $deletedFiles = array_diff($existingFiles, (array) $files);
        
        if (!empty($deletedFiles)) {
            $jobCard->files()
                ->whereIn('file_name', $deletedFiles)
                ->delete();
        }

        foreach ((array) $files as $fileName) {

            JobCardFile::firstOrCreate(
                [
                    'job_id'    => $jobCard->id,
                    'file_name' => $fileName,
                ],
                [
                    'user_id' => Auth::id(),
                ]
            );
        }

        Inertia::flash([
            'message' => 'Job card updated successfully',
            'type' => 'success'
        ]);

        return redirect()
            ->route('job-cards.show', $jobCard);

    }

    /**
     * Printable view
     */
    public function print(JobCard $jobCard)
    {
        return Inertia::render('JobCards/Print', [
            'jobCard' => $jobCard,
        ]);
    }




    /**
     * Display job cards for a specific customer.
     * Route: GET /customer-job-cards/{customer}?status=all&search=...
     */
    public function jobCardsByCustomer(Request $request, Customer $customer)
    { 
        $query = JobCard::query() ->where('customer_id', $customer->id);

        if ($request->has('search') && $request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('job_no', 'like', '%' . $request->search . '%')
                ->orWhereHas('customer', function ($query) use ($request) {
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
            'job_no',
            'created_at',
            'updated_at',
            'delivery_date',
            'status',
            'total',
        ];

        $sortBy  = request('sort_by', 'id');
        $sortDir = request('sort_dir', 'desc');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
    
        $jobCards = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(25);

        $jobCards->load('customer');

        return Inertia::render('JobCards/CustomerIndex', [
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
            'jobCards' => $jobCards,
            'jobStatusOptions' => JobCardStatus::options(),
            'sort_by' => $sortBy, 
            'sort_dir' => $sortDir,
            'customer' => $customer
        ]);
    }


     /**
     * Delete a JobCard
     */
    public function destroy(JobCard $jobCard)
    {
        $jobCard->delete();

        Inertia::flash([
            'message' => 'Job card deleted successfully',
            'type' => 'success'
        ]);

        return redirect()
            ->route('job-cards.index');
    }

}
