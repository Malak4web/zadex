@extends('layouts.admin')

@section('title', 'تعديل التذكرة ' . $ticket->ticket_number . ' — Zadex')

@section('styles')
<style>
    .form-page-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .form-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 28px;
    }

    .page-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--ink);
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 640px) {
        .form-grid-2 {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        color: var(--ink);
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--line);
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: #fff;
        color: var(--ink);
        transition: border-color 0.15s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(53, 101, 235, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 140px;
    }

    .field-hint {
        font-size: 12px;
        color: var(--muted);
        margin-top: 4px;
    }

    .form-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--line);
    }
</style>
@endsection

@section('content')
<div class="form-page-container">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">تعديل التذكرة: <span style="font-family: var(--font-mono); color: var(--primary);">{{ $ticket->ticket_number }}</span></h1>
            <p style="font-size: 13px; color: var(--muted); margin-top: 4px;">تحديث بيانات التذكرة والتصنيف والحالة والأولويات</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-secondary">
                عرض التذكرة
            </a>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-secondary">
                &larr; القائمة
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error" style="background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:14px;">
            <ul style="margin: 0; padding-right: 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-card">
        <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}">
            @csrf
            @method('PUT')

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="name">اسم العميل <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $ticket->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="email">البريد الإلكتروني للعميل <span style="color:#ef4444;">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $ticket->email) }}" required dir="ltr" style="text-align: right;">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="category">القسم / التصنيف <span style="color:#ef4444;">*</span></label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="general" {{ old('category', $ticket->category) === 'general' ? 'selected' : '' }}>استفسار عام</option>
                        <option value="bug" {{ old('category', $ticket->category) === 'bug' ? 'selected' : '' }}>خطأ تقني / برمجي</option>
                        <option value="feature" {{ old('category', $ticket->category) === 'feature' ? 'selected' : '' }}>طلب ميزة أو تطوير</option>
                        <option value="technical" {{ old('category', $ticket->category) === 'technical' ? 'selected' : '' }}>توافق قوالب وإضافات</option>
                        <option value="account" {{ old('category', $ticket->category) === 'account' ? 'selected' : '' }}>حسابات وتراخيص</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">الأولوية <span style="color:#ef4444;">*</span></label>
                    <select id="priority" name="priority" class="form-control" required>
                        <option value="low" {{ old('priority', $ticket->priority) === 'low' ? 'selected' : '' }}>منخفضة</option>
                        <option value="medium" {{ old('priority', $ticket->priority) === 'medium' ? 'selected' : '' }}>متوسطة</option>
                        <option value="high" {{ old('priority', $ticket->priority) === 'high' ? 'selected' : '' }}>مرتفعة</option>
                        <option value="urgent" {{ old('priority', $ticket->priority) === 'urgent' ? 'selected' : '' }}>عاجلة (حرجة)</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="status">حالة التذكرة <span style="color:#ef4444;">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="open" {{ old('status', $ticket->status) === 'open' ? 'selected' : '' }}>مفتوحة</option>
                        <option value="in_progress" {{ old('status', $ticket->status) === 'in_progress' ? 'selected' : '' }}>قيد المعالجة</option>
                        <option value="resolved" {{ old('status', $ticket->status) === 'resolved' ? 'selected' : '' }}>تم الحل</option>
                        <option value="closed" {{ old('status', $ticket->status) === 'closed' ? 'selected' : '' }}>مغلقة</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject">عنوان التذكرة <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="subject" name="subject" class="form-control" value="{{ old('subject', $ticket->subject) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="message">تفاصيل التذكرة والمشكلة <span style="color:#ef4444;">*</span></label>
                <textarea id="message" name="message" class="form-control" required>{{ old('message', $ticket->message) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    حفظ التعديلات
                </button>
                <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-secondary">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
