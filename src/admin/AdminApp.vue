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
  Filter
} from 'lucide-vue-next';

const isAuthenticated = ref(false);
const loginEmail = ref('admin@zadex.com');
const loginPassword = ref('zadex@2026');
const loginError = ref('');
const rememberMe = ref(true);

const defaultTickets = [
  {
    ticket_number: 'ZX-AR701',
    name: 'أحمد المهدي',
    email: 'ahmed.mahdi@example.com',
    subject: 'مشكلة في اتجاه النصوص RTL عند استخدام عناصر معينة',
    category: 'bug',
    priority: 'high',
    status: 'in_progress',
    message: 'السلام عليكم،\nعند استخدام عنصر شبكة التدوينات (Post Loop Grid) مع اللغة العربية، لاحظت أن اتجاه الأسهم والترقيم يظهر مقلوباً قليلاً في الشاشات الصغيرة.\nأرجو فحص المشكلة ولكم جزيل الشكر.',
    ip_address: '192.168.1.105',
    created_at: new Date(Date.now() - 6 * 3600000).toISOString(),
    replies: [
      {
        id: 1,
        sender_type: 'admin',
        sender_name: 'فريق الدعم الفني Zadex',
        message: 'أهلاً بك أخي أحمد،\nنشكرك على ملاحظتك القيمة. تم رصد المشكلة من قبل فريق التطوير، وجارٍ ضبط الـ flex direction في أنماط RTL ليتم تحديثها تلقائياً في الإصدار القادم خلال 24 ساعة بإذن الله.',
        created_at: new Date(Date.now() - 3 * 3600000).toISOString(),
      }
    ]
  },
  {
    ticket_number: 'ZX-FT204',
    name: 'سارة الشريف',
    email: 'sara.designer@example.com',
    subject: 'طلب دعم تصدير واستيراد تصاميم القوالب كملفات JSON',
    category: 'feature',
    priority: 'medium',
    status: 'open',
    message: 'مرحباً، هل من الممكن إضافة ميزة تتيح حفظ القالب أو القسم كملف جاهز للاستيراد في مواقع أخرى مبنية بـ Zadex؟ هذه الميزة ستكون مفيدة جداً للمصممين الذين يبنون أكثر من موقع.',
    ip_address: '192.168.1.140',
    created_at: new Date(Date.now() - 24 * 3600000).toISOString(),
    replies: []
  },
  {
    ticket_number: 'ZX-TC890',
    name: 'Marc Dupont',
    email: 'marc.dupont@example.org',
    subject: 'Compatibility with WooCommerce 9.4 and PHP 8.3',
    category: 'technical',
    priority: 'low',
    status: 'resolved',
    message: 'Hello, I just tested Zadex on a staging site with WooCommerce 9.4 and PHP 8.3. Does Zadex support the new High-Performance Order Storage (HPOS) tables out of the box?',
    ip_address: '82.165.197.1',
    resolved_at: new Date(Date.now() - 12 * 3600000).toISOString(),
    created_at: new Date(Date.now() - 48 * 3600000).toISOString(),
    replies: [
      {
        id: 2,
        sender_type: 'admin',
        sender_name: 'Zadex Core Support',
        message: 'Hello Marc,\nYes, Zadex is 100% compatible with HPOS and does not directly mutate internal WooCommerce order tables. All WooCommerce loop and product templates work seamlessly.',
        created_at: new Date(Date.now() - 14 * 3600000).toISOString(),
      }
    ]
  },
  {
    ticket_number: 'ZX-UG015',
    name: 'خالد ناصر',
    email: 'khaled.nasser@example.com',
    subject: 'استفسار عن إمكانية تضمين كود CSS و JavaScript مخصص في كل صفحة',
    category: 'general',
    priority: 'urgent',
    status: 'open',
    message: 'أحتاج لتضمين مكتبة أنيميشن مخصصة في صفحة الهبوط فقط. هل يوجد تبويب لإدخال Custom CSS/JS لكل صفحة على حدة دون التأثير على كامل الموقع؟',
    ip_address: '197.38.22.90',
    created_at: new Date(Date.now() - 1 * 3600000).toISOString(),
    replies: []
  }
];

const tickets = ref([]);
const activeTicket = ref(null);
const replyMessage = ref('');
const replyStatus = ref('in_progress');
const toastMessage = ref('');

const filterStatus = ref('all');
const filterCategory = ref('all');
const filterPriority = ref('all');
const searchQuery = ref('');

function loadTickets() {
  try {
    const saved = localStorage.getItem('zadex_tickets_store');
    if (saved) {
      tickets.value = JSON.parse(saved);
    } else {
      tickets.value = defaultTickets;
      saveTickets();
    }
  } catch {
    tickets.value = defaultTickets;
  }
}

function saveTickets() {
  try {
    localStorage.setItem('zadex_tickets_store', JSON.stringify(tickets.value));
  } catch {}
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
  const all = tickets.value;
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
  return tickets.value.filter(t => {
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

function openTicket(ticket) {
  activeTicket.value = JSON.parse(JSON.stringify(ticket));
  replyStatus.value = ticket.status === 'open' ? 'in_progress' : ticket.status;
  replyMessage.value = '';
}

function closeTicketModal() {
  activeTicket.value = null;
}

function updateTicketStatusDirect(ticket, newStatus) {
  ticket.status = newStatus;
  if (newStatus === 'resolved' || newStatus === 'closed') {
    ticket.resolved_at = new Date().toISOString();
  } else {
    ticket.resolved_at = null;
  }
  saveTickets();
  showToast(`تم تحديث حالة التذكرة ${ticket.ticket_number} بنجاح.`);
}

function submitReply() {
  if (!replyMessage.value.trim()) return;

  const target = tickets.value.find(t => t.ticket_number === activeTicket.value.ticket_number);
  if (!target) return;

  if (!target.replies) target.replies = [];

  const newReply = {
    id: Date.now(),
    sender_type: 'admin',
    sender_name: 'فريق دعم Zadex',
    message: replyMessage.value.trim(),
    created_at: new Date().toISOString()
  };

  target.replies.push(newReply);
  target.status = replyStatus.value;
  if (replyStatus.value === 'resolved' || replyStatus.value === 'closed') {
    target.resolved_at = new Date().toISOString();
  }

  // update modal view
  activeTicket.value.replies.push(newReply);
  activeTicket.value.status = replyStatus.value;

  replyMessage.value = '';
  saveTickets();
  showToast('تم إرسال الرد وتحديث التذكرة بنجاح.');
}

function deleteTicket(ticketNumber) {
  if (!confirm(`هل أنت متأكد من حذف التذكرة ${ticketNumber} نهائياً؟`)) return;
  tickets.value = tickets.value.filter(t => t.ticket_number !== ticketNumber);
  saveTickets();
  if (activeTicket.value?.ticket_number === ticketNumber) {
    activeTicket.value = null;
  }
  showToast(`تم حذف التذكرة ${ticketNumber} بنجاح.`);
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
              placeholder="admin@zadex.com" 
              dir="ltr"
            />
          </div>

          <div class="form-group">
            <label for="admin-pass">كلمة المرور</label>
            <input 
              id="admin-pass" 
              v-model="loginPassword" 
              type="password" 
              required 
              placeholder="••••••••" 
              dir="ltr"
            />
          </div>

          <div class="remember-row">
            <label class="checkbox-label">
              <input type="checkbox" v-model="rememberMe" />
              <span>تذكرني على هذا الجهاز</span>
            </label>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            <span>تسجيل الدخول</span>
            <ArrowRight :size="16" />
          </button>
        </form>

        <div class="credentials-hint">
          <strong>بيانات تسجيل الدخول:</strong><br>
          البريد: <code class="cred-code">admin@zadex.com</code><br>
          كلمة المرور: <code class="cred-code">zadex@2026</code>
        </div>
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
            <h2>التذاكر الواردة ({{ filteredTickets.length }})</h2>
            <span style="font-size: 12px; color: var(--muted)">تحديث فوري ومباشر</span>
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
                  <th>إجراء</th>
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
                    <span class="badge" :class="'badge-' + ticket.status">
                      {{ ticket.status === 'open' ? 'مفتوحة' : ticket.status === 'in_progress' ? 'قيد المعالجة' : ticket.status === 'resolved' ? 'تم الحل' : 'مغلقة' }}
                    </span>
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
                    <button type="button" class="btn btn-secondary btn-sm" @click="openTicket(ticket)">
                      عرض والرد
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="empty-state">
            <Layers :size="48" />
            <h3>لا توجد تذاكر تطابق هذا البحث</h3>
            <p>جرّب تعديل الفلاتر أو البحث بكلمة مختلفة.</p>
          </div>
        </div>
      </main>

      <!-- Ticket Detail Modal -->
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
            <button type="button" class="btn-close" @click="closeTicketModal">
              <X :size="20" />
            </button>
          </div>

          <div class="modal-body">
            <!-- Customer Initial Message Card -->
            <div class="inquiry-card">
              <div class="inquiry-head">
                <div class="author-info">
                  <div class="avatar">{{ activeTicket.name.charAt(0) }}</div>
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
                  <button type="button" class="btn btn-danger btn-sm" @click="deleteTicket(activeTicket.ticket_number)">
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
  right: 20px;
  z-index: 9999;
  background: #10b981;
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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
  font-size: 26px;
  font-weight: 700;
  letter-spacing: -0.5px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #11151c;
  text-decoration: none;
}

.brand-dot {
  width: 7px;
  height: 7px;
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
}

.form-group input {
  width: 100%;
  padding: 11px 14px;
  border: 1px solid #e2e6ed;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  background: #fdfdfd;
  color: #11151c;
  box-sizing: border-box;
}

.form-group input:focus {
  outline: none;
  border-color: #3565eb;
  box-shadow: 0 0 0 3px rgba(53, 101, 235, 0.1);
  background: #fff;
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

.credentials-hint {
  margin-top: 24px;
  padding: 12px;
  border-top: 1px solid #e2e6ed;
  background: #f8fafc;
  border-radius: 8px;
  font-size: 12px;
  color: #5a6474;
  line-height: 1.6;
}

.cred-code {
  font-family: 'JetBrains Mono', monospace;
  font-weight: 600;
  color: #3565eb;
  direction: ltr;
  display: inline-block;
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
  font-size: 11px;
  font-weight: 600;
  padding: 3px 8px;
  background: #ebf0fe;
  color: #3565eb;
  border-radius: 999px;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.admin-user-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #1e3a8a;
  background: #eff6ff;
  padding: 4px 10px;
  border-radius: 6px;
}

/* Container */
.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 32px 24px;
  width: 100%;
  box-sizing: border-box;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 28px;
}

.stat-card {
  background: #ffffff;
  border: 1px solid #e2e6ed;
  border-radius: 12px;
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.stat-label {
  font-size: 13px;
  color: #5a6474;
  font-weight: 500;
}

.stat-number {
  font-size: 30px;
  font-weight: 700;
  font-family: 'Sora', sans-serif;
  color: #11151c;
  line-height: 1.1;
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
  border-bottom: 1px solid #f0f3f7;
  padding-bottom: 14px;
}

.status-tab {
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 500;
  border: 0;
  color: #5a6474;
  background: #f8fafc;
  cursor: pointer;
  transition: all 0.15s;
}

.status-tab.active {
  background: #3565eb;
  color: white;
}

.filter-row {
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
  border: 1px solid #e2e6ed;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  background: #f8fafc;
  color: #11151c;
  box-sizing: border-box;
}

.search-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #5a6474;
  pointer-events: none;
}

.filter-select {
  padding: 9px 14px;
  border: 1px solid #e2e6ed;
  border-radius: 8px;
  font-size: 13px;
  font-family: inherit;
  background: #f8fafc;
  color: #11151c;
  cursor: pointer;
}

/* Table Card */
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
}

.table-responsive {
  overflow-x: auto;
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
  color: #5a6474;
  font-size: 12px;
  border-bottom: 1px solid #e2e6ed;
  white-space: nowrap;
}

.tickets-table td {
  padding: 14px 18px;
  border-bottom: 1px solid #f0f3f7;
  vertical-align: middle;
}

.ticket-subject-cell {
  max-width: 320px;
}

.ticket-subject-link {
  color: #11151c;
  font-weight: 600;
  background: none;
  border: 0;
  padding: 0;
  text-align: right;
  cursor: pointer;
  display: block;
  margin-bottom: 2px;
  font-size: 14px;
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
  color: #5a6474;
  font-family: 'Public Sans', sans-serif;
}

.code-tag {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  font-weight: 600;
  color: #3565eb;
  background: #ebf0fe;
  padding: 2px 6px;
  border-radius: 4px;
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
  gap: 8px;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 500;
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

.btn-danger { background: #fee2e2; color: #b91c1c; }
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
}

.btn-close {
  background: none;
  border: 0;
  cursor: pointer;
  color: #5a6474;
  padding: 4px;
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
  color: #5a6474;
}

.reply-item {
  background: #f8fafc;
  border: 1px solid #e2e6ed;
  border-radius: 10px;
  padding: 14px;
}

.reply-item.admin-reply {
  background: #f0f5ff;
  border-color: #d0deff;
}

.reply-head {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #5a6474;
  margin-bottom: 6px;
}

.reply-msg {
  font-size: 14px;
  line-height: 1.7;
  white-space: pre-wrap;
}

.reply-box {
  background: #ffffff;
  border: 1px solid #e2e6ed;
  border-radius: 12px;
  padding: 18px;
}

.reply-box h4 {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 12px;
}

.reply-textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #e2e6ed;
  border-radius: 8px;
  font-size: 14px;
  font-family: inherit;
  box-sizing: border-box;
  margin-bottom: 14px;
  resize: vertical;
}

.reply-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.status-change-group {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
}

.modal-btns {
  display: flex;
  align-items: center;
  gap: 10px;
}
</style>
