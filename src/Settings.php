<?php
/**
 * System-wide settings backed by the `system_settings` table.
 * Provides get/set per key and bulk read by group.
 */
class Settings {

    private static array $cache = [];

    public static function get(string $key, string $default = ''): string {
        if (isset(self::$cache[$key])) return self::$cache[$key];
        $row = Database::row('SELECT setting_val FROM system_settings WHERE setting_key = ?', [$key]);
        $val = $row ? ($row['setting_val'] ?? '') : $default;
        self::$cache[$key] = $val;
        return $val;
    }

    public static function set(string $key, string $grp, ?string $value): void {
        self::$cache[$key] = (string)$value;
        if (DB_DRIVER === 'pgsql') {
            Database::execute(
                'INSERT INTO system_settings (setting_key, setting_val, grp, updated_at)
                 VALUES (?, ?, ?, NOW())
                 ON CONFLICT (setting_key) DO UPDATE
                 SET setting_val = EXCLUDED.setting_val, grp = EXCLUDED.grp, updated_at = NOW()',
                [$key, $value, $grp]
            );
        } else {
            Database::execute(
                'INSERT INTO system_settings (setting_key, setting_val, grp, updated_at)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE setting_val = VALUES(setting_val), grp = VALUES(grp), updated_at = NOW()',
                [$key, $value, $grp]
            );
        }
    }

    public static function group(string $grp): array {
        $rows = Database::query('SELECT setting_key, setting_val FROM system_settings WHERE grp = ?', [$grp]);
        $out = [];
        foreach ($rows as $r) {
            $k = $r['setting_key'];
            $out[$k] = $r['setting_val'] ?? '';
            self::$cache[$k] = $out[$k];
        }
        return $out;
    }

    public static function setGroup(string $grp, array $kv): void {
        foreach ($kv as $key => $val) {
            self::set($key, $grp, $val);
        }
    }

    /** Retrieve the WhatsApp Cloud API credentials as an array. */
    public static function whatsapp(): array {
        return [
            'phone_number_id' => self::get('whatsapp.phone_number_id'),
            'access_token'    => self::get('whatsapp.access_token'),
            'verify_token'    => self::get('whatsapp.verify_token'),
            'api_version'     => self::get('whatsapp.api_version', 'v19.0'),
        ];
    }

    public static function isWhatsappConfigured(): bool {
        return self::get('whatsapp.phone_number_id') !== ''
            && self::get('whatsapp.access_token') !== '';
    }
}
