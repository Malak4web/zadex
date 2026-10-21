<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketApiController extends Controller
{
    /**
     * List all support tickets with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status', 'all'),
            'priority' => $request->query('priority', 'all'),
            'category' => $request->query('category', 'all'),
            'search' => $request->query('search', ''),
        ];

        $tickets = Ticket::with(['replies' => function ($query) {
            $query->select('id', 'ticket_id', 'sender_type', 'sender_name', 'message', 'created_at')->orderBy('id', 'asc');
        }])
            ->filter($filters)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
        ]);
    }

    /**
     * Submit a new support ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'in:general,bug,feature,technical,account'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
            'status' => ['nullable', 'string', 'in:open,in_progress,resolved,closed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $ticket = Ticket::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'category' => $validated['category'] ?? 'general',
            'priority' => $validated['priority'] ?? 'medium',
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $validated['status'] ?? 'open',
        ]);

        $ticket->load('replies');

        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully',
            'ticket' => $ticket,
        ], 201);
    }

    /**
     * Look up ticket by ticket number.
     */
    public function show(string $ticketNumber): JsonResponse
    {
        $ticket = Ticket::with(['replies' => function ($query) {
            $query->select('id', 'ticket_id', 'sender_type', 'sender_name', 'message', 'created_at')->orderBy('id', 'asc');
        }])->where('ticket_number', strtoupper($ticketNumber))->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
        ]);
    }

    /**
     * Update an existing ticket.
     */
    public function update(Request $request, string $ticketNumber): JsonResponse
    {
        $ticket = Ticket::where('ticket_number', strtoupper($ticketNumber))->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'subject' => ['nullable', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'in:general,bug,feature,technical,account'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'status' => ['nullable', 'string', 'in:open,in_progress,resolved,closed'],
            'message' => ['nullable', 'string', 'min:5', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if (isset($validated['name'])) $ticket->name = $validated['name'];
        if (isset($validated['email'])) $ticket->email = $validated['email'];
        if (isset($validated['subject'])) $ticket->subject = $validated['subject'];
        if (isset($validated['category'])) $ticket->category = $validated['category'];
        if (isset($validated['priority'])) $ticket->priority = $validated['priority'];
        if (isset($validated['message'])) $ticket->message = $validated['message'];

        if (isset($validated['status'])) {
            $ticket->status = $validated['status'];
            if (in_array($validated['status'], ['resolved', 'closed'])) {
                $ticket->resolved_at = $ticket->resolved_at ?? now();
            } else {
                $ticket->resolved_at = null;
            }
        }

        $ticket->save();
        $ticket->load('replies');

        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket,
        ]);
    }

    /**
     * Delete a ticket.
     */
    public function destroy(string $ticketNumber): JsonResponse
    {
        $ticket = Ticket::where('ticket_number', strtoupper($ticketNumber))->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }

        $ticket->replies()->delete();
        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket deleted successfully',
        ]);
    }

    /**
     * Add a reply to a ticket.
     */
    public function reply(Request $request, string $ticketNumber): JsonResponse
    {
        $ticket = Ticket::where('ticket_number', strtoupper($ticketNumber))->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string', 'min:2', 'max:5000'],
            'sender_name' => ['nullable', 'string', 'max:100'],
            'sender_type' => ['nullable', 'string', 'in:admin,customer'],
            'status' => ['nullable', 'string', 'in:open,in_progress,resolved,closed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $reply = $ticket->replies()->create([
            'sender_type' => $validated['sender_type'] ?? 'admin',
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

        return response()->json([
            'success' => true,
            'message' => 'Reply posted successfully',
            'reply' => $reply,
            'ticket_status' => $ticket->status,
        ]);
    }
}
