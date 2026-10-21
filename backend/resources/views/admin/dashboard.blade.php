@extends('layouts.admin')

@section('title', 'لوحة التحكم بالتذاكر — Zadex')

@section('styles')
<style>
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }

    .stat-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 18px 20px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.04);
    }

    .stat-label {
        font-size: 13px;
        color: var(--muted);
        font-weight: 500;
    }

    .stat-number {
        font-size: 30px;
        font-weight: 700;
        font-family: var(--font-brand);
        color: var(--ink);
        line-height: 1.1;
    }

    .stat-number.open { color: #059669; }
    .stat-number.in_progress { color: #d97706; }
    .stat-number.resolved { color: #2563eb; }
    .stat-number.urgent { color: #dc2626; }

    /* Filters Bar */
    .filter-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .status-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--line-light);
        padding-bottom: 14px;
    }

    .status-tab {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        color: var(--muted);
        background: var(--bg);
        transition: all 0.15s;
    }

    .status-tab:hover {
        color: var(--ink);
        background: #e2e8f0;
    }

    .status-tab.active {
        background: var(--primary);
        color: white;
    }

    .filter-form {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-input-group {
        flex: 1;
        min-width: 240px;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 9px 38px 9px 14px;
        border: 1px solid var(--line);
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: var(--bg);
        color: var(--ink);
    }
    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
    }

    .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted);
        pointer-events: none;
    }

    .filter-select {
        padding: 9px 14px;
        border: 1px solid var(--line);
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        background: var(--bg);
        color: var(--ink);
        cursor: pointer;
    }

    /* Table Card */
    .table-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 12px;
        overflow: hidden;
    }

    .table-header-title {
        padding: 18px 20px;
        border-bottom: 1px solid var(--line);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-header-title h2 {
        font-size: 16px;
        font-weight: 700;
    }

    .tickets-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
        font-size: 14px;
    }

    .tickets-table th {
        background: #f8fafc;
        padding: 12px 18px;
        font-weight: 600;
        color: var(--muted);
        font-size: 12px;
        border-bottom: 1px solid var(--line);
        white-space: nowrap;
    }

    .tickets-table td {
        padding: 14px 18px;
        border-bottom: 1px solid var(--line-light);
        vertical-align: middle;
    }

    .tickets-table tr:hover td {
        background: #fcfdfe;
    }

    .ticket-subject-cell {
        max-width: 320px;
    }

    .ticket-subject-link {
        color: var(--ink);
        font-weight: 600;
        text-decoration: none;
        display: block;
        margin-bottom: 2px;
    }
    .ticket-subject-link:hover {
        color: var(--primary);
    }

    .ticket-excerpt {
        font-size: 12px;
        color: var(--muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .customer-info {
        display: flex;
        flex-direction: column;
    }
    .customer-name {
        font-weight: 600;
        font-size: 13px;
    }
    .customer-email {
        font-size: 12px;
        color: var(--muted);
        font-family: var(--font-en);
    }

    .empty-state {
        text-align: center;
        padding: 56px 20px;
        color: var(--muted);
    }
    .empty-state svg {
        margin-bottom: 12px;
        color: #cbd5e1;
    }

    .pagination-wrapper {
        padding: 16px 20px;
        border-top: 1px solid var(--line);
        display: flex;
        justify-content: center;
    }
</style>
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">إجمالي التذاكر المستلمة</span>
            <span class="stat-number">{{ $stats['total'] }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">تذاكر جديدة / مفتوحة</span>
            <span class="stat-number open">{{ $stats['open'] }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">قيد المتابعة والمعالجة</span>
            <span class="stat-number in_progress">{{ $stats['in_progress'] }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">تم حلها ومغلقة</span>
            <span class="stat-number resolved">{{ $stats['resolved'] }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">تذاكر عاجلة بحاجة للرد</span>
            <span class="stat-number urgent">{{ $stats['urgent'] }}</span>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="filter-card">
        <!-- Status Tabs -->
        <div class="status-tabs">
            <a href="{{ route('admin.tickets.index', array_merge($filters, ['status' => 'all'])) }}" class="status-tab {{ $filters['status'] === 'all' ? 'active' : '' }}">
                الكل ({{ $stats['total'] }})
            </a>
            <a href="{{ route('admin.tickets.index', array_merge($filters, ['status' => 'open'])) }}" class="status-tab {{ $filters['status'] === 'open' ? 'active' : '' }}">
                مفتوحة ({{ $stats['open'] }})
            </a>
            <a href="{{ route('admin.tickets.index', array_merge($filters, ['status' => 'in_progress'])) }}" class="status-tab {{ $filters['status'] === 'in_progress' ? 'active' : '' }}">
                قيد المعالجة ({{ $stats['in_progress'] }})
            </a>
            <a href="{{ route('admin.tickets.index', array_merge($filters, ['status' => 'resolved'])) }}" class="status-tab {{ $filters['status'] === 'resolved' ? 'active' : '' }}">
                تم الحل ({{ $stats['resolved'] }})
            </a>
            <a href="{{ route('admin.tickets.index', array_merge($filters, ['status' => 'closed'])) }}" class="status-tab {{ $filters['status'] === 'closed' ? 'active' : '' }}">
                مغلقة
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="filter-form">
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
            
            <div class="search-input-group">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="search" class="search-input" placeholder="ابحث برقم التذكرة، الاسم، البريد، أو الموضوع..." value="{{ $filters['search'] }}">
            </div>

            <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="all" {{ $filters['category'] === 'all' ? 'selected' : '' }}>جميع التصنيفات</option>
                <option value="general" {{ $filters['category'] === 'general' ? 'selected' : '' }}>استفسار عام</option>
                <option value="bug" {{ $filters['category'] === 'bug' ? 'selected' : '' }}>إبلاغ عن خطأ تقني</option>
                <option value="feature" {{ $filters['category'] === 'feature' ? 'selected' : '' }}>طلب ميزة جديدة</option>
                <option value="technical" {{ $filters['category'] === 'technical' ? 'selected' : '' }}>مساعدة برمجية/توافق</option>
                <option value="account" {{ $filters['category'] === 'account' ? 'selected' : '' }}>الحساب والتراخيص</option>
            </select>

            <select name="priority" class="filter-select" onchange="this.form.submit()">
                <option value="all" {{ $filters['priority'] === 'all' ? 'selected' : '' }}>جميع الأولويات</option>
                <option value="urgent" {{ $filters['priority'] === 'urgent' ? 'selected' : '' }}>عاجلة جداً</option>
                <option value="high" {{ $filters['priority'] === 'high' ? 'selected' : '' }}>أولوية مرتفعة</option>
                <option value="medium" {{ $filters['priority'] === 'medium' ? 'selected' : '' }}>متوسطة</option>
                <option value="low" {{ $filters['priority'] === 'low' ? 'selected' : '' }}>منخفضة</option>
            </select>

            <button type="submit" class="btn btn-secondary">تصفية</button>
            @if($filters['search'] || $filters['category'] !== 'all' || $filters['priority'] !== 'all' || $filters['status'] !== 'all')
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm" style="color: var(--muted)">إعادة ضبط</a>
            @endif
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="table-card">
        <div class="table-header-title">
            <div style="display: flex; align-items: center; gap: 12px;">
                <h2>قائمة التذاكر المستلمة ({{ $tickets->total() }})</h2>
                <span style="font-size: 12px; color: var(--muted)">محدث تلقائياً</span>
            </div>
            <a href="{{ route('admin.tickets.create') }}" class="btn btn-primary btn-sm" style="text-decoration: none;">
                + تذكرة جديدة
            </a>
        </div>

        @if($tickets->count() > 0)
            <div style="overflow-x: auto;">
                <table class="tickets-table">
                    <thead>
                        <tr>
                            <th>رقم التذكرة</th>
                            <th>العميل</th>
                            <th>الموضوع</th>
                            <th>التصنيف</th>
                            <th>الأولوية</th>
                            <th>الحالة</th>
                            <th>الردود</th>
                            <th>تاريخ الاستلام</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td>
                                    <span class="code-tag">{{ $ticket->ticket_number }}</span>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name">{{ $ticket->name }}</span>
                                        <span class="customer-email">{{ $ticket->email }}</span>
                                    </div>
                                </td>
                                <td class="ticket-subject-cell">
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="ticket-subject-link">
                                        {{ $ticket->subject }}
                                    </a>
                                    <div class="ticket-excerpt">{{ Str::limit($ticket->message, 60) }}</div>
                                </td>
                                <td>
                                    <span style="font-size: 12px; color: var(--muted); font-weight: 500;">
                                        @switch($ticket->category)
                                            @case('bug') خطأ تقني @break
                                            @case('feature') طلب ميزة @break
                                            @case('technical') توافق تقني @break
                                            @case('account') حساب @break
                                            @default عام
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-priority-{{ $ticket->priority }}">
                                        @switch($ticket->priority)
                                            @case('urgent') عاجلة @break
                                            @case('high') مرتفعة @break
                                            @case('medium') متوسطة @break
                                            @default منخفضة
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $ticket->status }}">
                                        @switch($ticket->status)
                                            @case('open') مفتوحة @break
                                            @case('in_progress') قيد المعالجة @break
                                            @case('resolved') تم الحل @break
                                            @case('closed') مغلقة @break
                                            @default {{ $ticket->status }}
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 13px; font-weight: 600; color: var(--muted);">
                                        {{ $ticket->replies_count }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 12px; color: var(--muted);" title="{{ $ticket->created_at }}">
                                        {{ $ticket->created_at->diffForHumans() }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-secondary btn-sm" title="عرض التذكرة والرد">
                                            عرض
                                        </a>
                                        <a href="{{ route('admin.tickets.edit', $ticket) }}" class="btn btn-secondary btn-sm" title="تعديل بيانات التذكرة">
                                            تعديل
                                        </a>
                                        <form method="POST" action="{{ route('admin.tickets.destroy', $ticket) }}" onsubmit="return confirm('هل أنت متأكد من حذف التذكرة {{ $ticket->ticket_number }} نهائياً؟');" style="display: inline; margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; cursor: pointer; border-radius: 6px;" title="حذف">
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div class="pagination-wrapper">
                    {{ $tickets->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <h3 style="font-size: 16px; margin-bottom: 6px;">لا توجد تذاكر تطابق هذا البحث</h3>
                <p style="font-size: 13px;">جرّب تعديل الفلاتر أو البحث بكلمة مختلفة.</p>
            </div>
        @endif
    </div>
@endsection
