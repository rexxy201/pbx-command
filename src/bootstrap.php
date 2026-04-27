<?php
/**
 * Bootstrap: seed default admin user if dashboard_users table is empty.
 * Runs on every request but is a no-op once any user exists.
 */
function bootstrap_seed(): void {
    try {
        $count = Database::row('SELECT COUNT(*) AS cnt FROM dashboard_users');
        if ((int)($count['cnt'] ?? 0) > 0) return; // already seeded

        // Table is empty — create the default admin account
        $hash = password_hash('Admin123!', PASSWORD_DEFAULT);
        Database::execute(
            "INSERT INTO dashboard_users (name, email, role, status, password_hash)
             VALUES ('Admin', 'admin@pbx.local', 'admin', 'active', ?)",
            [$hash]
        );
    } catch (Throwable $e) {
        // Table may not exist yet — silently skip
    }
}

bootstrap_seed();
