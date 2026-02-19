<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['group' => 'general', 'key' => 'site_name', 'value' => 'DopiFuture', 'type' => 'string', 'is_translatable' => true, 'description' => 'Site adı'],
            ['group' => 'general', 'key' => 'site_description', 'value' => 'Admin Panel', 'type' => 'string', 'is_translatable' => true, 'description' => 'Site açıklaması'],

            // Security
            ['group' => 'security', 'key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer', 'description' => 'Maksimum giriş denemesi'],
            ['group' => 'security', 'key' => 'lockout_minutes', 'value' => '30', 'type' => 'integer', 'description' => 'Hesap kilitleme süresi (dk)'],
            ['group' => 'security', 'key' => 'recaptcha_enabled', 'value' => '0', 'type' => 'boolean', 'description' => 'reCAPTCHA aktif'],
            ['group' => 'security', 'key' => 'recaptcha_version', 'value' => 'v2', 'type' => 'string', 'description' => 'reCAPTCHA sürümü (v2/v3)'],
            ['group' => 'security', 'key' => 'recaptcha_site_key', 'value' => '', 'type' => 'string', 'description' => 'reCAPTCHA Site Key'],
            ['group' => 'security', 'key' => 'recaptcha_secret_key', 'value' => '', 'type' => 'string', 'is_encrypted' => true, 'description' => 'reCAPTCHA Secret Key'],

            // Storage
            ['group' => 'storage', 'key' => 'default_disk', 'value' => 'local', 'type' => 'string', 'description' => 'Varsayılan depolama'],
            ['group' => 'storage', 'key' => 'avatar_disk', 'value' => 'public', 'type' => 'string', 'description' => 'Avatar depolama diski'],
            ['group' => 'storage', 'key' => 'avatar_private', 'value' => '0', 'type' => 'boolean', 'description' => 'Avatar\'lar özel mi'],
            ['group' => 'storage', 'key' => 'max_upload_size', 'value' => '10240', 'type' => 'integer', 'description' => 'Maks dosya boyutu (KB)'],

            // FCM
            ['group' => 'fcm', 'key' => 'server_key', 'value' => '', 'type' => 'string', 'is_encrypted' => true, 'description' => 'FCM Server Key'],
            ['group' => 'fcm', 'key' => 'project_id', 'value' => '', 'type' => 'string', 'description' => 'Firebase Project ID'],

            // Appearance
            ['group' => 'appearance', 'key' => 'default_dark_mode', 'value' => '0', 'type' => 'boolean', 'description' => 'Varsayılan dark mode'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                $setting
            );
        }
    }
}
