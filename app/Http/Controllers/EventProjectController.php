<?php

namespace App\Http\Controllers;

use App\Models\EventProject;
use App\Models\BudgetItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EventProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EventProject::where('user_id', Auth::id())
            ->with(['details.budgetItem', 'cashFlow']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('event_date', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('event_date', '<=', $request->query('date_to'));
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'event_date');
        $sortDir = $request->query('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $eventProjects = $query
            ->withSum('details', 'allocated_amount')
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('EventProjects/Index', [
            'eventProjects' => $eventProjects,
            'filters' => $request->query(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // Load budget items belonging to the user's budgets
        $budgetItems = BudgetItem::whereHas('budget', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['category', 'budget'])->get();

        return Inertia::render('EventProjects/Create', [
            'budgetItems' => $budgetItems,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'status' => 'nullable|string',
            'attachment' => 'nullable|file',
            'cash_flow_id' => 'nullable|exists:cash_flows,id',
            'details' => 'required|array|min:1',
            'details.*.budget_item_id' => 'required|exists:budget_items,id',
            'details.*.allocated_amount' => 'required|numeric|min:0',
            'details.*.approved_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('attachments', 'public');
            }

            $event = EventProject::create([
                'user_id' => Auth::id(),
                'event_name' => $validated['event_name'],
                'event_date' => $validated['event_date'],
                'location' => $validated['location'] ?? null,
                'description' => $validated['description'] ?? null,
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'] ?? 'planned',
                'attachment' => $attachmentPath,
                'cash_flow_id' => $validated['cash_flow_id'] ?? null,
            ]);

            foreach ($validated['details'] as $d) {
                $event->details()->create([
                    'budget_item_id' => $d['budget_item_id'],
                    'allocated_amount' => $d['allocated_amount'],
                    'approved_amount' => $d['approved_amount'] ?? 0,
                ]);
            }
        });

        return redirect()->route('event-projects.index')
            ->with('success', 'Event project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EventProject $eventProject)
    {
        // $this->authorize('view', $eventProject);
        $eventProject->load('details.budgetItem', 'cashFlow', 'user', 'verificator');

        return Inertia::render('EventProjects/Show', [
            'eventProject' => $eventProject,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventProject $eventProject)
    {
        // $this->authorize('update', $eventProject);
        $user = Auth::user();

        $budgetItems = BudgetItem::with(['budget','category'])->get();
        $eventProject->load('details');

        return Inertia::render('EventProjects/Edit', [
            'eventProject' => $eventProject,
            'budgetItems' => $budgetItems,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventProject $eventProject)
    {
        // $this->authorize('update', $eventProject);

        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'note' => 'nullable|string',
            'status' => 'nullable|string',
            'attachment' => 'nullable|file',
            'cash_flow_id' => 'nullable|exists:cash_flows,id',
            'details' => 'required|array|min:1',
            'details.*.budget_item_id' => 'required|exists:budget_items,id',
            'details.*.allocated_amount' => 'required|numeric|min:0',
            'details.*.approved_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $validated, $eventProject) {
            if ($request->hasFile('attachment')) {
                if ($eventProject->attachment) {
                    Storage::disk('public')->delete($eventProject->attachment);
                }
                $eventProject->attachment = $request->file('attachment')->store('attachments', 'public');
            }

            $eventProject->event_name = $validated['event_name'];
            $eventProject->event_date = $validated['event_date'];
            $eventProject->location = $validated['location'] ?? null;
            $eventProject->description = $validated['description'] ?? null;
            $eventProject->note = $validated['note'] ?? null;
            $eventProject->status = $validated['status'] ?? $eventProject->status;
            $eventProject->cash_flow_id = $validated['cash_flow_id'] ?? null;
            $eventProject->save();

            // Replace details
            $eventProject->details()->delete();
            foreach ($validated['details'] as $d) {
                $eventProject->details()->create([
                    'budget_item_id' => $d['budget_item_id'],
                    'allocated_amount' => $d['allocated_amount'],
                    'approved_amount' => $d['approved_amount'] ?? 0,
                ]);
            }
        });

        return redirect()->route('event-projects.index')
            ->with('success', 'Event project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventProject $eventProject)
    {
        // $this->authorize('delete', $eventProject);

        DB::transaction(function () use ($eventProject) {
            if ($eventProject->attachment) {
                Storage::disk('public')->delete($eventProject->attachment);
            }

            $eventProject->delete();
        });

        return redirect()->route('event-projects.index')
            ->with('success', 'Event project deleted successfully.');
    }

    /**
     * Approval event project.
     */
    public function approval($id)
    {
        DB::transaction(function () use ($id) {
            $eventProject = EventProject::findOrFail($id);
            $eventProject->status = 'approved';
            $eventProject->verified_by = Auth::id();
            $eventProject->verified_date = now();
            $eventProject->save();
            // send notification or email if needed
            // $user = User::find($eventProject->user_id);
            // Mail::to($user->email)->send(new MailableClass);
            // return response()->json(['message' => 'Event project approved successfully.', 'data' => $eventProject]);
        });
    }
}
