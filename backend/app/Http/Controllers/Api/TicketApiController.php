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
            'message' => ['required', 'string', 'min:10', 'max:5000'],
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
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket submitted successfully',
            'ticket' => [
                'ticket_number' => $ticket->ticket_number,
                'name' => $ticket->name,
                'email' => $ticket->email,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'created_at' => $ticket->created_at->toISOString(),
            ],
        ], 201);
    }

    /**
     * Look up ticket by ticket number.
     */
    public function show(string $ticketNumber): JsonResponse
    {
        $ticket = Ticket::with(['replies' => function ($query) {
            $query->select('id', 'ticket_id', 'sender_type', 'sender_name', 'message', 'created_at');
        }])->where('ticket_number', strtoupper($ticketNumber))->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'ticket' => [
                'ticket_number' => $ticket->ticket_number,
                'name' => $ticket->name,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'created_at' => $ticket->created_at->toISOString(),
                'resolved_at' => $ticket->resolved_at?->toISOString(),
                'replies' => $ticket->replies,
            ],
        ]);
    }
}
