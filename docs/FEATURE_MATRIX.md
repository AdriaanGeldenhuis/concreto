# Concreto Feature Matrix

## Core Features

| Feature | Status | Backend File(s) | Endpoint(s) / UI |
|---------|--------|-----------------|-------------------|
| **Products** | | | |
| Product catalog (public) | ✅ | `PublicController`, `Product` model | `GET /products`, `GET /products/{product}` |
| Product CRUD (admin) | ✅ | `Admin\ProductController` | `GET/POST/PUT/DELETE /admin/products` |
| Categories | ✅ | `Admin\CategoryController`, `Category` model | `GET/POST/PUT/DELETE /admin/categories` |
| Product images | ✅ | `Admin\ProductController` (store/update) | Upload via product form, validated `image|max:5120|mimes:jpg,jpeg,png,webp` |
| **Shopping Cart** | | | |
| Session-based cart | ✅ | `CartController` | `GET /cart`, `POST /cart/add,update,remove` |
| Cart count (AJAX) | ✅ | `CartController@count` | `GET /cart/count` (JSON) |
| Checkout flow | ✅ | `CartController@checkout,placeOrder` | `GET/POST /checkout` |
| **Orders** | | | |
| Order creation (customer portal) | ✅ | `Customer\OrderController`, `OrderService` | `GET/POST /customer/orders` |
| Order creation (cart checkout) | ✅ | `CartController@placeOrder` | `POST /checkout` |
| Order viewing (customer) | ✅ | `Customer\OrderController@show` | `GET /customer/orders/{order}` |
| Order management (admin) | ✅ | `Admin\OrderController` | `GET /admin/orders`, `GET /admin/orders/{order}` |
| Order status updates (admin) | ✅ | `Admin\OrderController@updateStatus` | `POST /admin/orders/{order}/status` |
| Order cancellation (admin) | ✅ | `Admin\OrderController@cancel` | `POST /admin/orders/{order}/cancel` |
| Order number generation | ✅ | `Order::generateOrderNumber()` | Format: `CON-YYYYMMDD-XXXX` |
| Server-side total calculation | ✅ | `Order::calculateTotals()` | subtotal + VAT(15%) + delivery_fee |
| Locked total (server snapshot) | ✅ | `Order.locked_total` | Set on order creation, prevents tampering |
| Idempotency key | ✅ | `Order.idempotency_key` (unique) | Prevents duplicate orders from double-taps |
| Reorder | ✅ | `Customer\OrderController@reorder` | `GET /customer/orders/{order}/reorder` |
| Order dispute | ✅ | `Customer\OrderController@dispute` | `POST /customer/orders/{order}/dispute` |
| **Customers** | | | |
| Customer registration | ✅ | `RegisterController` | `POST /register` |
| COD customers | ✅ | `Customer` model (`type=COD`) | Default type on registration |
| Account customers | ✅ | `Customer` model (`type=ACCOUNT`) | Set via admin |
| Pay-before-dispatch flag | ✅ | `Customer.pay_before_dispatch` | Configurable per customer |
| Credit limit | ✅ | `Customer.credit_limit` (field exists) | Field available, enforcement at admin discretion |
| Customer management (admin) | ✅ | `Admin\CustomerController` | `GET /admin/customers`, `PUT /admin/customers/{id}` |
| Customer addresses | ✅ | `Customer\AddressController` | `GET/POST/DELETE /customer/addresses` |
| **Drivers** | | | |
| Driver assignment (admin) | ✅ | `Admin\OrderController@assignDriver`, `OrderService` | `POST /admin/orders/{order}/assign-driver` |
| Driver job list | ✅ | `Driver\JobController@index` | `GET /driver/jobs` |
| Driver accept job | ✅ | `Driver\JobController@accept` | `POST /driver/jobs/{order}/accept` |
| Driver status updates | ✅ | `Driver\JobController` (loaded/transit/arrived) | `POST /driver/jobs/{order}/{action}` |
| Driver authorization | ✅ | `authorizeDriver()` in all driver controllers | Returns 403 if driver_id != auth user |
| **Tracking** | | | |
| GPS location updates | ✅ | `Driver\JobController@updateLocation`, `DriverApiController` | `POST /driver/jobs/{order}/location` |
| Location data model | ✅ | `DriverLocation` model | lat, lng, speed, heading, accuracy, recorded_at |
| Location cleanup (30 days) | ✅ | `routes/console.php` scheduled task | Daily automatic cleanup |
| Rate limiting on tracking | ✅ | Both driver controllers | Moving: 1/10s, Stationary: 1/60s + `throttle:tracking` (30/min) |
| Status-based tracking | ✅ | Both driver controllers | Only active for ACCEPTED, LOADED, IN_TRANSIT, ARRIVED |
| **POD (Proof of Delivery)** | | | |
| Signature capture | ✅ | `Driver\JobController@storeSignature`, `DriverApiController` | `POST /driver/jobs/{order}/signature` |
| Signature storage (PNG) | ✅ | Stored as file via `Storage::disk('local')` | With MIME type validation |
| Optional delivery photo | ✅ | Both driver controllers | `photo` field: `image|max:10240|mimes:jpg,jpeg,png` |
| GPS coordinates on delivery | ✅ | `ProofOfDelivery` model (gps_lat, gps_lng) | Stored on POD creation |
| Signature MIME validation | ✅ | `finfo` checks in both controllers | Rejects non-image data disguised as signatures |
| POD on invoice | ✅ | `pdf/invoice.blade.php` | Shows signer name and signed_at on invoice PDF |
| **Invoices** | | | |
| Invoice generation (PDF) | ✅ | `InvoiceService@generate` | DomPDF via Blade template |
| Invoice numbering | ✅ | `Invoice::generateInvoiceNumber()` | Format: `INV-YYYYMM-XXXXXX`, atomic with lockForUpdate |
| Invoice download (customer) | ✅ | `Customer\InvoiceController@download` | `GET /customer/invoices/{invoice}/download` |
| Invoice email on delivery | ✅ | `Driver\JobController@storeSignature` | Sent with PDF attachment, 3 retries with backoff |
| Resend invoice (admin) | ✅ | `Admin\OrderController@resendInvoice` | `POST /admin/orders/{order}/resend-invoice` |
| **Statements** | | | |
| Statement PDF generation | ✅ | `InvoiceService@generateStatement` | Generates PDF for date range |
| Customer statement download | ✅ | `Customer\InvoiceController@statement` | `GET /customer/statement?from=&to=` |
| **Payments (Yoco)** | | | |
| Yoco checkout creation | ✅ | `YocoService@createCheckout` | Via customer order pay flow |
| Webhook processing | ✅ | `YocoWebhookController` | `POST /webhooks/yoco` |
| Webhook signature verification | ✅ | `YocoService@verifyWebhookSignature` | HMAC-SHA256 |
| Payment status tracking | ✅ | `Payment` model | pending/completed/failed/refunded |
| Webhook idempotency | ✅ | `PaymentEvent` table + event_id check | Duplicate events return `already_processed` |
| Payment amount verification | ✅ | `YocoWebhookController` | Verifies webhook amount matches order total |
| Raw event storage | ✅ | `PaymentEvent` model | All webhook events stored for audit |
| COD dispatch block | ✅ | `OrderService` + webhook flow | COD → PENDING_PAYMENT until webhook confirms |
| **Quotes** | | | |
| Quote creation (admin) | ✅ | `Admin\QuoteController` | `GET/POST /admin/quotes` |
| Quote sending | ✅ | `Admin\QuoteController@send` | `POST /admin/quotes/{quote}/send` |
| Quote viewing (customer) | ✅ | `Customer\QuoteController` | `GET /customer/quotes` |
| Quote approval (customer) | ✅ | `Customer\QuoteController@approve` | `POST /customer/quotes/{quote}/approve` |
| **Admin Dashboard** | | | |
| Dashboard overview | ✅ | `Admin\DashboardController` | `GET /admin/` |
| User management | ✅ | `Admin\UserController` | CRUD at `/admin/users` |
| Site settings | ✅ | `Admin\SettingsController` | `GET/POST /admin/settings` |
| Contact messages | ✅ | `Admin\MessageController` | `GET/POST/DELETE /admin/messages` |
| Audit logs viewer | ✅ | `Admin\AuditLogController` | `GET /admin/audit-logs` |
| Ops view (stuck orders) | ✅ | `Admin\OpsController` | `GET /admin/ops` (unassigned, stale, no tracking, not invoiced) |
| Force status with reason | ✅ | `Admin\OrderController@forceStatus` | `POST /admin/orders/{order}/force-status` (requires reason, audit logged) |
| **Notifications** | | | |
| Order confirmed email | ✅ | `NotificationService@orderConfirmed` | Triggered on order creation via `OrderService` |
| Payment received email | ✅ | `NotificationService@paymentReceived` | Triggered on webhook payment success |
| Order loaded email | ✅ | `NotificationService@orderLoaded` | Triggered when driver marks loaded |
| Order en route email | ✅ | `NotificationService@orderEnRoute` | Triggered when driver marks in transit |
| Order delivered email | ✅ | `NotificationService@orderDelivered` | Triggered on POD capture |
| Email retry mechanism | ✅ | `NotificationService` | 3 attempts with exponential backoff (1s, 2s) |
| Notification logging | ✅ | `NotificationLog` model | channel, recipient, status, attempts, error_message |
| Notification templates table | ✅ | `notification_templates` table | Available for future template customization |
| **Security** | | | |
| Role-based middleware | ✅ | `RoleMiddleware` | admin,staff / driver / customer |
| Ownership checks (customer) | ✅ | All customer controllers | customer_id = auth.customer_id (403 on mismatch) |
| Ownership checks (driver) | ✅ | All driver controllers | driver_id = auth.user_id (403 on mismatch) |
| Rate limiting (login) | ✅ | `throttle:login` middleware | 5 per minute per IP |
| Rate limiting (payment) | ✅ | `throttle:payment` middleware | 10 per minute per user |
| Rate limiting (webhook) | ✅ | `throttle:webhook` middleware | 120 per minute per IP |
| Rate limiting (tracking) | ✅ | `throttle:tracking` middleware | 30 per minute per user |
| Rate limiting (API global) | ✅ | `throttleApi('60,1')` | 60 requests per minute |
| CORS configuration | ✅ | `config/cors.php` | Restricted to APP_URL origin |
| CSRF protection | ✅ | Laravel default | Webhooks excluded |
| File upload validation | ✅ | Products, signatures, photos | MIME type + size validation |
| Signature binary validation | ✅ | `finfo` MIME check | Rejects non-image data |
| Secrets in environment | ✅ | `.env` / `config/yoco.php` | YOCO keys, DB creds, APP_KEY in .env only |
| **Observability** | | | |
| Audit logging | ✅ | `AuditLog` model | actor_user_id, actor_role, action, entity, meta, ip_address |
| Email logging | ✅ | `EmailLog` model | to, subject, status, error |
| Structured request logging | ✅ | `RequestLogger` middleware | request_id, method, url, status, ip, user_id, role, duration_ms, order_id |
| Health endpoint | ✅ | `HealthController` | `GET /health` - database, queue, cache, storage checks |
| **Testing** | | | |
| Order idempotency tests | ✅ | `OrderIdempotencyTest` | 3 tests: duplicate key, different keys, server-side totals |
| Status transition tests | ✅ | `OrderStatusTransitionTest` | 8 tests: valid/invalid transitions, lifecycle, terminal, force, cancel |
| Role permission tests | ✅ | `RolePermissionTest` | 8 tests: cross-role access, ownership, unauthenticated |
| Webhook idempotency tests | ✅ | `WebhookIdempotencyTest` | 3 tests: duplicate events, invalid signature, payment status |
| Health check tests | ✅ | `HealthCheckTest` | 1 test: endpoint returns healthy |
| Invoice number tests | ✅ | `InvoiceNumberTest` | 3 tests: uniqueness (50 invoices), format, duplicate insert fails |

## API Endpoints (Sanctum Token Auth)

| Feature | Status | Controller | Endpoint |
|---------|--------|------------|----------|
| Customer: list orders | ✅ | `CustomerApiController` | `GET /api/orders` |
| Customer: create order | ✅ | `CustomerApiController` | `POST /api/orders` |
| Customer: show order | ✅ | `CustomerApiController` | `GET /api/orders/{order}` |
| Customer: pay (Yoco) | ✅ | `CustomerApiController` | `POST /api/orders/{order}/pay/yoco` |
| Customer: list invoices | ✅ | `CustomerApiController` | `GET /api/invoices` |
| Customer: download invoice | ✅ | `CustomerApiController` | `GET /api/invoices/{invoice}/download` |
| Driver: list orders | ✅ | `DriverApiController` | `GET /api/driver/orders` |
| Driver: accept order | ✅ | `DriverApiController` | `POST /api/driver/orders/{order}/accept` |
| Driver: loaded | ✅ | `DriverApiController` | `POST /api/driver/orders/{order}/loaded` |
| Driver: transit | ✅ | `DriverApiController` | `POST /api/driver/orders/{order}/transit` |
| Driver: arrived | ✅ | `DriverApiController` | `POST /api/driver/orders/{order}/arrived` |
| Driver: location | ✅ | `DriverApiController` | `POST /api/driver/orders/{order}/location` |
| Driver: signature + photo | ✅ | `DriverApiController` | `POST /api/driver/orders/{order}/signature` |
| Admin: list orders | ✅ | `AdminApiController` | `GET /api/admin/orders` |
| Admin: assign driver | ✅ | `AdminApiController` | `POST /api/admin/orders/{order}/assign-driver` |
| Admin: get settings | ✅ | `AdminApiController` | `GET /api/admin/settings` |
| Admin: update settings | ✅ | `AdminApiController` | `POST /api/admin/settings` |

## Database Tables

| Table | Exists | Foreign Keys | Unique Constraints | Timestamps |
|-------|--------|-------------|-------------------|------------|
| users | ✅ | — | email | ✅ |
| customers | ✅ | user_id → users | user_id | ✅ |
| addresses | ✅ | customer_id → customers | — | ✅ |
| categories | ✅ | — | slug | ✅ |
| products | ✅ | category_id → categories | slug | ✅ |
| orders | ✅ | customer_id → customers, delivery_address_id → addresses, driver_id → users | order_number, idempotency_key | ✅ |
| order_items | ✅ | order_id → orders, product_id → products | — | ✅ |
| payments | ✅ | order_id → orders, customer_id → customers | checkout_id | ✅ |
| payment_events | ✅ | payment_id → payments | event_id | ✅ |
| invoices | ✅ | order_id → orders | invoice_no | ✅ |
| quotes | ✅ | customer_id → customers | — | ✅ |
| quote_items | ✅ | quote_id → quotes, product_id → products | — | ✅ |
| driver_locations | ✅ | driver_id → users, order_id → orders | — | created_at |
| proof_of_deliveries | ✅ | order_id → orders | — | ✅ |
| settings | ✅ | — | key | ✅ |
| audit_logs | ✅ | actor_user_id → users | — (indexed: entity+entity_id, actor_user_id, created_at) | ✅ |
| email_logs | ✅ | — | — | ✅ |
| notification_logs | ✅ | — | — (indexed: related_type+related_id, status) | ✅ |
| notification_templates | ✅ | — | key | ✅ |
| delivery_areas | ✅ | — | — | ✅ |
| contact_messages | ✅ | — | — | ✅ |
