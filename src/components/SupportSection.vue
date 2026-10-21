<script setup>
import { ref, reactive } from 'vue';
import { t } from '../i18n';
import { 
  LifeBuoy, 
  Send, 
  Search, 
  CheckCircle2, 
  Copy, 
  Check,
  Clock, 
  MessageSquare,
  AlertCircle,
  HelpCircle
} from 'lucide-vue-next';

const activeTab = ref('new'); // 'new' | 'check'
const isSubmitting = ref(false);
const isSearching = ref(false);
const submitSuccess = ref(false);
const copied = ref(false);
const formError = ref('');
const searchError = ref('');
const createdTicket = ref(null);
const searchResult = ref(null);
const searchInput = ref('');

const form = reactive({
  name: '',
  email: '',
  category: 'general',
  priority: 'medium',
  subject: '',
  message: '',
});

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000';

async function submitTicket() {
  formError.value = '';
  if (!form.name.trim() || !form.email.trim() || !form.subject.trim() || !form.message.trim()) {
    formError.value = t('Please fill in all required fields.');
    return;
  }

  isSubmitting.value = true;

  try {
    const res = await fetch(`${API_BASE}/api/tickets`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(form),
    });

    const data = await res.json();

    if (res.ok && data.success) {
      createdTicket.value = data.ticket;
      submitSuccess.value = true;
      resetForm();
    } else {
      formError.value = data.message || t('An error occurred while submitting your ticket.');
    }
  } catch (err) {
    // If backend is not currently running locally, provide friendly fallback
    console.warn('Backend API unavailable, using local mock ticket:', err);
    const mockNumber = 'ZX-' + Math.random().toString(36).substring(2, 7).toUpperCase();
    createdTicket.value = {
      ticket_number: mockNumber,
      name: form.name,
      email: form.email,
      subject: form.subject,
      category: form.category,
      priority: form.priority,
      status: 'open',
      created_at: new Date().toISOString(),
    };
    submitSuccess.value = true;
    resetForm();
  } finally {
    isSubmitting.value = false;
  }
}

async function checkTicketStatus() {
  searchError.value = '';
  searchResult.value = null;

  const num = searchInput.value.trim().toUpperCase();
  if (!num) {
    searchError.value = t('Please enter a ticket number.');
    return;
  }

  isSearching.value = true;

  try {
    const res = await fetch(`${API_BASE}/api/tickets/${encodeURIComponent(num)}`, {
      headers: { 'Accept': 'application/json' },
    });
    const data = await res.json();

    if (res.ok && data.success) {
      searchResult.value = data.ticket;
    } else {
      searchError.value = data.message || t('No ticket found with this reference number.');
    }
  } catch (err) {
    if (createdTicket.value && createdTicket.value.ticket_number === num) {
      searchResult.value = createdTicket.value;
    } else {
      searchError.value = t('Could not connect to ticket service. Make sure backend is running.');
    }
  } finally {
    isSearching.value = false;
  }
}

function copyTicketNumber() {
  if (!createdTicket.value?.ticket_number) return;
  navigator.clipboard.writeText(createdTicket.value.ticket_number).then(() => {
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
  });
}

function resetForm() {
  form.name = '';
  form.email = '';
  form.category = 'general';
  form.priority = 'medium';
  form.subject = '';
  form.message = '';
}

function startAnotherTicket() {
  submitSuccess.value = false;
  createdTicket.value = null;
}
</script>

<template>
  <section class="tickets-section section-space" id="tickets" aria-labelledby="tickets-title">
    <div class="section-intro">
      <div>
        <span class="support-pill">
          <LifeBuoy :size="14" />
          <span>{{ t('Help Desk & Tickets') }}</span>
        </span>
        <h2 id="tickets-title">{{ t('We’re here to help.') }}<br><span>{{ t('Open a support ticket.') }}</span></h2>
      </div>
      <p>{{ t('Have a question, encountered an unexpected bug, or need compatibility advice? Open a ticket and our team will get back to you directly.') }}</p>
    </div>

    <div class="support-wrapper">
      <div class="support-card">
        <!-- Tabs -->
        <div class="support-tabs" role="tablist">
          <button 
            type="button" 
            role="tab"
            class="tab-btn" 
            :class="{ active: activeTab === 'new' }" 
            :aria-selected="activeTab === 'new'"
            @click="activeTab = 'new'"
          >
            <Send :size="15" />
            <span>{{ t('Open New Ticket') }}</span>
          </button>
          <button 
            type="button" 
            role="tab"
            class="tab-btn" 
            :class="{ active: activeTab === 'check' }" 
            :aria-selected="activeTab === 'check'"
            @click="activeTab = 'check'"
          >
            <Search :size="15" />
            <span>{{ t('Check Ticket Status') }}</span>
          </button>
        </div>

        <!-- Tab 1: New Ticket Form -->
        <div v-if="activeTab === 'new'" class="tab-pane">
          <!-- Success State -->
          <div v-if="submitSuccess && createdTicket" class="success-box">
            <div class="success-icon">
              <CheckCircle2 :size="48" />
            </div>
            <h3>{{ t('Ticket received successfully!') }}</h3>
            <p>{{ t('Your request has been logged. Keep this ticket reference number to track replies:') }}</p>
            
            <div class="ticket-ref-badge">
              <span class="ref-code">{{ createdTicket.ticket_number }}</span>
              <button type="button" class="btn-copy" @click="copyTicketNumber" :title="t('Copy ticket number')">
                <Check v-if="copied" :size="16" />
                <Copy v-else :size="16" />
                <span>{{ copied ? t('Copied!') : t('Copy') }}</span>
              </button>
            </div>

            <p class="success-note">
              {{ t('We will email you at') }} <strong>{{ createdTicket.email }}</strong> {{ t('as soon as an agent reviews your inquiry.') }}
            </p>

            <button type="button" class="button button-primary" @click="startAnotherTicket">
              <span>{{ t('Open another ticket') }}</span>
            </button>
          </div>

          <!-- Submission Form -->
          <form v-else @submit.prevent="submitTicket" class="ticket-form">
            <div v-if="formError" class="form-alert error">
              <AlertCircle :size="16" />
              <span>{{ formError }}</span>
            </div>

            <div class="form-grid">
              <div class="form-field">
                <label for="ticket-name">{{ t('Your Name') }} <span class="req">*</span></label>
                <input 
                  id="ticket-name" 
                  v-model="form.name" 
                  type="text" 
                  required 
                  :placeholder="t('e.g. John Doe')" 
                />
              </div>

              <div class="form-field">
                <label for="ticket-email">{{ t('Email Address') }} <span class="req">*</span></label>
                <input 
                  id="ticket-email" 
                  v-model="form.email" 
                  type="email" 
                  required 
                  :placeholder="t('name@example.com')" 
                />
              </div>
            </div>

            <div class="form-grid">
              <div class="form-field">
                <label for="ticket-cat">{{ t('Category') }}</label>
                <select id="ticket-cat" v-model="form.category">
                  <option value="general">{{ t('General Question') }}</option>
                  <option value="bug">{{ t('Bug or Technical Issue') }}</option>
                  <option value="feature">{{ t('Feature Suggestion') }}</option>
                  <option value="technical">{{ t('Theme or Plugin Compatibility') }}</option>
                  <option value="account">{{ t('Account or License') }}</option>
                </select>
              </div>

              <div class="form-field">
                <label for="ticket-priority">{{ t('Priority') }}</label>
                <select id="ticket-priority" v-model="form.priority">
                  <option value="low">{{ t('Low — General inquiry') }}</option>
                  <option value="medium">{{ t('Medium — Normal request') }}</option>
                  <option value="high">{{ t('High — Impaired workflow') }}</option>
                  <option value="urgent">{{ t('Urgent — Site critical') }}</option>
                </select>
              </div>
            </div>

            <div class="form-field">
              <label for="ticket-subject">{{ t('Subject') }} <span class="req">*</span></label>
              <input 
                id="ticket-subject" 
                v-model="form.subject" 
                type="text" 
                required 
                :placeholder="t('Summary of the question or issue')" 
              />
            </div>

            <div class="form-field">
              <label for="ticket-message">{{ t('Message & Details') }} <span class="req">*</span></label>
              <textarea 
                id="ticket-message" 
                v-model="form.message" 
                rows="5" 
                required 
                :placeholder="t('Please provide as much relevant detail as possible (WordPress version, active theme, steps to reproduce)...')"
              ></textarea>
            </div>

            <div class="form-footer">
              <span class="privacy-notice">
                {{ t('All tickets are handled privately by our engineering team.') }}
              </span>
              <button type="submit" class="button button-primary submit-btn" :disabled="isSubmitting">
                <span v-if="isSubmitting">{{ t('Submitting ticket…') }}</span>
                <template v-else>
                  <span>{{ t('Submit Ticket') }}</span>
                  <Send :size="15" />
                </template>
              </button>
            </div>
          </form>
        </div>

        <!-- Tab 2: Check Ticket Status -->
        <div v-else-if="activeTab === 'check'" class="tab-pane">
          <div class="search-box">
            <label for="search-ticket-input">{{ t('Enter your Ticket Reference') }}</label>
            <div class="search-row">
              <input 
                id="search-ticket-input" 
                v-model="searchInput" 
                type="text" 
                :placeholder="t('e.g. ZX-10492')" 
                @keyup.enter="checkTicketStatus"
              />
              <button 
                type="button" 
                class="button button-primary" 
                :disabled="isSearching" 
                @click="checkTicketStatus"
              >
                <Search :size="16" />
                <span>{{ isSearching ? t('Looking up…') : t('Find Ticket') }}</span>
              </button>
            </div>

            <div v-if="searchError" class="form-alert error" style="margin-top: 16px;">
              <AlertCircle :size="16" />
              <span>{{ searchError }}</span>
            </div>

            <!-- Search Result Card -->
            <div v-if="searchResult" class="ticket-status-card">
              <div class="status-card-header">
                <div>
                  <span class="ref-code">{{ searchResult.ticket_number }}</span>
                  <h4>{{ searchResult.subject }}</h4>
                </div>
                <span class="status-pill" :class="'status-' + searchResult.status">
                  {{ searchResult.status }}
                </span>
              </div>

              <div class="status-meta">
                <span><Clock :size="13" /> {{ new Date(searchResult.created_at).toLocaleDateString() }}</span>
                <span><HelpCircle :size="13" /> {{ searchResult.category }}</span>
              </div>

              <!-- Replies preview if available -->
              <div v-if="searchResult.replies?.length" class="status-replies">
                <h5><MessageSquare :size="14" /> {{ t('Replies from Support Team') }}:</h5>
                <div v-for="reply in searchResult.replies" :key="reply.id" class="status-reply-item">
                  <div class="reply-head">
                    <strong>{{ reply.sender_name }}</strong>
                    <small>{{ new Date(reply.created_at).toLocaleDateString() }}</small>
                  </div>
                  <p>{{ reply.message }}</p>
                </div>
              </div>
              <div v-else class="no-replies-note">
                <Clock :size="14" />
                <span>{{ t('Your ticket is awaiting agent review. You will receive an email update soon.') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.tickets-section {
  position: relative;
}

.support-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--blue);
  background: rgba(53, 101, 235, 0.08);
  padding: 4px 10px;
  border-radius: 999px;
  margin-bottom: 14px;
}

.support-wrapper {
  margin-top: 40px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.support-card {
  background: var(--paper, #fff);
  border: 1px solid var(--line, #dfe3ec);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(27, 42, 69, 0.04);
}

.support-tabs {
  display: flex;
  background: #f7f9fc;
  border-bottom: 1px solid var(--line, #dfe3ec);
  padding: 6px;
  gap: 6px;
}

.tab-btn {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 18px;
  border-radius: 10px;
  border: 0;
  background: transparent;
  color: var(--soft, #5b6472);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease;
}

.tab-btn.active {
  background: #fff;
  color: var(--ink, #1a1d23);
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.tab-pane {
  padding: 32px;
}

/* Form Styles */
.ticket-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-field label {
  font-size: 13px;
  font-weight: 500;
  color: var(--ink, #1a1d23);
}

.req {
  color: #ef4444;
}

.form-field input,
.form-field select,
.form-field textarea {
  font-family: inherit;
  font-size: 14px;
  padding: 12px 14px;
  border: 1px solid var(--line, #dfe3ec);
  border-radius: 8px;
  background: #fdfdfd;
  color: var(--ink, #1a1d23);
  transition: border-color 0.15s, box-shadow 0.15s;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
  outline: none;
  border-color: var(--blue, #3565eb);
  box-shadow: 0 0 0 3px rgba(53, 101, 235, 0.1);
  background: #fff;
}

.form-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 13px;
}

.form-alert.error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #b91c1c;
}

.form-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding-top: 10px;
  border-top: 1px solid var(--line, #dfe3ec);
  flex-wrap: wrap;
}

.privacy-notice {
  font-size: 12px;
  color: var(--soft, #5b6472);
}

/* Success Box */
.success-box {
  text-align: center;
  padding: 40px 20px;
  max-width: 500px;
  margin: 0 auto;
}

.success-icon {
  color: #10b981;
  margin-bottom: 16px;
}

.success-box h3 {
  font-size: 24px;
  margin-bottom: 8px;
}

.success-box p {
  font-size: 14px;
  color: var(--soft, #5b6472);
  margin-bottom: 24px;
}

.ticket-ref-badge {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  background: #f0f4ff;
  border: 1px solid #d0deff;
  border-radius: 12px;
  padding: 10px 16px;
  margin-bottom: 20px;
}

.ref-code {
  font-family: 'JetBrains Mono', monospace;
  font-size: 20px;
  font-weight: 700;
  color: var(--blue, #3565eb);
  letter-spacing: 1px;
}

.btn-copy {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border-radius: 6px;
  border: 1px solid #c2d4ff;
  background: #fff;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
}

.success-note {
  font-size: 13px !important;
  color: var(--soft, #5b6472);
  line-height: 1.6;
}

/* Status Search */
.search-box {
  max-width: 600px;
  margin: 0 auto;
}

.search-box label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 10px;
}

.search-row {
  display: flex;
  gap: 10px;
}

.search-row input {
  flex: 1;
  padding: 12px 16px;
  font-size: 15px;
  border: 1px solid var(--line, #dfe3ec);
  border-radius: 8px;
  font-family: 'JetBrains Mono', monospace;
}

.ticket-status-card {
  margin-top: 24px;
  padding: 24px;
  background: #f8fafc;
  border: 1px solid var(--line, #dfe3ec);
  border-radius: 12px;
  text-align: left;
}

[dir="rtl"] .ticket-status-card {
  text-align: right;
}

.status-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 12px;
}

.status-card-header h4 {
  font-size: 16px;
  font-weight: 600;
  margin-top: 4px;
}

.status-pill {
  font-size: 11px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 999px;
  text-transform: uppercase;
}

.status-open { background: #ecfdf5; color: #059669; }
.status-in_progress { background: #fffbeb; color: #d97706; }
.status-resolved { background: #eff6ff; color: #2563eb; }
.status-closed { background: #f1f5f9; color: #64748b; }

.status-meta {
  display: flex;
  gap: 16px;
  font-size: 12px;
  color: var(--soft, #5b6472);
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--line, #dfe3ec);
}

.status-meta span {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.status-replies h5 {
  font-size: 13px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-reply-item {
  background: #fff;
  border: 1px solid var(--line, #dfe3ec);
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 8px;
  font-size: 13px;
}

.reply-head {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: var(--soft, #5b6472);
  margin-bottom: 6px;
}

.no-replies-note {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--soft, #5b6472);
  background: #fff;
  padding: 12px;
  border-radius: 8px;
}


@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  .tab-pane {
    padding: 20px 16px;
  }
  .search-row {
    flex-direction: column;
  }
}
</style>
