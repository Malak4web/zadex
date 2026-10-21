@extends('layouts.admin')

@section('title', 'تذكرة ' . $ticket->ticket_number . ' — ' . $ticket->subject)

@section('styles')
<style>
    .ticket-header {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: gap;
        gap: 16px;
    }

    .ticket-title-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .ticket-tags {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ticket-main-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--ink);
    }

    .ticket-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
    }

    @media (max-width: 960px) {
        .ticket-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Message Card */
    .message-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--line-light);
        margin-bottom: 16px;
    }

    .message-author {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-light);
        color: var(--primary);
        font-weight: 700;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-en);
    }

    .avatar.admin {
        background: #11151c;
        color: #fff;
    }

    .author-info h4 {
        font-size: 14px;
        font-weight: 700;
    }
    .author-info span {
        font-size: 12px;
        color: var(--muted);
    }

    .message-body {
        font-size: 15px;
        line-height: 1.8;
        color: #24292f;
        white-space: pre-wrap;
    }

    /* Replies list */
    .reply-card {
        background: #fcfdfe;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
    }

    .reply-card.admin-reply {
        background: #f8faff;
        border-color: #dbeafe;
    }

    /* Reply Form Card */
    .reply-form-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 24px;
    }

    .reply-form-card h3 {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 16px;
    }

    .form-textarea {
        width: 100%;
        min-height: 140px;
        padding: 14px;
        border: 1px solid var(--line);
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        line-height: 1.6;
        margin-bottom: 16px;
        resize: vertical;
    }
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(53, 101, 235, 0.1);
    }

    .form-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    /* Sidebar cards */
    .sidebar-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .sidebar-card h3 {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--line-light);
    }

    .meta-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        font-size: 13px;
    }

    .meta-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .meta-key {
        color: var(--muted);
    }

    .meta-val {
        font-weight: 600;
    }

    .quick-status-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
</style>
@endsection

@section('content')
    <div style="margin-bottom: 16px;">
        <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            <span>العودة لقائمة التذاكر</span>
        </a>
    </div>

    <!-- Ticket Header -->
    <div class="ticket-header">
        <div class="ticket-title-group">
            <div class="ticket-tags">
                <span class="code-tag">{{ $ticket->ticket_number }}</span>
                <span class="badge badge-{{ $ticket->status }}">
                    @switch($ticket->status)
                        @case('open') مفتوحة @break
                        @case('in_progress') قيد المعالجة @break
                        @case('resolved') تم الحل @break
                        @case('closed') مغلقة @break
                        @default {{ $ticket->status }}
                    @endswitch
                </span>
                <span class="badge badge-priority-{{ $ticket->priority }}">
                    @switch($ticket->priority)
                        @case('urgent') أولوية عاجلة @break
                        @case('high') أولوية مرتفعة @break
                        @case('medium') أولوية متوسطة @break
                        @default أولوية منخفضة
                    @endswitch
                </span>
            </div>
            <h1 class="ticket-main-title">{{ $ticket->subject }}</h1>
        </div>

        <div>
            <!-- Quick status updater -->
            <form method="POST" action="{{ route('admin.tickets.updateStatus', $ticket) }}" style="display: flex; gap: 8px;">
                @csrf
                @method('PATCH')
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>تغيير الحالة: مفتوحة</option>
                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>تغيير الحالة: قيد المعالجة</option>
                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>تغيير الحالة: تم الحل</option>
                    <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>تغيير الحالة: مغلقة</option>
                </select>
            </form>
        </div>
    </div>

    <div class="ticket-grid">
        <!-- Main Column: Inquiries & Replies -->
        <div>
            <!-- Original Customer Message -->
            <div class="message-card">
                <div class="message-header">
                    <div class="message-author">
                        <div class="avatar">{{ mb_substr($ticket->name, 0, 1) }}</div>
                        <div class="author-info">
                            <h4>{{ $ticket->name }} <span style="font-weight: normal; color: var(--muted);">({{ $ticket->email }})</span></h4>
                            <span>{{ $ticket->created_at->format('Y-m-d H:i') }} (منذ {{ $ticket->created_at->diffForHumans() }})</span>
                        </div>
                    </div>
                    <span style="font-size: 12px; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; color: var(--muted);">رسالة العميل الأساسية</span>
                </div>
                <div class="message-body">{{ $ticket->message }}</div>
            </div>

            <!-- Replies Thread -->
            @if($ticket->replies->count() > 0)
                <div style="margin-bottom: 16px;">
                    <h3 style="font-size: 15px; font-weight: 700; color: var(--muted); margin-bottom: 12px;">سجل المحادثة والردود ({{ $ticket->replies->count() }})</h3>
                </div>

                @foreach($ticket->replies as $reply)
                    <div class="reply-card {{ $reply->sender_type === 'admin' ? 'admin-reply' : '' }}">
                        <div class="message-header">
                            <div class="message-author">
                                <div class="avatar {{ $reply->sender_type === 'admin' ? 'admin' : '' }}">
                                    {{ $reply->sender_type === 'admin' ? 'Z' : mb_substr($reply->sender_name, 0, 1) }}
                                </div>
                                <div class="author-info">
                                    <h4>
                                        {{ $reply->sender_name }}
                                        @if($reply->sender_type === 'admin')
                                            <span class="badge" style="background: var(--primary); color: white; margin-right: 6px;">فريق الدعم</span>
                                        @endif
                                    </h4>
                                    <span>{{ $reply->created_at->format('Y-m-d H:i') }} (منذ {{ $reply->created_at->diffForHumans() }})</span>
                                </div>
                            </div>
                        </div>
                        <div class="message-body">{{ $reply->message }}</div>
                    </div>
                @endforeach
            @endif

            <!-- Reply Form -->
            <div class="reply-form-card">
                <h3>إضافة رد على التذكرة</h3>
                <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}">
                    @csrf
                    <textarea name="message" class="form-textarea" placeholder="اكتب ردك للعميل هنا..." required></textarea>
                    
                    <div class="form-row">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label for="status-update" style="font-size: 13px; color: var(--muted);">تحديث حالة التذكرة بعد الرد:</label>
                            <select id="status-update" name="status" class="filter-select">
                                <option value="in_progress" {{ $ticket->status === 'open' ? 'selected' : '' }}>قيد المعالجة (In Progress)</option>
                                <option value="resolved">تم الحل (Resolved)</option>
                                <option value="closed">إغلاق التذكرة (Closed)</option>
                                <option value="{{ $ticket->status }}" selected>الإبقاء على الحالة الحالية</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            <span>إرسال الرد</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar: Ticket Details & Actions -->
        <div>
            <!-- Customer Details Card -->
            <div class="sidebar-card">
                <h3>معلومات العميل</h3>
                <div class="meta-list">
                    <div class="meta-item">
                        <span class="meta-key">الاسم:</span>
                        <span class="meta-val">{{ $ticket->name }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">البريد الإلكتروني:</span>
                        <span class="meta-val" style="font-family: var(--font-en); font-size: 12px;">{{ $ticket->email }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">رابط المراسلة:</span>
                        <a href="mailto:{{ $ticket->email }}?subject=رد بخصوص التذكرة {{ $ticket->ticket_number }}" class="btn btn-secondary btn-sm" style="padding: 3px 8px; font-size: 11px;">مراسلة بالبريد</a>
                    </div>
                </div>
            </div>

            <!-- Ticket Metadata Card -->
            <div class="sidebar-card">
                <h3>بيانات التذكرة</h3>
                <div class="meta-list">
                    <div class="meta-item">
                        <span class="meta-key">رقم التذكرة:</span>
                        <span class="code-tag">{{ $ticket->ticket_number }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">التصنيف:</span>
                        <span class="meta-val">
                            @switch($ticket->category)
                                @case('bug') إبلاغ عن خطأ تقني @break
                                @case('feature') طلب ميزة جديدة @break
                                @case('technical') توافق برمجيات @break
                                @case('account') الحساب والتراخيص @break
                                @default استفسار عام
                            @endswitch
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-key">تاريخ الإرسال:</span>
                        <span class="meta-val" style="font-size: 12px;">{{ $ticket->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    @if($ticket->resolved_at)
                        <div class="meta-item">
                            <span class="meta-key">تاريخ الحل:</span>
                            <span class="meta-val" style="font-size: 12px; color: #059669;">{{ $ticket->resolved_at->format('Y-m-d H:i') }}</span>
                        </div>
                    @endif
                    <div class="meta-item">
                        <span class="meta-key">عنوان IP:</span>
                        <span class="meta-val" style="font-family: var(--font-mono); font-size: 12px;">{{ $ticket->ip_address ?: 'غير متاح' }}</span>
                    </div>
                </div>
            </div>

            <!-- Danger Zone / Delete -->
            <div class="sidebar-card" style="border-color: #fee2e2;">
                <h3 style="color: var(--danger); border-bottom-color: #fee2e2;">إجراءات إضافية</h3>
                <p style="font-size: 12px; color: var(--muted); margin-bottom: 14px;">حذف هذه التذكرة سيؤدي إلى مسح جميع ردودها وسجلها بشكل نهائي.</p>
                <form method="POST" action="{{ route('admin.tickets.destroy', $ticket) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذه التذكرة نهائياً؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" style="width: 100%;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        <span>حذف التذكرة</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
