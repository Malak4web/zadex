<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ticket 1: Bug report with admin reply
        $ticket1 = Ticket::firstOrCreate(
            ['ticket_number' => 'ZX-AR701'],
            [
                'name' => 'أحمد المهدي',
                'email' => 'ahmed.mahdi@example.com',
                'subject' => 'مشكلة في اتجاه النصوص RTL عند استخدام عناصر معينة',
                'category' => 'bug',
                'priority' => 'high',
                'status' => 'in_progress',
                'message' => "السلام عليكم،\nعند استخدام عنصر شبكة التدوينات (Post Loop Grid) مع اللغة العربية، لاحظت أن اتجاه الأسهم والترقيم يظهر مقلوباً قليلاً في الشاشات الصغيرة.\nأرجو فحص المشكلة ولكم جزيل الشكر.",
                'ip_address' => '192.168.1.105',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0.0.0',
                'created_at' => now()->subHours(6),
            ]
        );

        if ($ticket1->replies()->count() === 0) {
            TicketReply::create([
                'ticket_id' => $ticket1->id,
                'sender_type' => 'admin',
                'sender_name' => 'فريق الدعم الفني Zadex',
                'message' => "أهلاً بك أخي أحمد،\nنشكرك على ملاحظتك القيمة. تم رصد المشكلة من قبل فريق التطوير، وجارٍ ضبط الـ flex direction في أنماط RTL ليتم تحديثها تلقائياً في الإصدار القادم خلال 24 ساعة بإذن الله.",
                'created_at' => now()->subHours(3),
            ]);
        }

        // Ticket 2: Feature Request
        Ticket::firstOrCreate(
            ['ticket_number' => 'ZX-FT204'],
            [
                'name' => 'سارة الشريف',
                'email' => 'sara.designer@example.com',
                'subject' => 'طلب دعم تصدير واستيراد تصاميم القوالب كملفات JSON',
                'category' => 'feature',
                'priority' => 'medium',
                'status' => 'open',
                'message' => "مرحباً، هل من الممكن إضافة ميزة تتيح حفظ القالب أو القسم كملف جاهز للاستيراد في مواقع أخرى مبنية بـ Zadex؟ هذه الميزة ستكون مفيدة جداً للمصممين الذين يبنون أكثر من موقع.",
                'ip_address' => '192.168.1.140',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15',
                'created_at' => now()->subDay(),
            ]
        );

        // Ticket 3: Technical compatibility (Resolved)
        $ticket3 = Ticket::firstOrCreate(
            ['ticket_number' => 'ZX-TC890'],
            [
                'name' => 'Marc Dupont',
                'email' => 'marc.dupont@example.org',
                'subject' => 'Compatibility with WooCommerce 9.4 and PHP 8.3',
                'category' => 'technical',
                'priority' => 'low',
                'status' => 'resolved',
                'message' => "Hello, I just tested Zadex on a staging site with WooCommerce 9.4 and PHP 8.3. Does Zadex support the new High-Performance Order Storage (HPOS) tables out of the box?",
                'ip_address' => '82.165.197.1',
                'user_agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64) Firefox/131.0',
                'resolved_at' => now()->subHours(12),
                'created_at' => now()->subDays(2),
            ]
        );

        if ($ticket3->replies()->count() === 0) {
            TicketReply::create([
                'ticket_id' => $ticket3->id,
                'sender_type' => 'admin',
                'sender_name' => 'Zadex Core Support',
                'message' => "Hello Marc,\nYes, Zadex is 100% compatible with HPOS and does not directly mutate internal WooCommerce order tables. All WooCommerce loop and product templates work seamlessly.",
                'created_at' => now()->subHours(14),
            ]);
        }

        // Ticket 4: Urgent general question
        Ticket::firstOrCreate(
            ['ticket_number' => 'ZX-UG015'],
            [
                'name' => 'خالد ناصر',
                'email' => 'khaled.nasser@example.com',
                'subject' => 'استفسار عن إمكانية تضمين كود CSS و JavaScript مخصص في كل صفحة',
                'category' => 'general',
                'priority' => 'urgent',
                'status' => 'open',
                'message' => "أحتاج لتضمين مكتبة أنيميشن مخصصة في صفحة الهبوط فقط. هل يوجد تبويب لإدخال Custom CSS/JS لكل صفحة على حدة دون التأثير على كامل الموقع؟",
                'ip_address' => '197.38.22.90',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Edge/130.0.0.0',
                'created_at' => now()->subHours(1),
            ]
        );
    }
}
