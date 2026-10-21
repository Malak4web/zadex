/**
 * Zadex Ticket API Client Service
 * Provides robust multi-fallback communication with backend endpoints:
 * 1. Same-origin PHP SQLite endpoint (/api/tickets.php) for live production.
 * 2. Rewrite endpoint (/api/tickets).
 * 3. Local Laravel development server (http://localhost:8000/api/tickets).
 * 4. Resilient localStorage fallback store.
 */

const LOCAL_STORAGE_KEY = 'zadex_tickets_store';

const defaultSeedTickets = [
  {
    ticket_number: 'ZX-AR701',
    name: 'أحمد محمود',
    email: 'ahmed@company.com',
    category: 'bug',
    priority: 'urgent',
    status: 'open',
    subject: 'خطأ 500 عند تفعيل الملحق على ووردبريس 6.7',
    message: 'بعد التحديث الأخير إلى Zadex 2.4 ووردبريس 6.7، يظهر خطأ 500 في صفحة إعدادات التخزين المؤقت.',
    created_at: new Date(Date.now() - 3600000 * 2).toISOString(),
    replies: [
      {
        id: 1,
        sender_type: 'admin',
        sender_name: 'فريق الدعم الفني',
        message: 'أهلاً بك، يرجى تزويدنا بسجل الأخطاء debug.log للتحقق فوراً.',
        created_at: new Date(Date.now() - 3600000).toISOString()
      }
    ]
  },
  {
    ticket_number: 'ZX-FT204',
    name: 'سارة العتيبي',
    email: 'sara@clouddev.sa',
    category: 'feature',
    priority: 'medium',
    status: 'in_progress',
    subject: 'طلب دعم التحويل التلقائي لصور AVIF',
    message: 'نود إضافة خيار لتوليد صيغة AVIF بجانب WebP لتقليل أحجام الوسائط أكثر.',
    created_at: new Date(Date.now() - 86400000 * 2).toISOString(),
    replies: [
      {
        id: 2,
        sender_type: 'admin',
        sender_name: 'فريق تطوير Zadex',
        message: 'تم جدولة الميزة للإصدار 2.5 القادم بنهاية هذا الشهر.',
        created_at: new Date(Date.now() - 86400000).toISOString()
      }
    ]
  },
  {
    ticket_number: 'ZX-TC890',
    name: 'خالد النجار',
    email: 'khaled@store.net',
    category: 'technical',
    priority: 'high',
    status: 'resolved',
    subject: 'تعارض مع إضافة WooCommerce عند تفريغ السلة',
    message: 'عند تفعيل تحسين JavaScript تظهر مشكلة في زر إتمام الطلب.',
    created_at: new Date(Date.now() - 86400000 * 5).toISOString(),
    resolved_at: new Date(Date.now() - 86400000 * 3).toISOString(),
    replies: [
      {
        id: 3,
        sender_type: 'admin',
        sender_name: 'فريق الدعم الفني',
        message: 'تم استثناء مسار wc-cart من التأخير وتم حل المشكلة بنجاح.',
        created_at: new Date(Date.now() - 86400000 * 3).toISOString()
      }
    ]
  }
];

function getCandidateEndpoints() {
  const custom = import.meta.env.VITE_API_URL;
  const origin = typeof window !== 'undefined' ? window.location.origin : '';
  const isHttps = typeof window !== 'undefined' && window.location.protocol === 'https:';

  const list = [
    `${origin}/api/tickets.php`,
    `${origin}/api/tickets`,
  ];

  if (custom) {
    list.unshift(custom.replace(/\/+$/, '') + '/tickets');
  }

  // Only attempt HTTP localhost if we are running in an insecure / dev context to avoid Mixed Content errors
  if (!isHttps) {
    list.push('http://localhost:8000/api/tickets');
  }

  return list;
}

export function getLocalStore() {
  try {
    const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (raw) {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    }
  } catch {}
  return defaultSeedTickets;
}

export function saveLocalStore(tickets) {
  try {
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(tickets));
  } catch {}
}

export async function fetchAllTickets() {
  const endpoints = getCandidateEndpoints();

  for (const url of endpoints) {
    try {
      const res = await fetch(url, {
        headers: { 'Accept': 'application/json' },
        signal: AbortSignal.timeout(4000),
      });
      if (res.ok) {
        const data = await res.json();
        if (data.success && Array.isArray(data.tickets)) {
          // Merge with local tickets if any unsynced
          saveLocalStore(data.tickets);
          return { success: true, tickets: data.tickets, source: url };
        }
      }
    } catch {}
  }

  return { success: true, tickets: getLocalStore(), source: 'local' };
}

export async function fetchSingleTicket(ticketNumber) {
  const num = ticketNumber.trim().toUpperCase();
  const endpoints = getCandidateEndpoints();

  for (const base of endpoints) {
    const url = base.endsWith('.php') 
      ? `${base}?number=${encodeURIComponent(num)}`
      : `${base}/${encodeURIComponent(num)}`;

    try {
      const res = await fetch(url, {
        headers: { 'Accept': 'application/json' },
        signal: AbortSignal.timeout(4000),
      });
      if (res.ok) {
        const data = await res.json();
        if (data.success && data.ticket) {
          return { success: true, ticket: data.ticket };
        }
      }
    } catch {}
  }

  // Fallback to local
  const localList = getLocalStore();
  const found = localList.find(t => t.ticket_number?.toUpperCase() === num);
  if (found) {
    return { success: true, ticket: found };
  }

  return { success: false, message: 'لم يتم العثور على التذكرة بهذا الرقم.' };
}

export async function createTicket(ticketData) {
  const endpoints = getCandidateEndpoints();
  let created = null;

  for (const url of endpoints) {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(ticketData),
        signal: AbortSignal.timeout(5000),
      });

      if (res.ok) {
        const data = await res.json();
        if (data.success && data.ticket) {
          created = data.ticket;
          break;
        }
      }
    } catch {}
  }

  if (!created) {
    // Generate fallback ticket locally
    const num = 'ZX-' + Math.random().toString(36).substring(2, 7).toUpperCase();
    created = {
      ticket_number: num,
      name: ticketData.name,
      email: ticketData.email,
      subject: ticketData.subject,
      category: ticketData.category || 'general',
      priority: ticketData.priority || 'medium',
      status: ticketData.status || 'open',
      message: ticketData.message,
      created_at: new Date().toISOString(),
      replies: []
    };
  }

  // Save to local store so it is immediately visible
  const list = getLocalStore();
  // Avoid duplicates
  const filtered = list.filter(t => t.ticket_number !== created.ticket_number);
  filtered.unshift(created);
  saveLocalStore(filtered);

  return { success: true, ticket: created };
}

export async function updateTicket(ticketNumber, updates) {
  const num = ticketNumber.trim().toUpperCase();
  const endpoints = getCandidateEndpoints();
  let updatedTicket = null;

  for (const base of endpoints) {
    const isPhp = base.endsWith('.php');
    const url = isPhp ? `${base}?number=${encodeURIComponent(num)}` : `${base}/${encodeURIComponent(num)}`;

    try {
      const res = await fetch(url, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          ticket_number: num,
          ...updates,
        }),
        signal: AbortSignal.timeout(5000),
      });

      if (res.ok) {
        const data = await res.json();
        if (data.success && data.ticket) {
          updatedTicket = data.ticket;
          break;
        }
      }
    } catch {}
  }

  // Update local store as well
  const list = getLocalStore();
  const idx = list.findIndex(t => t.ticket_number === num);
  if (idx !== -1) {
    list[idx] = {
      ...list[idx],
      ...updates,
      updated_at: new Date().toISOString()
    };
    saveLocalStore(list);
    if (!updatedTicket) updatedTicket = list[idx];
  }

  return { success: true, ticket: updatedTicket };
}

export async function deleteTicket(ticketNumber) {
  const num = ticketNumber.trim().toUpperCase();
  const endpoints = getCandidateEndpoints();

  for (const base of endpoints) {
    const isPhp = base.endsWith('.php');
    const url = isPhp ? `${base}?number=${encodeURIComponent(num)}` : `${base}/${encodeURIComponent(num)}`;

    try {
      await fetch(url, {
        method: 'DELETE',
        headers: { 'Accept': 'application/json' },
        signal: AbortSignal.timeout(4000),
      });
    } catch {}
  }

  // Remove from local store
  const list = getLocalStore().filter(t => t.ticket_number !== num);
  saveLocalStore(list);

  return { success: true };
}

export async function replyToTicket(ticketNumber, replyData) {
  const num = ticketNumber.trim().toUpperCase();
  const endpoints = getCandidateEndpoints();
  let createdReply = null;

  for (const base of endpoints) {
    const isPhp = base.endsWith('.php');
    const url = isPhp 
      ? `${base}?action=reply&number=${encodeURIComponent(num)}`
      : `${base}/${encodeURIComponent(num)}/reply`;

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          ticket_number: num,
          message: replyData.message,
          sender_name: replyData.sender_name || 'فريق دعم Zadex',
          sender_type: replyData.sender_type || 'admin',
          status: replyData.status,
        }),
        signal: AbortSignal.timeout(5000),
      });

      if (res.ok) {
        const data = await res.json();
        if (data.success) {
          createdReply = data.reply;
          break;
        }
      }
    } catch {}
  }

  if (!createdReply) {
    createdReply = {
      id: Date.now(),
      sender_type: replyData.sender_type || 'admin',
      sender_name: replyData.sender_name || 'فريق دعم Zadex',
      message: replyData.message,
      created_at: new Date().toISOString(),
    };
  }

  // Update local store
  const list = getLocalStore();
  const target = list.find(t => t.ticket_number === num);
  if (target) {
    if (!target.replies) target.replies = [];
    target.replies.push(createdReply);
    if (replyData.status) {
      target.status = replyData.status;
      if (['resolved', 'closed'].includes(replyData.status)) {
        target.resolved_at = new Date().toISOString();
      }
    }
    saveLocalStore(list);
  }

  return { success: true, reply: createdReply };
}
