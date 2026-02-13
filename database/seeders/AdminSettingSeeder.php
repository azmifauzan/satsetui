<?php

namespace Database\Seeders;

use App\Models\AdminSetting;
use Illuminate\Database\Seeder;

class AdminSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Billing Settings
            [
                'key' => 'billing.error_margin',
                'value' => '10',
                'type' => AdminSetting::TYPE_INTEGER,
                'description' => 'Error margin percentage for credit calculation',
                'group' => AdminSetting::GROUP_BILLING,
                'is_public' => false,
            ],
            [
                'key' => 'billing.profit_margin',
                'value' => '5',
                'type' => AdminSetting::TYPE_INTEGER,
                'description' => 'Profit margin percentage for credit calculation',
                'group' => AdminSetting::GROUP_BILLING,
                'is_public' => false,
            ],
            [
                'key' => 'billing.base_credits',
                'value' => '50',
                'type' => AdminSetting::TYPE_INTEGER,
                'description' => 'Base credits for template generation',
                'group' => AdminSetting::GROUP_BILLING,
                'is_public' => true,
            ],
            [
                'key' => 'billing.extra_page_multiplier',
                'value' => '1.5',
                'type' => AdminSetting::TYPE_FLOAT,
                'description' => 'Multiplier for each extra page',
                'group' => AdminSetting::GROUP_BILLING,
                'is_public' => false,
            ],
            [
                'key' => 'billing.extra_component_multiplier',
                'value' => '1.2',
                'type' => AdminSetting::TYPE_FLOAT,
                'description' => 'Multiplier for each extra component',
                'group' => AdminSetting::GROUP_BILLING,
                'is_public' => false,
            ],

            // Generation Settings
            [
                'key' => 'generation.max_concurrent',
                'value' => '3',
                'type' => AdminSetting::TYPE_INTEGER,
                'description' => 'Maximum concurrent generation jobs per user',
                'group' => AdminSetting::GROUP_GENERATION,
                'is_public' => true,
            ],
            [
                'key' => 'generation.max_pages',
                'value' => '20',
                'type' => AdminSetting::TYPE_INTEGER,
                'description' => 'Maximum pages per template',
                'group' => AdminSetting::GROUP_GENERATION,
                'is_public' => true,
            ],
            [
                'key' => 'generation.max_components',
                'value' => '50',
                'type' => AdminSetting::TYPE_INTEGER,
                'description' => 'Maximum components per template',
                'group' => AdminSetting::GROUP_GENERATION,
                'is_public' => true,
            ],
            [
                'key' => 'generation.timeout',
                'value' => '300',
                'type' => AdminSetting::TYPE_INTEGER,
                'description' => 'Generation timeout in seconds',
                'group' => AdminSetting::GROUP_GENERATION,
                'is_public' => false,
            ],

            // Email SMTP Settings
            [
                'key' => 'email.smtp_host',
                'value' => '',
                'type' => AdminSetting::TYPE_STRING,
                'description' => 'SMTP server host (e.g., smtp.gmail.com)',
                'group' => 'email',
                'is_public' => false,
            ],
            [
                'key' => 'email.smtp_port',
                'value' => '587',
                'type' => AdminSetting::TYPE_INTEGER,
                'description' => 'SMTP server port (587 for TLS, 465 for SSL)',
                'group' => 'email',
                'is_public' => false,
            ],
            [
                'key' => 'email.smtp_username',
                'value' => '',
                'type' => AdminSetting::TYPE_STRING,
                'description' => 'SMTP username (usually your email)',
                'group' => 'email',
                'is_public' => false,
            ],
            [
                'key' => 'email.smtp_password',
                'value' => '',
                'type' => AdminSetting::TYPE_STRING,
                'description' => 'SMTP password or app-specific password',
                'group' => 'email',
                'is_public' => false,
            ],
            [
                'key' => 'email.smtp_encryption',
                'value' => 'tls',
                'type' => AdminSetting::TYPE_STRING,
                'description' => 'SMTP encryption type (tls or ssl)',
                'group' => 'email',
                'is_public' => false,
            ],
            [
                'key' => 'email.from_address',
                'value' => 'noreply@satsetui.com',
                'type' => AdminSetting::TYPE_STRING,
                'description' => 'Email from address',
                'group' => 'email',
                'is_public' => false,
            ],
            [
                'key' => 'email.from_name',
                'value' => 'SatSetUI',
                'type' => AdminSetting::TYPE_STRING,
                'description' => 'Email from name',
                'group' => 'email',
                'is_public' => false,
            ],

            // Telegram Notification Settings
            [
                'key' => 'notification.telegram_enabled',
                'value' => 'false',
                'type' => AdminSetting::TYPE_BOOLEAN,
                'description' => 'Enable Telegram notifications for new user registrations',
                'group' => 'notification',
                'is_public' => false,
            ],
            [
                'key' => 'notification.telegram_bot_token',
                'value' => '',
                'type' => AdminSetting::TYPE_STRING,
                'description' => 'Telegram Bot Token (get from @BotFather)',
                'group' => 'notification',
                'is_public' => false,
            ],
            [
                'key' => 'notification.telegram_chat_id',
                'value' => '',
                'type' => AdminSetting::TYPE_STRING,
                'description' => 'Telegram Chat ID (admin chat to receive notifications)',
                'group' => 'notification',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            AdminSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
