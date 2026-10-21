<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the tickets dashboard.
     */
    public function index(Request $request): View
    {
        $filters = [
            'status' => $request->query('status', 'all'),
            'priority' => $request->query('priority', 'all'),
            'category' => $request->query('category', 'all'),
            'search' => $request->query('search', ''),
        ];

        $tickets = Ticket::withCount('replies')
            ->filter($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'resolved' => Ticket::whereIn('status', ['resolved', 'closed'])->count(),
            'urgent' => Ticket::whereIn('priority', ['high', 'urgent'])
                ->whereIn('status', ['open', 'in_progress'])
                ->count(),
        ];

        return view('admin.dashboard', compact('tickets', 'stats', 'filters'));
    }

    /**
     * Show form to create a new ticket from dashboard.
     */
    public function create(): View
    {
        return view('admin.create');
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'in:general,bug,feature,technical,account'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'status' => ['required', 'string', 'in:open,in_progress,resolved,closed'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        $ticket = Ticket::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed']) ? now() : null,
        ]);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', "تم إنشاء التذكرة {$ticket->ticket_number} بنجاح.");
    }

    /**
     * Display ticket detail view.
     */
    public function show(Ticket $ticket): View
    {
        $ticket->load('replies');

        return view('admin.show', compact('ticket'));
    }

    /**
     * Show form to edit an existing ticket.
     */
    public function edit(Ticket $ticket): View
    {
        return view('admin.edit', compact('ticket'));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'in:general,bug,feature,technical,account'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'status' => ['required', 'string', 'in:open,in_progress,resolved,closed'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        $ticket->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'message' => $validated['message'],
            'resolved_at' => in_array($validated['status'], ['resolved', 'closed']) ? ($ticket->resolved_at ?? now()) : null,
        ]);

        $ticket->save();

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', "تم تحديث بيانات التذكرة {$ticket->ticket_number} بنجاح.");
    }

    /**
     * Post a reply to the ticket.
     */
    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
            'sender_name' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:open,in_progress,resolved,closed'],
        ]);

        $ticket->replies()->create([
            'sender_type' => 'admin',
            'sender_name' => !empty($validated['sender_name']) ? $validated['sender_name'] : 'فريق دعم Zadex',
            'message' => $validated['message'],
        ]);

        if (!empty($validated['status']) && $validated['status'] !== $ticket->status) {
            $ticket->status = $validated['status'];
            if (in_array($validated['status'], ['resolved', 'closed'])) {
                $ticket->resolved_at = now();
            } else {
                $ticket->resolved_at = null;
            }
            $ticket->save();
        }

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'تم إرسال الرد وتحديث التذكرة بنجاح.');
    }

    /**
     * Update ticket status or priority.
     */
    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:open,in_progress,resolved,closed'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
        ]);

        if (!empty($validated['status'])) {
            $ticket->status = $validated['status'];
            if (in_array($validated['status'], ['resolved', 'closed'])) {
                $ticket->resolved_at = now();
            } elseif ($ticket->status === 'open' || $ticket->status === 'in_progress') {
                $ticket->resolved_at = null;
            }
        }

        if (!empty($validated['priority'])) {
            $ticket->priority = $validated['priority'];
        }

        $ticket->save();

        return back()->with('success', 'تم تحديث حالة التذكرة بنجاح.');
    }

    /**
     * Delete a ticket.
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        $number = $ticket->ticket_number;
        $ticket->replies()->delete();
        $ticket->delete();

        return redirect()->route('admin.tickets.index')
            ->with('success', "تم حذف التذكرة $number بنجاح.");
    }
}
