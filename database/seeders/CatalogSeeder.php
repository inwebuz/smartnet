<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['websites', null, ['Разработка сайтов', 'Veb-saytlar yaratish', 'Website development'], ['Сайты для продаж, презентации компании и онлайн-сервисов.', 'Savdo, kompaniya taqdimoti va onlayn xizmatlar uchun saytlar.', 'Websites for sales, company presence, and online services.'], 5000000],
            ['telegram-bots', null, ['Telegram-боты', 'Telegram botlar', 'Telegram bots'], ['Автоматизация общения, продаж и поддержки в Telegram.', 'Telegram orqali muloqot, savdo va yordamni avtomatlashtirish.', 'Automate conversations, sales, and support in Telegram.'], 4000000],
            ['business-systems', null, ['Бизнес-системы', 'Biznes tizimlari', 'Business systems'], ['Внутренние системы для управления процессами и данными.', 'Jarayonlar va ma’lumotlarni boshqarish uchun ichki tizimlar.', 'Internal systems for processes, teams, and data.'], 25000000],
            ['crm-systems', 'business-systems', ['CRM-системы', 'CRM tizimlari', 'CRM systems'], ['Управление клиентами, продажами и заявками в одной системе.', 'Mijozlar, savdo va so‘rovlarni yagona tizimda boshqarish.', 'Manage customers, sales, and leads in one system.'], 25000000],
            ['erp-systems', 'business-systems', ['ERP-системы', 'ERP tizimlari', 'ERP systems'], ['Учёт ресурсов, операций и финансов в едином контуре.', 'Resurslar, operatsiyalar va moliyani yagona tizimda boshqarish.', 'Connect resources, operations, and finance in one system.'], 60000000],
            ['integrations', null, ['Интеграции и автоматизация', 'Integratsiya va avtomatlashtirish', 'Integrations and automation'], ['Связываем сервисы и убираем повторяющиеся ручные операции.', 'Xizmatlarni bog‘laymiz va takroriy qo‘l mehnatini kamaytiramiz.', 'Connect services and remove repetitive manual work.'], 5000000],
        ];

        $categoryIds = [];
        foreach ($categories as $order => [$slug, $parentSlug, $titles, $summaries, $price]) {
            $title = array_combine(['ru', 'uz', 'en'], $titles);
            $summary = array_combine(['ru', 'uz', 'en'], $summaries);
            $category = ServiceCategory::firstOrCreate(['slug' => $slug], [
                'parent_id' => $parentSlug ? $categoryIds[$parentSlug] : null,
                'title' => $title, 'summary' => $summary,
                'seo_title' => array_map(fn (string $value) => $value.' | Smartnet', $title),
                'seo_description' => $summary,
                'price_from' => $price, 'price_currency' => 'UZS',
                'sort_order' => $order, 'is_published' => false,
            ]);
            $categoryIds[$slug] = $category->id;
        }

        $services = [
            ['landing-page', 'websites', ['Лендинг', 'Landing sahifa', 'Landing page'], ['Одностраничный сайт для продукта или рекламной кампании.', 'Mahsulot yoki reklama kampaniyasi uchun bir sahifali sayt.', 'A focused single-page website for a product or campaign.'], 5000000],
            ['corporate-website', 'websites', ['Корпоративный сайт', 'Korporativ sayt', 'Corporate website'], ['Сайт компании с услугами, кейсами и заявками.', 'Xizmatlar, loyihalar va so‘rovlar uchun kompaniya sayti.', 'A company website for services, case studies, and leads.'], 12000000],
            ['online-store', 'websites', ['Интернет-магазин', 'Internet do‘kon', 'Online store'], ['Каталог, корзина и оформление заказов.', 'Katalog, savat va buyurtmalarni rasmiylashtirish.', 'Product catalog, cart, and checkout.'], 25000000],
            ['web-application', 'websites', ['Веб-приложение', 'Veb-ilova', 'Web application'], ['Личный кабинет или сервис с индивидуальной логикой.', 'Shaxsiy kabinet yoki maxsus mantiqqa ega xizmat.', 'A customer portal or custom online service.'], 35000000],
            ['telegram-info-bot', 'telegram-bots', ['Информационный бот', 'Axborot boti', 'Information bot'], ['Ответы на частые вопросы и сбор обращений.', 'Tez-tez beriladigan savollarga javob va murojaatlarni yig‘ish.', 'Answer common questions and collect requests.'], 4000000],
            ['telegram-sales-bot', 'telegram-bots', ['Бот для продаж', 'Savdo boti', 'Sales bot'], ['Каталог, сценарии продаж и передача заявок менеджеру.', 'Katalog, savdo ssenariylari va so‘rovlarni menejerga uzatish.', 'A catalog and sales flow that sends leads to your team.'], 8000000],
            ['telegram-support-bot', 'telegram-bots', ['Бот поддержки', 'Yordam boti', 'Support bot'], ['Приём запросов, маршрутизация и уведомления команды.', 'So‘rovlarni qabul qilish, yo‘naltirish va jamoani xabardor qilish.', 'Collect support requests and notify the right team.'], 7000000],
            ['custom-crm', 'crm-systems', ['CRM под ваши процессы', 'Maxsus CRM', 'Custom CRM'], ['Клиенты, воронка продаж, задачи и отчёты в одной системе.', 'Mijozlar, savdo bosqichlari, vazifalar va hisobotlar yagona tizimda.', 'Customers, pipeline, tasks, and reports in one system.'], 25000000],
            ['crm-integration', 'crm-systems', ['Интеграция CRM', 'CRM integratsiyasi', 'CRM integration'], ['Связь CRM с сайтом, телефонией и мессенджерами.', 'CRMni sayt, telefoniya va messenjerlar bilan bog‘lash.', 'Connect your CRM with your website, calls, and messaging.'], 8000000],
            ['custom-erp', 'erp-systems', ['ERP под ваш бизнес', 'Maxsus ERP', 'Custom ERP'], ['Учёт заказов, запасов и операций под ваши процессы.', 'Buyurtmalar, ombor va operatsiyalarni jarayonlaringizga mos hisobga olish.', 'Manage orders, inventory, and operations around your workflows.'], 60000000],
            ['erp-module', 'erp-systems', ['Модуль для ERP', 'ERP moduli', 'ERP module'], ['Отдельный модуль или доработка действующей системы.', 'Mavjud tizim uchun alohida modul yoki takomillashtirish.', 'Extend an existing system with a focused module.'], 20000000],
            ['api-integration', 'integrations', ['API-интеграция', 'API integratsiyasi', 'API integration'], ['Обмен данными между сайтом, CRM и внешними сервисами.', 'Sayt, CRM va tashqi xizmatlar o‘rtasida ma’lumot almashinuvi.', 'Move data between your website, CRM, and external services.'], 7000000],
            ['workflow-automation', 'integrations', ['Автоматизация процессов', 'Jarayonlarni avtomatlashtirish', 'Workflow automation'], ['Автоматические действия, уведомления и отчёты вместо ручной рутины.', 'Qo‘l mehnati o‘rniga avtomatik amallar, xabarnomalar va hisobotlar.', 'Replace repetitive tasks with automated actions and reports.'], 5000000],
        ];

        foreach ($services as $order => [$slug, $categorySlug, $titles, $summaries, $price]) {
            $title = array_combine(['ru', 'uz', 'en'], $titles);
            $summary = array_combine(['ru', 'uz', 'en'], $summaries);
            Service::firstOrCreate(['slug' => $slug], [
                'service_category_id' => $categoryIds[$categorySlug],
                'title' => $title, 'summary' => $summary,
                'seo_title' => array_map(fn (string $value) => $value.' | Smartnet', $title),
                'seo_description' => $summary,
                'price_from' => $price, 'price_currency' => 'UZS',
                'sort_order' => $order, 'is_published' => false,
            ]);
        }
    }
}
