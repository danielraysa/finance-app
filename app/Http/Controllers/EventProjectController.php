<?php

namespace App\Http\Controllers;

use App\Enums\RolesEnum;
use App\Helpers\Formatter;
use App\Models\EventProject;
use App\Models\BudgetItem;
use App\Models\CashAccount;
use App\Models\CashFlow;
use App\Models\User;
use App\Notifications\EventProjectApproval;
use App\Notifications\EventProjectCreated;
use App\Notifications\EventProjectRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EventProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EventProject::with(['details.budgetItem', 'cashFlow']);

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
        $sortBy = $request->query('sort_by', 'created_at');
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
            // 'cash_flow_id' => 'nullable|exists:cash_flows,id',
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
            $creator = $request->user();
            $event = EventProject::create([
                'user_id' => $creator->id,
                'event_name' => $validated['event_name'],
                'event_date' => $validated['event_date'],
                'location' => $validated['location'] ?? null,
                'description' => $validated['description'] ?? null,
                'note' => $validated['note'] ?? null,
                'status' => $validated['status'] ?? 'planned',
                'attachment' => $attachmentPath,
                // 'cash_flow_id' => $validated['cash_flow_id'] ?? null,
            ]);

            $details = collect();

            foreach ($validated['details'] as $d) {
                $details->push([
                    'budget_item_id' => $d['budget_item_id'],
                    'allocated_amount' => $d['allocated_amount'],
                    'approved_amount' => $d['approved_amount'] ?? 0,
                ]);
            }
            $event->details()->createMany($details);

            // send notification to verificator
            $users = User::role(RolesEnum::VERIFICATOR)->get();
            Notification::send($users, new EventProjectCreated($creator, $event));
            $user = $request->user();
            activity()->performedOn($event)->log($user->name . ' membuat kegiatan baru: ' . $event->event_name);
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
        $eventProject->load('details.budgetItem.budget', 'details.budgetItem.category', 'cashFlow', 'user', 'verificator');

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
            // 'cash_flow_id' => 'nullable|exists:cash_flows,id',
            'details' => 'required|array|min:1',
            'details.*.budget_item_id' => 'required|exists:budget_items,id',
            'details.*.allocated_amount' => 'required|numeric|min:0',
            'details.*.approved_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $validated, $eventProject) {
            $eventProject->lockForUpdate();
            if ($request->hasFile('attachment')) {
                if ($eventProject->attachment) {
                    Storage::disk('public')->delete($eventProject->attachment);
                }
                $eventProject->attachment = $request->file('attachment')->store('attachments', 'public');
            }

            if ($eventProject->status !== 'approved') {
                // reset verified_by and verified_date
                $eventProject->verified_by = null;
                $eventProject->verified_date = null;
                $eventProject->rejection_reason = null;
            }
            $eventProject->event_name = $validated['event_name'];
            $eventProject->event_date = $validated['event_date'];
            $eventProject->location = $validated['location'] ?? null;
            $eventProject->description = $validated['description'] ?? null;
            $eventProject->note = $validated['note'] ?? null;
            $eventProject->status = $validated['status'] ?? $eventProject->status;
            // $eventProject->cash_flow_id = $validated['cash_flow_id'] ?? null;
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
            $user = $request->user();
            activity()->performedOn($eventProject)->log($user->name . ' melakukan perubahan data kegiatan: ' . $eventProject->event_name);
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
            $user = Auth::user();
            activity()->performedOn($eventProject)->log($user->name . ' menghapus kegiatan: ' . $eventProject->event_name);
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
            $eventProject = EventProject::with('details')->lockForUpdate()->findOrFail($id);
            $eventProject->status = 'approved';
            $eventProject->verified_by = Auth::id();
            $eventProject->verified_date = now();
            $eventProject->save();
            foreach ($eventProject->details as $detail) {
                // Update approved amount to match allocated amount upon approval
                $detail->approved_amount = $detail->allocated_amount;
                $detail->save();
            }

            // send notification or email if needed
            $user = User::find($eventProject->user_id);
            Notification::send($user, new EventProjectApproval($eventProject));
            // return response()->json(['message' => 'Event project approved successfully.', 'data' => $eventProject]);
            $user = Auth::user();
            activity()->performedOn($eventProject)->log($user->name . ' memberikan persetujuan pada kegiatan: ' . $eventProject->event_name);
        });
    }

    /**
     * Reject event project with reason.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $eventProject = EventProject::lockForUpdate()->findOrFail($id);
            $eventProject->status = 'rejected';
            $eventProject->rejection_reason = $validated['rejection_reason'];
            $eventProject->verified_by = Auth::id();
            $eventProject->verified_date = now();
            $eventProject->save();

            // Send notification to the project creator
            $user = User::find($eventProject->user_id);
            Notification::send($user, new EventProjectRejected($eventProject, $validated['rejection_reason']));

            $authenticatedUser = Auth::user();
            activity()->performedOn($eventProject)->log($authenticatedUser->name . ' menolak kegiatan: ' . $eventProject->event_name);
        });

        return redirect()->route('event-projects.index')
            ->with('success', 'Event project rejected successfully.');
    }

    /**
     * Generate cash flow for the event project.
     */
    public function generateCashFlow($id)
    {
        DB::transaction(function () use ($id) {
            $eventProject = EventProject::with('details.budgetItem')->lockForUpdate()->findOrFail($id);
            if ($eventProject->cash_flow_id) {
                // Cash flow already generated
                return;
            }
            // Create cash flow logic here
            $cashFlow = $this->createCashFlowFromEventProject($eventProject);
            $eventProject->cash_flow_id = $cashFlow->id;
            $eventProject->save();

            $user = Auth::user();
            activity()->performedOn($cashFlow)->log($user->name . ' generate arus dana ' . $cashFlow->reference_number . ' dari kegiatan: ' . $eventProject->event_name);
        });
    }

    public function createCashFlowFromEventProject(EventProject $eventProject)
    {
        // generate reference number
        $now = now();
        $number = CashFlow::whereYear('created_at', $now->year)->count() + 1;
        $referenceNumber = Formatter::generateCashFlowReferenceNumber();

        // Create a new CashFlow based on the EventProject details
        $cashFlow = CashFlow::create([
            'name' => 'Cash Flow for ' . $eventProject->event_name,
            'description' => 'Generated from Event Project: ' . $eventProject->event_name,
            'user_id' => $eventProject->user_id,
            'transaction_date' => $now,
            'reference_number' => $referenceNumber,
        ]);

        $transactions = collect();
        $cashAccount = CashAccount::where('is_active', true)->first();
        foreach ($eventProject->details as $detail) {
            // Create cash flow transactions based on approved amounts
            $transactions->push([
                'cash_account_id' => $cashAccount->id,
                'cash_flow_id' => $cashFlow->id,
                'transaction_category_id' => $detail->budgetItem->transaction_category_id,
                'type' => 'expense', // assuming event project expenses
                'budget_item_id' => $detail->budget_item_id,
                'transaction_date' => $now,
                'amount' => $detail->approved_amount,
                'description' => 'From Event Project: ' . $eventProject->event_name,
            ]);
        }

        $cashFlow->transactions()->createMany($transactions);

        return $cashFlow;
    }
}
