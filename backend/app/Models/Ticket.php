<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'name',
        'email',
        'subject',
        'category',
        'priority',
        'status',
        'message',
        'ip_address',
        'user_agent',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateUniqueTicketNumber();
            }
        });
    }

    public static function generateUniqueTicketNumber(): string
    {
        do {
            $number = 'ZX-' . strtoupper(Str::random(5));
        } while (static::where('ticket_number', $number)->exists());

        return $number;
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', $search)
                  ->orWhere('name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('subject', 'like', $search)
                  ->orWhere('message', 'like', $search);
            });
        }

        return $query;
    }
}
