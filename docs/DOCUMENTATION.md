# SIGEH-PHP Documentation

SIGEH-PHP is a Laravel hotel administration system covering rooms, guests, bookings, reservations, billing, payments, services, iCalendar, reporting, users, roles, permissions, settings, and audit trails.

## Phase 7 Modules

### Reporting

Reports live under `/backend/reports` and support date range, room type, and status filters.

- Operational: occupancy rate, available rooms, active reservations.
- Financial: daily revenue, monthly revenue, payment methods, outstanding balances.
- Statistical: most frequent guests, peak seasons, cancellation trends.

Each report exports to PDF and Excel.

### Audit

Audit logs are stored in `audit_logs` and available at `/backend/audit-logs` for authorized users. The system tracks login, logout, CRUD operations, reservation changes, invoice changes, and payment changes.

### Security

- Laravel web CSRF protection is active for mutating web routes.
- Blade escaped output and request sanitization reduce XSS exposure.
- Sessions are encrypted by default, HTTP-only, and strict same-site.
- Backend routes use authorization middleware mapped to permissions.
- Form requests and controller validation enforce input rules.

## Database Schema

The database schema is versioned in `database/migrations`. Run:

```bash
php artisan migrate
```

Seeder data is available through:

```bash
php artisan db:seed
```

Core tables include users, roles, permissions, floors, room types, rooms, guests, bookings, reservations, invoices, payments, services, reservation services, settings, calendar events, and audit logs.
