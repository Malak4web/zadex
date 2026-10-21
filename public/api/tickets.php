<?php
/**
 * Zadex Support Tickets API
 * Standalone zero-dependency endpoint using SQLite PDO with JSON fallback.
 * Works natively on any PHP 7.4+ / 8.x web server (Nginx/Apache/Cpanel).
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0755, true);
}

// Protect data dir from direct HTTP access
$htaccess = $dataDir . '/.htaccess';
if (!file_exists($htaccess)) {
    @file_put_contents($htaccess, "Deny from all\n");
}

$dbFile = $dataDir . '/tickets.sqlite';
$jsonFile = $dataDir . '/tickets.json';

// Initialize SQLite or JSON store
$useSqlite = false;
$pdo = null;

if (extension_loaded('pdo_sqlite')) {
    try {
        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tickets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_number TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                subject TEXT NOT NULL,
                category TEXT DEFAULT 'general',
                priority TEXT DEFAULT 'medium',
                status TEXT DEFAULT 'open',
                message TEXT NOT NULL,
                ip_address TEXT,
                user_agent TEXT,
                resolved_at TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS ticket_replies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ticket_id INTEGER NOT NULL,
                sender_type TEXT NOT NULL,
                sender_name TEXT NOT NULL,
                message TEXT NOT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
            );
        ");
        $useSqlite = true;
    } catch (Exception $e) {
        $useSqlite = false;
    }
}

// Seed default tickets if empty
function seedIfEmpty($pdo, $useSqlite, $jsonFile) {
    $defaultTickets = [
        [
            'ticket_number' => 'ZX-AR701',
            'name' => 'أحمد محمود',
            'email' => 'ahmed@company.com',
            'category' => 'bug',
            'priority' => 'urgent',
            'status' => 'open',
            'subject' => 'خطأ 500 عند تفعيل الملحق على ووردبريس 6.7',
            'message' => 'بعد التحديث الأخير إلى Zadex 2.4 ووردبريس 6.7، يظهر خطأ 500 في صفحة إعدادات التخزين المؤقت.',
            'created_at' => date('Y-m-d H:i:s', time() - 7200),
            'updated_at' => date('Y-m-d H:i:s', time() - 3600),
            'replies' => [
                [
                    'sender_type' => 'admin',
                    'sender_name' => 'فريق الدعم الفني',
                    'message' => 'أهلاً بك، يرجى تزويدنا بسجل الأخطاء debug.log للتحقق فوراً.',
                    'created_at' => date('Y-m-d H:i:s', time() - 3600)
                ]
            ]
        ],
        [
            'ticket_number' => 'ZX-FT204',
            'name' => 'سارة العتيبي',
            'email' => 'sara@clouddev.sa',
            'category' => 'feature',
            'priority' => 'medium',
            'status' => 'in_progress',
            'subject' => 'طلب دعم التحويل التلقائي لصور AVIF',
            'message' => 'نود إضافة خيار لتوليد صيغة AVIF بجانب WebP لتقليل أحجام الوسائط أكثر.',
            'created_at' => date('Y-m-d H:i:s', time() - 86400 * 2),
            'updated_at' => date('Y-m-d H:i:s', time() - 86400),
            'replies' => [
                [
                    'sender_type' => 'admin',
                    'sender_name' => 'فريق تطوير Zadex',
                    'message' => 'تم جدولة الميزة للإصدار 2.5 القادم بنهاية هذا الشهر.',
                    'created_at' => date('Y-m-d H:i:s', time() - 86400)
                ]
            ]
        ],
        [
            'ticket_number' => 'ZX-TC890',
            'name' => 'خالد النجار',
            'email' => 'khaled@store.net',
            'category' => 'technical',
            'priority' => 'high',
            'status' => 'resolved',
            'subject' => 'تعارض مع إضافة WooCommerce عند تفريغ السلة',
            'message' => 'عند تفعيل تحسين JavaScript تظهر مشكلة في زر إتمام الطلب.',
            'created_at' => date('Y-m-d H:i:s', time() - 86400 * 5),
            'updated_at' => date('Y-m-d H:i:s', time() - 86400 * 3),
            'resolved_at' => date('Y-m-d H:i:s', time() - 86400 * 3),
            'replies' => [
                [
                    'sender_type' => 'admin',
                    'sender_name' => 'فريق الدعم الفني',
                    'message' => 'تم استثناء مسار wc-cart من التأخير وتم حل المشكلة.',
                    'created_at' => date('Y-m-d H:i:s', time() - 86400 * 3)
                ]
            ]
        ]
    ];

    if ($useSqlite) {
        $count = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
        if ($count == 0) {
            foreach ($defaultTickets as $t) {
                $stmt = $pdo->prepare("INSERT INTO tickets (ticket_number, name, email, subject, category, priority, status, message, resolved_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $t['ticket_number'],
                    $t['name'],
                    $t['email'],
                    $t['subject'],
                    $t['category'],
                    $t['priority'],
                    $t['status'],
                    $t['message'],
                    $t['resolved_at'] ?? null,
                    $t['created_at'],
                    $t['updated_at']
                ]);
                $tid = $pdo->lastInsertId();
                if (!empty($t['replies'])) {
                    foreach ($t['replies'] as $r) {
                        $rstmt = $pdo->prepare("INSERT INTO ticket_replies (ticket_id, sender_type, sender_name, message, created_at) VALUES (?, ?, ?, ?, ?)");
                        $rstmt->execute([$tid, $r['sender_type'], $r['sender_name'], $r['message'], $r['created_at']]);
                    }
                }
            }
        }
    } else {
        if (!file_exists($jsonFile) || filesize($jsonFile) < 5) {
            file_put_contents($jsonFile, json_encode($defaultTickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

seedIfEmpty($pdo, $useSqlite, $jsonFile);

function generateTicketNumber($pdo, $useSqlite, $jsonFile) {
    for ($i = 0; $i < 20; $i++) {
        $num = 'ZX-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        if ($useSqlite) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE ticket_number = ?");
            $stmt->execute([$num]);
            if ($stmt->fetchColumn() == 0) return $num;
        } else {
            return $num;
        }
    }
    return 'ZX-' . mt_rand(10000, 99999);
}

// Parse request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$rawInput = file_get_contents('php://input');
$body = json_decode($rawInput, true) ?? $_POST;

// Support method overriding via body or query
if (isset($body['_method'])) {
    $method = strtoupper($body['_method']);
} elseif (isset($_GET['_method'])) {
    $method = strtoupper($_GET['_method']);
}

$action = $_GET['action'] ?? ($body['action'] ?? '');
$ticketNumber = $_GET['number'] ?? ($_GET['ticket_number'] ?? ($body['ticket_number'] ?? ''));

// ========================
// 1. GET: List all or show one
// ========================
if ($method === 'GET') {
    if (!empty($ticketNumber)) {
        // Show single ticket
        if ($useSqlite) {
            $stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_number = ?");
            $stmt->execute([strtoupper($ticketNumber)]);
            $ticket = $stmt->fetch();
            if (!$ticket) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
                exit;
            }
            $rstmt = $pdo->prepare("SELECT id, sender_type, sender_name, message, created_at FROM ticket_replies WHERE ticket_id = ? ORDER BY id ASC");
            $rstmt->execute([$ticket['id']]);
            $ticket['replies'] = $rstmt->fetchAll();
            echo json_encode(['success' => true, 'ticket' => $ticket]);
            exit;
        } else {
            $tickets = json_decode(file_get_contents($jsonFile), true) ?? [];
            foreach ($tickets as $t) {
                if (strcasecmp($t['ticket_number'], $ticketNumber) === 0) {
                    echo json_encode(['success' => true, 'ticket' => $t]);
                    exit;
                }
            }
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
            exit;
        }
    }

    // List tickets
    if ($useSqlite) {
        $stmt = $pdo->query("SELECT * FROM tickets ORDER BY id DESC");
        $tickets = $stmt->fetchAll();
        foreach ($tickets as &$t) {
            $rstmt = $pdo->prepare("SELECT id, sender_type, sender_name, message, created_at FROM ticket_replies WHERE ticket_id = ? ORDER BY id ASC");
            $rstmt->execute([$t['id']]);
            $t['replies'] = $rstmt->fetchAll();
        }
    } else {
        $tickets = json_decode(file_get_contents($jsonFile), true) ?? [];
    }

    echo json_encode(['success' => true, 'tickets' => $tickets]);
    exit;
}

// ========================
// 2. POST: Create ticket OR Reply
// ========================
if ($method === 'POST') {
    // If action is reply
    if ($action === 'reply' || isset($body['reply_message'])) {
        $replyMessage = trim($body['reply_message'] ?? ($body['message'] ?? ''));
        $senderName = trim($body['sender_name'] ?? 'فريق دعم Zadex');
        $senderType = $body['sender_type'] ?? 'admin';
        $newStatus = $body['status'] ?? null;

        if (!$replyMessage || !$ticketNumber) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'الرسالة ورقم التذكرة مطلوبان']);
            exit;
        }

        if ($useSqlite) {
            $stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_number = ?");
            $stmt->execute([strtoupper($ticketNumber)]);
            $ticket = $stmt->fetch();
            if (!$ticket) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
                exit;
            }

            $now = date('Y-m-d H:i:s');
            $rstmt = $pdo->prepare("INSERT INTO ticket_replies (ticket_id, sender_type, sender_name, message, created_at) VALUES (?, ?, ?, ?, ?)");
            $rstmt->execute([$ticket['id'], $senderType, $senderName, $replyMessage, $now]);

            if ($newStatus) {
                $resolvedAt = in_array($newStatus, ['resolved', 'closed']) ? $now : null;
                $ustmt = $pdo->prepare("UPDATE tickets SET status = ?, resolved_at = ?, updated_at = ? WHERE id = ?");
                $ustmt->execute([$newStatus, $resolvedAt, $now, $ticket['id']]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'تم إرسال الرد بنجاح',
                'reply' => [
                    'id' => (int)$pdo->lastInsertId(),
                    'sender_type' => $senderType,
                    'sender_name' => $senderName,
                    'message' => $replyMessage,
                    'created_at' => $now
                ]
            ]);
            exit;
        } else {
            $tickets = json_decode(file_get_contents($jsonFile), true) ?? [];
            $found = false;
            foreach ($tickets as &$t) {
                if (strcasecmp($t['ticket_number'], $ticketNumber) === 0) {
                    if (!isset($t['replies'])) $t['replies'] = [];
                    $reply = [
                        'id' => time(),
                        'sender_type' => $senderType,
                        'sender_name' => $senderName,
                        'message' => $replyMessage,
                        'created_at' => date('c')
                    ];
                    $t['replies'][] = $reply;
                    if ($newStatus) $t['status'] = $newStatus;
                    $found = true;
                    break;
                }
            }
            if ($found) {
                file_put_contents($jsonFile, json_encode($tickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                echo json_encode(['success' => true, 'message' => 'تم إرسال الرد بنجاح']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
            }
            exit;
        }
    }

    // Create New Ticket
    $name = trim($body['name'] ?? '');
    $email = trim($body['email'] ?? '');
    $subject = trim($body['subject'] ?? '');
    $message = trim($body['message'] ?? '');
    $category = $body['category'] ?? 'general';
    $priority = $body['priority'] ?? 'medium';
    $status = $body['status'] ?? 'open';

    if (!$name || !$email || !$subject || !$message) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول الإلزامية']);
        exit;
    }

    $num = generateTicketNumber($pdo, $useSqlite, $jsonFile);
    $now = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $newTicket = [
        'ticket_number' => $num,
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'category' => $category,
        'priority' => $priority,
        'status' => $status,
        'message' => $message,
        'ip_address' => $ip,
        'user_agent' => $ua,
        'resolved_at' => null,
        'created_at' => $now,
        'updated_at' => $now,
        'replies' => []
    ];

    if ($useSqlite) {
        $stmt = $pdo->prepare("INSERT INTO tickets (ticket_number, name, email, subject, category, priority, status, message, ip_address, user_agent, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$num, $name, $email, $subject, $category, $priority, $status, $message, $ip, $ua, $now, $now]);
        $newTicket['id'] = (int)$pdo->lastInsertId();
    } else {
        $tickets = json_decode(file_get_contents($jsonFile), true) ?? [];
        array_unshift($tickets, $newTicket);
        file_put_contents($jsonFile, json_encode($tickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'تم إنشاء التذكرة بنجاح',
        'ticket' => $newTicket
    ]);
    exit;
}

// ========================
// 3. PUT / PATCH: Update Ticket (Details or Status)
// ========================
if ($method === 'PUT' || $method === 'PATCH') {
    if (!$ticketNumber) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'رقم التذكرة مطلوب']);
        exit;
    }

    if ($useSqlite) {
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_number = ?");
        $stmt->execute([strtoupper($ticketNumber)]);
        $ticket = $stmt->fetch();
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
            exit;
        }

        $name = trim($body['name'] ?? $ticket['name']);
        $email = trim($body['email'] ?? $ticket['email']);
        $subject = trim($body['subject'] ?? $ticket['subject']);
        $category = $body['category'] ?? $ticket['category'];
        $priority = $body['priority'] ?? $ticket['priority'];
        $status = $body['status'] ?? $ticket['status'];
        $message = trim($body['message'] ?? $ticket['message']);

        $resolvedAt = in_array($status, ['resolved', 'closed']) ? date('Y-m-d H:i:s') : null;
        $now = date('Y-m-d H:i:s');

        $ustmt = $pdo->prepare("
            UPDATE tickets 
            SET name = ?, email = ?, subject = ?, category = ?, priority = ?, status = ?, message = ?, resolved_at = ?, updated_at = ?
            WHERE id = ?
        ");
        $ustmt->execute([$name, $email, $subject, $category, $priority, $status, $message, $resolvedAt, $now, $ticket['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث بيانات التذكرة بنجاح',
            'ticket' => [
                'ticket_number' => $ticket['ticket_number'],
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'category' => $category,
                'priority' => $priority,
                'status' => $status,
                'message' => $message,
                'resolved_at' => $resolvedAt,
                'updated_at' => $now
            ]
        ]);
        exit;
    } else {
        $tickets = json_decode(file_get_contents($jsonFile), true) ?? [];
        $found = false;
        foreach ($tickets as &$t) {
            if (strcasecmp($t['ticket_number'], $ticketNumber) === 0) {
                if (isset($body['name'])) $t['name'] = trim($body['name']);
                if (isset($body['email'])) $t['email'] = trim($body['email']);
                if (isset($body['subject'])) $t['subject'] = trim($body['subject']);
                if (isset($body['category'])) $t['category'] = $body['category'];
                if (isset($body['priority'])) $t['priority'] = $body['priority'];
                if (isset($body['status'])) $t['status'] = $body['status'];
                if (isset($body['message'])) $t['message'] = trim($body['message']);
                $t['updated_at'] = date('c');
                $found = true;
                break;
            }
        }
        if ($found) {
            file_put_contents($jsonFile, json_encode($tickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo json_encode(['success' => true, 'message' => 'تم تحديث بيانات التذكرة بنجاح']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
        }
        exit;
    }
}

// ========================
// 4. DELETE: Remove Ticket
// ========================
if ($method === 'DELETE') {
    if (!$ticketNumber) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'رقم التذكرة مطلوب']);
        exit;
    }

    if ($useSqlite) {
        $stmt = $pdo->prepare("SELECT id FROM tickets WHERE ticket_number = ?");
        $stmt->execute([strtoupper($ticketNumber)]);
        $id = $stmt->fetchColumn();
        if (!$id) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
            exit;
        }

        $pdo->prepare("DELETE FROM ticket_replies WHERE ticket_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM tickets WHERE id = ?")->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'تم حذف التذكرة بنجاح']);
        exit;
    } else {
        $tickets = json_decode(file_get_contents($jsonFile), true) ?? [];
        $newTickets = array_values(array_filter($tickets, function($t) use ($ticketNumber) {
            return strcasecmp($t['ticket_number'], $ticketNumber) !== 0;
        }));
        file_put_contents($jsonFile, json_encode($newTickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true, 'message' => 'تم حذف التذكرة بنجاح']);
        exit;
    }
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
