# Route Documentation

This project currently exposes web backend routes rather than a separate public API.

## Reporting

- `GET /backend/reports`
- `GET /backend/reports/operational`
- `GET /backend/reports/financial`
- `GET /backend/reports/statistical`
- `GET /backend/reports/{type}/pdf`
- `GET /backend/reports/{type}/excel`

Query filters:

- `date_from`: `YYYY-MM-DD`
- `date_to`: `YYYY-MM-DD`
- `room_type_id`: room type id
- `status`: reservation, booking, invoice, or payment status depending on report

## Audit

- `GET /backend/audit-logs`
- `GET /backend/audit-logs/{auditLog}`

Audit filters:

- `date_from`
- `date_to`
- `module`
- `action`
- `user_id`

All backend routes require authentication and permission authorization.
