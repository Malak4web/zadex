<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { 
  LifeBuoy, 
  Search, 
  Send, 
  Check, 
  Clock, 
  User, 
  Mail, 
  AlertCircle, 
  CheckCircle2, 
  ExternalLink, 
  LogOut, 
  Trash2, 
  X, 
  MessageSquare, 
  Shield, 
  Layers, 
  ArrowRight, 
  Filter,
  Plus,
  Pencil,
  RefreshCw
} from 'lucide-vue-next';
import { 
  fetchAllTickets, 
  createTicket as apiCreateTicket, 
  updateTicket as apiUpdateTicket, 
  deleteTicket as apiDeleteTicket, 
  replyToTicket as apiReplyToTicket 
} from '../services/ticketApi';

const isAuthenticated = ref(false);
const loginEmail = ref('');
const loginPassword = ref('');
const loginError = ref('');
const rememberMe = ref(true);

const tickets = ref([]);
const isLoadingTickets = ref(false);
const activeTicket = ref(null);
const replyMessage = ref('');
const replyStatus = ref('in_progress');
const toastMessage = ref('');

// Modals
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isSaving = ref(false);

const createForm = reactive({
  name: '',
  email: '',
  subject: '',
  category: 'general',
  priority: 'medium',
  status: 'open',
  message: '',
});

const editForm = reactive({
  ticket_number: '',
  name: '',
  email: '',
  subject: '',
  category: 'general',
  priority: 'medium',
  status: 'open',
  message: '',
});

const filterStatus = ref('all');
const filterCategory = ref('all');
const filterPriority = ref('all');
const searchQuery = ref('');

async function loadTickets() {
  isLoadingTickets.value = true;
  try {
    const res = await fetchAllTickets();
    if (res && res.tickets) {
      tickets.value = res.tickets;
    }
  } catch {
    // fallback is handled by fetchAllTickets
  } finally {
    isLoadingTickets.value = false;
  }
}

onMounted(() => {
  // Check auth
  const auth = sessionStorage.getItem('zadex_admin_auth') || localStorage.getItem('zadex_admin_auth');
  if (auth === 'true') {
    isAuthenticated.value = true;
  }
  loadTickets();
});

function handleLogin() {
  loginError.value = '';
  if (loginEmail.value.trim().toLowerCase() === 'admin@zadex.com' && loginPassword.value === 'zadex@2026') {
    isAuthenticated.value = true;
    if (rememberMe.value) {
      localStorage.setItem('zadex_admin_auth', 'true');
    }
    sessionStorage.setItem('zadex_admin_auth', 'true');
    showToast('تم تسجيل الدخول بنجاح. مرحباً بك في لوحة تحكم Zadex.');
    loadTickets();
  } else {
    loginError.value = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
  }
}

function handleLogout() {
  isAuthenticated.value = false;
  sessionStorage.removeItem('zadex_admin_auth');
  localStorage.removeItem('zadex_admin_auth');
  showToast('تم تسجيل الخروج بنجاح.');
}

function showToast(msg) {
  toastMessage.value = msg;
  setTimeout(() => { toastMessage.value = ''; }, 3500);
}

// Stats
const stats = computed(() => {
  const all = tickets.value || [];
  return {
    total: all.length,
    open: all.filter(t => t.status === 'open').length,
    in_progress: all.filter(t => t.status === 'in_progress').length,
    resolved: all.filter(t => t.status === 'resolved' || t.status === 'closed').length,
    urgent: all.filter(t => (t.priority === 'urgent' || t.priority === 'high') && t.status !== 'resolved' && t.status !== 'closed').length,
  };
});

// Filtered Tickets
const filteredTickets = computed(() => {
  return (tickets.value || []).filter(t => {
    if (filterStatus.value !== 'all' && t.status !== filterStatus.value) return false;
    if (filterCategory.value !== 'all' && t.category !== filterCategory.value) return false;
    if (filterPriority.value !== 'all' && t.priority !== filterPriority.value) return false;
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.trim().toLowerCase();
      const matchNumber = t.ticket_number?.toLowerCase().includes(q);
      const matchName = t.name?.toLowerCase().includes(q);
      const matchEmail = t.email?.toLowerCase().includes(q);
      const matchSubject = t.subject?.toLowerCase().includes(q);
      const matchMessage = t.message?.toLowerCase().includes(q);
      if (!matchNumber && !matchName && !matchEmail && !matchSubject && !matchMessage) return false;
    }
    return true;
  });
});

// CREATE TICKET
function openCreateModal() {
  createForm.name = '';
  createForm.email = '';
  createForm.subject = '';
  createForm.category = 'general';
  createForm.priority = 'medium';
  createForm.status = 'open';
  createForm.message = '';
  isCreateModalOpen.value = true;
}

function closeCreateModal() {
  isCreateModalOpen.value = false;
}

async function handleCreateTicket() {
  if (!createForm.name.trim() || !createForm.email.trim() || !createForm.subject.trim() || !createForm.message.trim()) {
    alert('يرجى ملء كافة الحقول الإلزامية.');
    return;
  }

  isSaving.value = true;
  try {
    const res = await apiCreateTicket({
      name: createForm.name.trim(),
      email: createForm.email.trim(),
      subject: createForm.subject.trim(),
      category: createForm.category,
      priority: createForm.priority,
      status: createForm.status,
      message: createForm.message.trim(),
    });

    if (res.success && res.ticket) {
      tickets.value.unshift(res.ticket);
      isCreateModalOpen.value = false;
      showToast(`تم إنشاء التذكرة ${res.ticket.ticket_number} بنجاح.`);
    }
  } catch (err) {
    alert('حدث خطأ أثناء حفظ التذكرة.');
  } finally {
    isSaving.value = false;
  }
}

// EDIT TICKET
function openEditModal(ticket) {
  editForm.ticket_number = ticket.ticket_number;
  editForm.name = ticket.name || '';
  editForm.email = ticket.email || '';
  editForm.subject = ticket.subject || '';
  editForm.category = ticket.category || 'general';
  editForm.priority = ticket.priority || 'medium';
  editForm.status = ticket.status || 'open';
  editForm.message = ticket.message || '';
  isEditModalOpen.value = true;
}

function closeEditModal() {
  isEditModalOpen.value = false;
}

async function handleUpdateTicket() {
  if (!editForm.name.trim() || !editForm.email.trim() || !editForm.subject.trim() || !editForm.message.trim()) {
    alert('يرجى ملء كافة الحقول الإلزامية.');
    return;
  }

  isSaving.value = true;
  try {
    const res = await apiUpdateTicket(editForm.ticket_number, {
      name: editForm.name.trim(),
      email: editForm.email.trim(),
      subject: editForm.subject.trim(),
      category: editForm.category,
      priority: editForm.priority,
      status: editForm.status,
      message: editForm.message.trim(),
    });

    if (res.success && res.ticket) {
      const idx = tickets.value.findIndex(t => t.ticket_number === editForm.ticket_number);
      if (idx !== -1) {
        tickets.value[idx] = { ...tickets.value[idx], ...res.ticket };
      }
      if (activeTicket.value?.ticket_number === editForm.ticket_number) {
        activeTicket.value = { ...activeTicket.value, ...res.ticket };
      }
      isEditModalOpen.value = false;
      showToast(`تم تحديث التذكرة ${editForm.ticket_number} بنجاح.`);
    }
  } catch (err) {
    alert('حدث خطأ أثناء تحديث التذكرة.');
  } finally {
    isSaving.value = false;
  }
}

// READ DETAILS
function openTicket(ticket) {
  activeTicket.value = JSON.parse(JSON.stringify(ticket));
  replyStatus.value = ticket.status === 'open' ? 'in_progress' : ticket.status;
  replyMessage.value = '';
}

function closeTicketModal() {
  activeTicket.value = null;
}

// QUICK STATUS DIRECT
async function updateTicketStatusDirect(ticket, newStatus) {
  ticket.status = newStatus;
  if (['resolved', 'closed'].includes(newStatus)) {
    ticket.resolved_at = new Date().toISOString();
  } else {
    ticket.resolved_at = null;
  }
  await apiUpdateTicket(ticket.ticket_number, { status: newStatus });
  showToast(`تم تغيير حالة التذكرة ${ticket.ticket_number} إلى ${newStatus}.`);
}

// REPLY
async function submitReply() {
  if (!replyMessage.value.trim() || !activeTicket.value) return;

  const targetNum = activeTicket.value.ticket_number;
  const msg = replyMessage.value.trim();
  const st = replyStatus.value;

  try {
    const res = await apiReplyToTicket(targetNum, {
      message: msg,
      status: st,
      sender_name: 'فريق دعم Zadex',
      sender_type: 'admin'
    });

    if (res.success && res.reply) {
      if (!activeTicket.value.replies) activeTicket.value.replies = [];
      activeTicket.value.replies.push(res.reply);
      activeTicket.value.status = st;

      const target = tickets.value.find(t => t.ticket_number === targetNum);
      if (target) {
        if (!target.replies) target.replies = [];
        target.replies.push(res.reply);
        target.status = st;
      }
      replyMessage.value = '';
      showToast('تم إرسال الرد وتحديث التذكرة بنجاح.');
    }
  } catch {
    alert('حدث خطأ أثناء إرسال الرد.');
  }
}

// DELETE
async function handleDeleteTicket(ticketNumber) {
  if (!confirm(`هل أنت متأكد من حذف التذكرة ${ticketNumber} نهائياً؟`)) return;

  try {
    await apiDeleteTicket(ticketNumber);
    tickets.value = tickets.value.filter(t => t.ticket_number !== ticketNumber);
    if (activeTicket.value?.ticket_number === ticketNumber) {
      activeTicket.value = null;
    }
    showToast(`تم حذف التذكرة ${ticketNumber} بنجاح.`);
  } catch {
    alert('حدث خطأ أثناء حذف التذكرة.');
  }
}

function timeAgo(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  const diffHours = Math.round((Date.now() - date.getTime()) / 3600000);
  if (diffHours < 1) return 'منذ دقائق';
  if (diffHours < 24) return `منذ ${diffHours} ساعة`;
  const diffDays = Math.round(diffHours / 24);
  return `منذ ${diffDays} يوم`;
}
</script>

<template>
  <div class="admin-shell">
    <!-- Toast Notification -->
    <transition name="fade">
      <div v-if="toastMessage" class="toast-alert">
        <CheckCircle2 :size="18" />
        <span>{{ toastMessage }}</span>
      </div>
    </transition>

    <!-- Login View -->
    <div v-if="!isAuthenticated" class="login-wrapper">
      <div class="login-box">
        <div class="brand-header">
          <a href="/" class="brand-logo">Zadex<span class="brand-dot"></span></a>
          <p class="brand-subtitle">تسجيل الدخول إلى لوحة تحكم تذاكر الدعم</p>
        </div>

        <div v-if="loginError" class="alert-error">
          <AlertCircle :size="16" />
          <span>{{ loginError }}</span>
        </div>

        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label for="admin-email">البريد الإلكتروني</label>
            <input 
              id="admin-email" 
              v-model="loginEmail" 
              type="email" 
              required 
              placeholder="name@example.com"
              autocomplete="username"
            />
          </div>

          <div class="form-group">
            <label for="admin-password">كلمة المرور</label>
            <input 
              id="admin-password" 
              v-model="loginPassword" 
              type="password" 
              required 
              placeholder="••••••••"
              autocomplete="current-password"
            />
          </div>

          <div class="remember-row">
            <label class="checkbox-label">
              <input v-model="rememberMe" type="checkbox" />
              <span>تذكرني على هذا الجهاز</span>
            </label>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            <span>تسجيل الدخول</span>
            <ArrowRight :size="16" />
          </button>
        </form>
      </div>
    </div>

    <!-- Authenticated Dashboard -->
    <div v-else class="dashboard-wrapper">
      <!-- Topbar -->
      <header class="topbar">
        <div class="brand-group">
          <a href="/admin" class="brand-logo">Zadex<span class="brand-dot"></span></a>
          <span class="badge-tickets">لوحة التذاكر والدعم</span>
        </div>

        <div class="topbar-actions">
          <button type="button" class="btn btn-primary btn-sm" @click="openCreateModal">
            <Plus :size="15" />
            <span>إنشاء تذكرة جديدة</span>
          </button>

          <button type="button" class="btn btn-secondary btn-sm" :disabled="isLoadingTickets" @click="loadTickets" title="تحديث التذاكر">
            <RefreshCw :size="14" :class="{ 'spin-anim': isLoadingTickets }" />
            <span>تحديث</span>
          </button>

          <span class="admin-user-pill">
            <Shield :size="14" />
            <span>Zadex Admin</span>
          </span>
          <a href="/" class="btn btn-secondary btn-sm" title="العودة إلى الموقع">
            <ExternalLink :size="14" />
            <span>الموقع الرئيسي</span>
          </a>
          <button type="button" class="btn btn-secondary btn-sm" @click="handleLogout">
            <LogOut :size="14" />
            <span>خروج</span>
          </button>
        </div>
      </header>

      <!-- Main Container -->
      <main class="container">
        <!-- Stats Grid -->
        <div class="stats-grid">
          <div class="stat-card">
            <span class="stat-label">إجمالي التذاكر المستلمة</span>
            <span class="stat-number">{{ stats.total }}</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">تذاكر مفتوحة / جديدة</span>
            <span class="stat-number open">{{ stats.open }}</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">قيد المعالجة والمتابعة</span>
            <span class="stat-number in_progress">{{ stats.in_progress }}</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">تم حلها بنجاح</span>
            <span class="stat-number resolved">{{ stats.resolved }}</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">تذاكر عاجلة بحاجة للرد</span>
            <span class="stat-number urgent">{{ stats.urgent }}</span>
          </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
          <!-- Status Tabs -->
          <div class="status-tabs">
            <button 
              type="button" 
              class="status-tab" 
              :class="{ active: filterStatus === 'all' }" 
              @click="filterStatus = 'all'"
            >
              الكل ({{ stats.total }})
            </button>
            <button 
              type="button" 
              class="status-tab" 
              :class="{ active: filterStatus === 'open' }" 
              @click="filterStatus = 'open'"
            >
              مفتوحة ({{ stats.open }})
            </button>
            <button 
              type="button" 
              class="status-tab" 
              :class="{ active: filterStatus === 'in_progress' }" 
              @click="filterStatus = 'in_progress'"
            >
              قيد المعالجة ({{ stats.in_progress }})
            </button>
            <button 
              type="button" 
              class="status-tab" 
              :class="{ active: filterStatus === 'resolved' }" 
              @click="filterStatus = 'resolved'"
            >
              تم الحل ({{ stats.resolved }})
            </button>
            <button 
              type="button" 
              class="status-tab" 
              :class="{ active: filterStatus === 'closed' }" 
              @click="filterStatus = 'closed'"
            >
              مغلقة
            </button>
          </div>

          <!-- Search and filters -->
          <div class="filter-row">
            <div class="search-input-group">
              <Search class="search-icon" :size="16" />
              <input 
                v-model="searchQuery" 
                type="text" 
                class="search-input" 
                placeholder="ابحث برقم التذكرة، العميل، البريد، أو الموضوع..." 
              />
            </div>

            <select v-model="filterCategory" class="filter-select">
              <option value="all">جميع التصنيفات</option>
              <option value="general">استفسار عام</option>
              <option value="bug">خطأ تقني</option>
              <option value="feature">طلب ميزة</option>
              <option value="technical">توافق تقني</option>
              <option value="account">حساب وتراخيص</option>
            </select>

            <select v-model="filterPriority" class="filter-select">
              <option value="all">جميع الأولويات</option>
              <option value="urgent">عاجلة جداً</option>
              <option value="high">مرتفعة</option>
              <option value="medium">متوسطة</option>
              <option value="low">منخفضة</option>
            </select>
          </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
          <div class="table-header-title">
            <div style="display: flex; align-items: center; gap: 12px;">
              <h2>التذاكر الواردة ({{ filteredTickets.length }})</h2>
              <span style="font-size: 12px; color: #5a6474">تحديث فوري ومباشر مع قاعدة البيانات</span>
            </div>
            <button type="button" class="btn btn-primary btn-sm" @click="openCreateModal">
              <Plus :size="14" />
              <span>+ تذكرة جديدة</span>
            </button>
          </div>

          <div v-if="filteredTickets.length > 0" class="table-responsive">
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
                  <th>التاريخ</th>
                  <th style="min-width: 140px;">إجراءات CRUD</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="ticket in filteredTickets" :key="ticket.ticket_number">
                  <td>
                    <span class="code-tag">{{ ticket.ticket_number }}</span>
                  </td>
                  <td>
                    <div class="customer-info">
                      <span class="customer-name">{{ ticket.name }}</span>
                      <span class="customer-email">{{ ticket.email }}</span>
                    </div>
                  </td>
                  <td class="ticket-subject-cell">
                    <button type="button" class="ticket-subject-link" @click="openTicket(ticket)">
                      {{ ticket.subject }}
                    </button>
                    <div class="ticket-excerpt">{{ ticket.message }}</div>
                  </td>
                  <td>
                    <span class="meta-badge">
                      {{ ticket.category === 'bug' ? 'خطأ تقني' : ticket.category === 'feature' ? 'طلب ميزة' : ticket.category === 'technical' ? 'توافق' : 'عام' }}
                    </span>
                  </td>
                  <td>
                    <span class="badge" :class="'badge-priority-' + ticket.priority">
                      {{ ticket.priority === 'urgent' ? 'عاجلة' : ticket.priority === 'high' ? 'مرتفعة' : ticket.priority === 'medium' ? 'متوسطة' : 'منخفضة' }}
                    </span>
                  </td>
                  <td>
                    <select 
                      :value="ticket.status" 
                      class="mini-status-select" 
                      :class="'badge-' + ticket.status"
                      @change="updateTicketStatusDirect(ticket, $event.target.value)"
                    >
                      <option value="open">مفتوحة</option>
                      <option value="in_progress">قيد المعالجة</option>
                      <option value="resolved">تم الحل</option>
                      <option value="closed">مغلقة</option>
                    </select>
                  </td>
                  <td>
                    <span class="replies-count">
                      {{ ticket.replies?.length || 0 }}
                    </span>
                  </td>
                  <td>
                    <span class="ticket-date" :title="ticket.created_at">
                      {{ timeAgo(ticket.created_at) }}
                    </span>
                  </td>
                  <td>
                    <div class="row-actions">
                      <button type="button" class="btn btn-secondary btn-sm" @click="openTicket(ticket)" title="عرض المحادثة والرد">
                        عرض
                      </button>
                      <button type="button" class="btn btn-secondary btn-sm" @click="openEditModal(ticket)" title="تعديل بيانات التذكرة">
                        <Pencil :size="13" />
                        <span>تعديل</span>
                      </button>
                      <button type="button" class="btn btn-danger btn-sm" @click="handleDeleteTicket(ticket.ticket_number)" title="حذف التذكرة">
                        <Trash2 :size="13" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="empty-state">
            <Layers :size="48" />
            <h3>لا توجد تذاكر تطابق هذا البحث</h3>
            <p>جرّب تعديل الفلاتر أو البحث بكلمة مختلفة أو قم بإنشاء تذكرة جديدة.</p>
            <button type="button" class="btn btn-primary" style="margin-top: 14px;" @click="openCreateModal">
              <Plus :size="16" />
              <span>إنشاء تذكرة جديدة الآن</span>
            </button>
          </div>
        </div>
      </main>

      <!-- CREATE TICKET MODAL -->
      <div v-if="isCreateModalOpen" class="modal-backdrop" @click.self="closeCreateModal">
        <div class="modal-box modal-form-box">
          <div class="modal-header">
            <div>
              <h2 class="modal-subject">إنشاء تذكرة دعم جديدة</h2>
              <p style="font-size: 13px; color: #5a6474; margin-top: 4px;">إضافة تذكرة يدوياً إلى النظام وقاعدة البيانات</p>
            </div>
            <button type="button" class="btn-close" @click="closeCreateModal">
              <X :size="20" />
            </button>
          </div>

          <div class="modal-body">
            <form @submit.prevent="handleCreateTicket" class="form-stack">
              <div class="form-grid-2">
                <div class="form-group">
                  <label>اسم العميل <span class="req">*</span></label>
                  <input v-model="createForm.name" type="text" required placeholder="مثال: محمد أحمد" class="form-input" />
                </div>
                <div class="form-group">
                  <label>البريد الإلكتروني للعميل <span class="req">*</span></label>
                  <input v-model="createForm.email" type="email" required placeholder="client@example.com" class="form-input" dir="ltr" style="text-align: right;" />
                </div>
              </div>

              <div class="form-grid-3">
                <div class="form-group">
                  <label>القسم / التصنيف</label>
                  <select v-model="createForm.category" class="form-input">
                    <option value="general">استفسار عام</option>
                    <option value="bug">خطأ تقني</option>
                    <option value="feature">طلب ميزة</option>
                    <option value="technical">توافق تقني</option>
                    <option value="account">حساب وتراخيص</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>الأولوية</label>
                  <select v-model="createForm.priority" class="form-input">
                    <option value="low">منخفضة</option>
                    <option value="medium">متوسطة</option>
                    <option value="high">مرتفعة</option>
                    <option value="urgent">عاجلة جداً</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>الحالة الأولية</label>
                  <select v-model="createForm.status" class="form-input">
                    <option value="open">مفتوحة</option>
                    <option value="in_progress">قيد المعالجة</option>
                    <option value="resolved">تم الحل</option>
                    <option value="closed">مغلقة</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label>عنوان وموضوع التذكرة <span class="req">*</span></label>
                <input v-model="createForm.subject" type="text" required placeholder="موضوع المشكلة أو الاستفسار" class="form-input" />
              </div>

              <div class="form-group">
                <label>تفاصيل المشكلة والرسالة <span class="req">*</span></label>
                <textarea v-model="createForm.message" rows="5" required placeholder="اكتب تفاصيل التذكرة بدقة..." class="form-input"></textarea>
              </div>

              <div class="modal-footer-actions">
                <button type="button" class="btn btn-secondary" @click="closeCreateModal">إلغاء</button>
                <button type="submit" class="btn btn-primary" :disabled="isSaving">
                  <span>{{ isSaving ? 'جارٍ الحفظ...' : 'حفظ وإنشاء التذكرة' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- EDIT TICKET MODAL -->
      <div v-if="isEditModalOpen" class="modal-backdrop" @click.self="closeEditModal">
        <div class="modal-box modal-form-box">
          <div class="modal-header">
            <div>
              <div class="modal-tags">
                <span class="code-tag">{{ editForm.ticket_number }}</span>
                <span class="badge" :class="'badge-' + editForm.status">تعديل التذكرة</span>
              </div>
              <h2 class="modal-subject" style="margin-top: 6px;">تعديل بيانات التذكرة</h2>
            </div>
            <button type="button" class="btn-close" @click="closeEditModal">
              <X :size="20" />
            </button>
          </div>

          <div class="modal-body">
            <form @submit.prevent="handleUpdateTicket" class="form-stack">
              <div class="form-grid-2">
                <div class="form-group">
                  <label>اسم العميل <span class="req">*</span></label>
                  <input v-model="editForm.name" type="text" required class="form-input" />
                </div>
                <div class="form-group">
                  <label>البريد الإلكتروني للعميل <span class="req">*</span></label>
                  <input v-model="editForm.email" type="email" required class="form-input" dir="ltr" style="text-align: right;" />
                </div>
              </div>

              <div class="form-grid-3">
                <div class="form-group">
                  <label>القسم / التصنيف</label>
                  <select v-model="editForm.category" class="form-input">
                    <option value="general">استفسار عام</option>
                    <option value="bug">خطأ تقني</option>
                    <option value="feature">طلب ميزة</option>
                    <option value="technical">توافق تقني</option>
                    <option value="account">حساب وتراخيص</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>الأولوية</label>
                  <select v-model="editForm.priority" class="form-input">
                    <option value="low">منخفضة</option>
                    <option value="medium">متوسطة</option>
                    <option value="high">مرتفعة</option>
                    <option value="urgent">عاجلة جداً</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>الحالة</label>
                  <select v-model="editForm.status" class="form-input">
                    <option value="open">مفتوحة</option>
                    <option value="in_progress">قيد المعالجة</option>
                    <option value="resolved">تم الحل</option>
                    <option value="closed">مغلقة</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label>عنوان وموضوع التذكرة <span class="req">*</span></label>
                <input v-model="editForm.subject" type="text" required class="form-input" />
              </div>

              <div class="form-group">
                <label>تفاصيل المشكلة والرسالة <span class="req">*</span></label>
                <textarea v-model="editForm.message" rows="5" required class="form-input"></textarea>
              </div>

              <div class="modal-footer-actions">
                <button type="button" class="btn btn-secondary" @click="closeEditModal">إلغاء</button>
                <button type="submit" class="btn btn-primary" :disabled="isSaving">
                  <span>{{ isSaving ? 'جارٍ الحفظ...' : 'حفظ التعديلات' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- VIEW TICKET DETAIL & REPLY MODAL -->
      <div v-if="activeTicket" class="modal-backdrop" @click.self="closeTicketModal">
        <div class="modal-box">
          <div class="modal-header">
            <div class="modal-title-group">
              <div class="modal-tags">
                <span class="code-tag">{{ activeTicket.ticket_number }}</span>
                <span class="badge" :class="'badge-' + activeTicket.status">
                  {{ activeTicket.status === 'open' ? 'مفتوحة' : activeTicket.status === 'in_progress' ? 'قيد المعالجة' : activeTicket.status === 'resolved' ? 'تم الحل' : 'مغلقة' }}
                </span>
                <span class="badge" :class="'badge-priority-' + activeTicket.priority">
                  {{ activeTicket.priority === 'urgent' ? 'عاجلة' : activeTicket.priority === 'high' ? 'مرتفعة' : 'متوسطة' }}
                </span>
              </div>
              <h2 class="modal-subject">{{ activeTicket.subject }}</h2>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
              <button type="button" class="btn btn-secondary btn-sm" @click="openEditModal(activeTicket)">
                <Pencil :size="13" />
                <span>تعديل التذكرة</span>
              </button>
              <button type="button" class="btn-close" @click="closeTicketModal">
                <X :size="20" />
              </button>
            </div>
          </div>

          <div class="modal-body">
            <!-- Customer Initial Message Card -->
            <div class="inquiry-card">
              <div class="inquiry-head">
                <div class="author-info">
                  <div class="avatar">{{ (activeTicket.name || 'ع').charAt(0) }}</div>
                  <div>
                    <h4>{{ activeTicket.name }} <span class="author-email">({{ activeTicket.email }})</span></h4>
                    <span class="inquiry-time">{{ new Date(activeTicket.created_at).toLocaleString('ar-EG') }}</span>
                  </div>
                </div>
                <span class="inquiry-badge">تذكرة العميل</span>
              </div>
              <div class="inquiry-content">{{ activeTicket.message }}</div>
            </div>

            <!-- Conversation Replies -->
            <div v-if="activeTicket.replies?.length" class="replies-thread">
              <h4 class="thread-title">سجل الردود والمحادثة ({{ activeTicket.replies.length }})</h4>
              <div 
                v-for="reply in activeTicket.replies" 
                :key="reply.id" 
                class="reply-item" 
                :class="{ 'admin-reply': reply.sender_type === 'admin' }"
              >
                <div class="reply-head">
                  <strong>{{ reply.sender_name }}</strong>
                  <small>{{ new Date(reply.created_at).toLocaleString('ar-EG') }}</small>
                </div>
                <div class="reply-msg">{{ reply.message }}</div>
              </div>
            </div>

            <!-- Reply Box -->
            <div class="reply-box">
              <h4>كتابة رد على التذكرة:</h4>
              <textarea 
                v-model="replyMessage" 
                rows="4" 
                class="reply-textarea" 
                placeholder="اكتب ردك للعميل هنا..."
              ></textarea>

              <div class="reply-actions">
                <div class="status-change-group">
                  <label>تحديث الحالة بعد الرد:</label>
                  <select v-model="replyStatus" class="filter-select">
                    <option value="in_progress">قيد المعالجة (In Progress)</option>
                    <option value="resolved">تم الحل (Resolved)</option>
                    <option value="closed">إغلاق التذكرة (Closed)</option>
                    <option value="open">إبقاء مفتوحة (Open)</option>
                  </select>
                </div>

                <div class="modal-btns">
                  <button type="button" class="btn btn-danger btn-sm" @click="handleDeleteTicket(activeTicket.ticket_number)">
                    <Trash2 :size="14" />
                    <span>حذف التذكرة</span>
                  </button>
                  <button type="button" class="btn btn-primary" @click="submitReply">
                    <Send :size="15" />
                    <span>إرسال الرد</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-shell {
  font-family: 'Tajawal', sans-serif;
  background: #f8fafc;
  color: #11151c;
  min-height: 100vh;
  direction: rtl;
  display: flex;
  flex-direction: column;
}

/* Toast */
.toast-alert {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: #11151c;
  color: #ffffff;
  padding: 12px 24px;
  border-radius: 50px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.2);
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  font-weight: 500;
  z-index: 9999;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.25s, transform 0.25s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translate(-50%, -10px);
}

/* Login View */
.login-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.login-box {
  background: #ffffff;
  border: 1px solid #e2e6ed;
  border-radius: 16px;
  width: 100%;
  max-width: 420px;
  padding: 36px 32px;
  box-shadow: 0 15px 35px rgba(27, 42, 69, 0.05);
}

.brand-header {
  text-align: center;
  margin-bottom: 28px;
}

.brand-logo {
  font-family: 'Sora', sans-serif;
  font-size: 28px;
  font-weight: 700;
  letter-spacing: -0.8px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #11151c;
  text-decoration: none;
}

.brand-dot {
  width: 8px;
  height: 8px;
  background: #3565eb;
  border-radius: 50%;
  display: inline-block;
}

.brand-subtitle {
  font-size: 13px;
  color: #5a6474;
  margin-top: 6px;
}

.alert-error {
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fecaca;
  padding: 12px 14px;
  border-radius: 8px;
  font-size: 13px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-group {
  margin-bottom: 18px;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 6px;
  color: #11151c;
}

.req {
  color: #ef4444;
}

.form-group input,
.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid #e2e6ed;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  background: #fdfdfd;
  color: #11151c;
  transition: all 0.15s;
  box-sizing: border-box;
}

.form-group input:focus,
.form-input:focus {
  outline: none;
  border-color: #3565eb;
  box-shadow: 0 0 0 3px rgba(53, 101, 235, 0.1);
  background: #fff;
}

textarea.form-input {
  resize: vertical;
}

.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-grid-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 16px;
}

@media (max-width: 640px) {
  .form-grid-2,
  .form-grid-3 {
    grid-template-columns: 1fr;
  }
}

.remember-row {
  display: flex;
  align-items: center;
  margin-bottom: 24px;
  font-size: 13px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #5a6474;
  cursor: pointer;
}

/* Dashboard */
.topbar {
  background: #ffffff;
  border-bottom: 1px solid #e2e6ed;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  position: sticky;
  top: 0;
  z-index: 50;
}

.brand-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.badge-tickets {
  background: #eef2ff;
  color: #3565eb;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.admin-user-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #f1f5f9;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}

.spin-anim {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% { transform: rotate(360deg); }
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 24px;
  width: 100%;
}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: #ffffff;
  border: 1px solid #e2e6ed;
  border-radius: 12px;
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  transition: transform 0.15s, box-shadow 0.15s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.04);
}

.stat-label {
  font-size: 13px;
  color: #5a6474;
}

.stat-number {
  font-size: 28px;
  font-weight: 700;
  font-family: 'Sora', sans-serif;
  color: #11151c;
}

.stat-number.open { color: #059669; }
.stat-number.in_progress { color: #d97706; }
.stat-number.resolved { color: #2563eb; }
.stat-number.urgent { color: #dc2626; }

/* Filter Card */
.filter-card {
  background: #ffffff;
  border: 1px solid #e2e6ed;
  border-radius: 12px;
  padding: 16px 20px;
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.status-tabs {
  display: flex;
  gap: 8px;
  border-bottom: 1px solid #e2e6ed;
  padding-bottom: 12px;
  overflow-x: auto;
}

.status-tab {
  background: none;
  border: 0;
  padding: 8px 14px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #5a6474;
  cursor: pointer;
  white-space: nowrap;
  font-family: inherit;
  transition: all 0.15s;
}

.status-tab:hover {
  background: #f1f5f9;
  color: #11151c;
}

.status-tab.active {
  background: #3565eb;
  color: #ffffff;
}

.filter-row {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.search-input-group {
  position: relative;
  flex: 1;
  min-width: 260px;
}

.search-icon {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  right: 12px;
  color: #94a3b8;
}

.search-input {
  width: 100%;
  padding: 9px 36px 9px 12px;
  border: 1px solid #e2e6ed;
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  box-sizing: border-box;
}

.filter-select {
  padding: 8px 14px;
  border: 1px solid #e2e6ed;
  border-radius: 8px;
  background: #fff;
  font-size: 13px;
  font-family: inherit;
  color: #11151c;
}

/* Table */
.table-card {
  background: #ffffff;
  border: 1px solid #e2e6ed;
  border-radius: 12px;
  overflow: hidden;
}

.table-header-title {
  padding: 18px 20px;
  border-bottom: 1px solid #e2e6ed;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-header-title h2 {
  font-size: 16px;
  font-weight: 700;
  margin: 0;
}

.table-responsive {
  overflow-x: auto;
}

.tickets-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
  text-align: right;
}

.tickets-table th {
  background: #f8fafc;
  padding: 12px 16px;
  color: #5a6474;
  font-weight: 600;
  border-bottom: 1px solid #e2e6ed;
  white-space: nowrap;
}

.tickets-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.tickets-table tr:hover {
  background: #fcfdfe;
}

.code-tag {
  font-family: 'JetBrains Mono', monospace;
  font-weight: 600;
  color: #3565eb;
  background: #eef2ff;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 12px;
  display: inline-block;
  direction: ltr;
}

.customer-info {
  display: flex;
  flex-direction: column;
}

.customer-name {
  font-weight: 600;
  color: #11151c;
}

.customer-email {
  font-size: 12px;
  color: #5a6474;
}

.ticket-subject-cell {
  max-width: 320px;
}

.ticket-subject-link {
  background: none;
  border: 0;
  padding: 0;
  text-align: right;
  font-size: 13px;
  font-weight: 600;
  color: #11151c;
  cursor: pointer;
  font-family: inherit;
  display: block;
}

.ticket-subject-link:hover {
  color: #3565eb;
}

.ticket-excerpt {
  font-size: 12px;
  color: #5a6474;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: 3px;
}

.meta-badge {
  font-size: 12px;
  color: #5a6474;
  font-weight: 500;
}

.replies-count {
  font-size: 13px;
  font-weight: 600;
  color: #5a6474;
}

.ticket-date {
  font-size: 12px;
  color: #5a6474;
}

.row-actions {
  display: flex;
  gap: 6px;
  align-items: center;
}

.mini-status-select {
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  border: 1px solid transparent;
  cursor: pointer;
  font-family: inherit;
}

/* Badges */
.badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}

.badge-open { background: #ecfdf5; color: #059669; }
.badge-in_progress { background: #fffbeb; color: #d97706; }
.badge-resolved { background: #eff6ff; color: #2563eb; }
.badge-closed { background: #f1f5f9; color: #64748b; }

.badge-priority-low { background: #f3f4f6; color: #4b5563; }
.badge-priority-medium { background: #e0f2fe; color: #0369a1; }
.badge-priority-high { background: #ffedd5; color: #c2410c; }
.badge-priority-urgent { background: #fee2e2; color: #b91c1c; font-weight: 700; }

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  border-radius: 8px;
  text-decoration: none;
  cursor: pointer;
  border: 1px solid transparent;
  font-family: inherit;
  transition: all 0.15s ease;
}

.btn-primary { background: #3565eb; color: white; }
.btn-primary:hover { background: #264ecc; }

.btn-secondary { background: #ffffff; border-color: #e2e6ed; color: #11151c; }
.btn-secondary:hover { background: #f8fafc; }

.btn-danger { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.btn-danger:hover { background: #ef4444; color: white; }

.btn-sm { padding: 5px 10px; font-size: 12px; border-radius: 6px; }
.btn-block { width: 100%; padding: 12px; }

.empty-state {
  text-align: center;
  padding: 56px 20px;
  color: #5a6474;
}
.empty-state svg { color: #cbd5e1; margin-bottom: 12px; }

/* Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(17, 21, 28, 0.5);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-box {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 760px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.modal-form-box {
  max-width: 680px;
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e2e6ed;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}

.modal-title-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.modal-tags {
  display: flex;
  gap: 8px;
  align-items: center;
}

.modal-subject {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.btn-close {
  background: none;
  border: 0;
  cursor: pointer;
  color: #5a6474;
  padding: 4px;
}

.btn-close:hover {
  color: #11151c;
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.inquiry-card {
  background: #f8fafc;
  border: 1px solid #e2e6ed;
  border-radius: 12px;
  padding: 18px;
}

.inquiry-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 12px;
  border-bottom: 1px solid #e2e6ed;
  margin-bottom: 12px;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #3565eb;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

.author-email {
  font-size: 12px;
  color: #5a6474;
  font-weight: normal;
}

.inquiry-time {
  font-size: 11px;
  color: #5a6474;
}

.inquiry-badge {
  font-size: 11px;
  background: #e2e8f0;
  padding: 2px 8px;
  border-radius: 4px;
  color: #5a6474;
}

.inquiry-content {
  font-size: 14px;
  line-height: 1.8;
  white-space: pre-wrap;
}

.replies-thread {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.thread-title {
  font-size: 14px;
  font-weight: 700;
  margin: 0;
}

.reply-item {
  background: #f1f5f9;
  border-radius: 10px;
  padding: 14px;
}

.reply-item.admin-reply {
  background: #eff6ff;
  border-right: 3px solid #3565eb;
}

.reply-head {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  margin-bottom: 8px;
  color: #5a6474;
}

.reply-msg {
  font-size: 14px;
  line-height: 1.6;
  white-space: pre-wrap;
}

.reply-box {
  border-top: 1px solid #e2e6ed;
  padding-top: 20px;
}

.reply-box h4 {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 10px;
}

.reply-textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #e2e6ed;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  resize: vertical;
  box-sizing: border-box;
}

.reply-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 14px;
  flex-wrap: wrap;
  gap: 12px;
}

.status-change-group {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #5a6474;
}

.modal-btns {
  display: flex;
  gap: 10px;
}

.modal-footer-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #e2e6ed;
}

.form-stack {
  display: flex;
  flex-direction: column;
}
</style>
