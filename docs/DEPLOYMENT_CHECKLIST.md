# Production Deployment Checklist

## Pre-Deployment

### Environment
- [ ] `.env` production values set (not `.env.example` defaults)
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` generated and securely stored
- [ ] `APP_URL` set to production domain

### Database
- [ ] `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` configured
- [ ] Run `php artisan migrate --force` to apply all migrations
- [ ] Verify new tables: `payment_events`, `notification_templates`, `notification_logs`
- [ ] Verify new columns: `orders.locked_total`, `orders.idempotency_key`, `audit_logs.actor_role`, `audit_logs.ip_address`, `driver_locations.created_at`, `payments.checkout_id` (unique constraint)
- [ ] Database backup taken before migration

### Yoco Payments
- [ ] `YOCO_SECRET_KEY` set (production key, not test)
- [ ] `YOCO_PUBLIC_KEY` set
- [ ] `YOCO_WEBHOOK_SECRET` set
- [ ] Webhook URL registered in Yoco dashboard: `https://yourdomain.com/webhooks/yoco`

### Mail
- [ ] `MAIL_MAILER` set (smtp, ses, etc. - NOT `log`)
- [ ] `MAIL_FROM_ADDRESS` set to production email
- [ ] Test email sending works

### Queue
- [ ] `QUEUE_CONNECTION=database` (or redis for higher throughput)
- [ ] Queue worker running: `php artisan queue:work --tries=3`
- [ ] Failed jobs table exists

### Cache & Session
- [ ] `CACHE_STORE=database` (or redis)
- [ ] `SESSION_DRIVER=database`
- [ ] `SESSION_ENCRYPT=true` (recommended for production)

## Deployment Steps

1. **Backup database** before any migration
2. `composer install --no-dev --optimize-autoloader`
3. `php artisan migrate --force`
4. `php artisan config:cache`
5. `php artisan route:cache`
6. `php artisan view:cache`
7. `npm run build`
8. Restart queue workers: `php artisan queue:restart`
9. Verify health: `curl https://yourdomain.com/health`

## Post-Deployment Verification

### Health Checks
- [ ] `GET /health` returns `{"status": "healthy"}`
- [ ] Database check passes
- [ ] Queue check passes
- [ ] Cache check passes
- [ ] Storage check passes

### Functional Tests
- [ ] Homepage loads
- [ ] Login works
- [ ] Customer can create order
- [ ] Payment flow redirects to Yoco
- [ ] Webhook endpoint accepts signed requests
- [ ] Admin dashboard loads
- [ ] Driver portal loads

### Acceptance Criteria (from Recipe)
1. [ ] COD cannot dispatch unless webhook-confirmed paid
2. [ ] No duplicate orders from double taps (idempotency key)
3. [ ] Driver cannot accept/track/update unassigned orders (403)
4. [ ] Tracking only active during delivery statuses, rate limited
5. [ ] POD required for DELIVERED, no duplicate POD, linked to invoice
6. [ ] Invoice generated automatically, unique numbers (INV-YYYYMM-XXXXXX)
7. [ ] Weekly statements for account clients (`GET /customer/statement?from=&to=`)
8. [ ] Admin can detect stuck orders via Ops Board (`GET /admin/ops`)
9. [ ] Logs allow tracing one order end-to-end (request_id, order_id in logs)
10. [ ] Backups + restore confirmed

## Backup Strategy
- **Daily**: Full database backup
- **Monthly**: Test restore into staging
- **Retention**: 30 days minimum
- **Storage**: Off-site (S3, external server)

## Rollback Plan
1. If migration fails: `php artisan migrate:rollback --step=2` (rolls back the 2 new migrations)
2. If app fails: Restore from backup, redeploy previous release
3. Keep previous release artifact/tag for quick rollback

## Monitoring
- Health endpoint: `GET /health` (integrate with uptime monitor)
- Queue monitoring: Check `failed_jobs` table periodically
- Email delivery: Check `email_logs` table for failures
- Audit trail: `audit_logs` table for unusual activity
- Structured logs: Search by `request_id` or `order_id`
