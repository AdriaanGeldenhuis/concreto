# Concreto Feature Matrix

## Core Features

| Feature | Status | Backend File(s) | Endpoint(s) / UI |
|---------|--------|-----------------|-------------------|
| **Products** | | | |
| Product catalog (public) | ✅ | `PublicController`, `Product` model | `GET /products`, `GET /products/{product}` |
| Product CRUD (admin) | ✅ | `Admin\ProductController` | `GET/POST/PUT/DELETE /admin/products` |
| Categories | ✅ | `Admin\CategoryController`, `Category` model | `GET/POST/PUT/DELETE /admin/categories` |
| Product images | ✅ | `Admin\ProductController` (store/update) | Upload via product form |
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
| Reorder | ✅ | `Customer\OrderController@reorder` | `GET /customer/orders/{order}/reorder` |
| Order dispute | ✅ | `Customer\OrderController@dispute` | `POST /customer/orders/{order}/dispute` |
| **Customers** | | | |
| Customer registration | ✅ | `RegisterController` | `POST /register` |
| COD customers | ✅ | `Customer` model (`type=COD`) | Default type on registration |
| Account customers | ✅ | `Customer` model (`type=ACCOUNT`) | Set via admin |
| Pay-before-dispatch flag | ✅ | `Customer.pay_before_dispatch` | Configurable per customer |
| Credit limit | ✅ | `Customer.credit_limit` (field exists) | ❌ NOT ENFORCED |
| Customer management (admin) | ✅ | `Admin\CustomerController` | `GET /admin/customers`, `PUT /admin/customers/{id}` |
| Customer addresses | ✅ | `Customer\AddressController` | `GET/POST/DELETE /customer/addresses` |
| **Drivers** | | | |
| Driver assignment (admin) | ✅ | `Admin\OrderController@assignDriver` | `POST /admin/orders/{order}/assign-driver` |
| Driver job list | ✅ | `Driver\JobController@index` | `GET /driver/jobs` |
| Driver accept job | ✅ | `Driver\JobController@accept` | `POST /driver/jobs/{order}/accept` |
| Driver status updates | ✅ | `Driver\JobController` (loaded/transit/arrived) | `POST /driver/jobs/{order}/{action}` |
| **Tracking** | | | |
| GPS location updates | ✅ | `Driver\JobController@updateLocation` | `POST /driver/jobs/{order}/location` |
| Location data model | ✅ | `DriverLocation` model | lat, lng, speed, heading, recorded_at |
| Location cleanup (30 days) | ✅ | `routes/console.php` scheduled task | Daily automatic cleanup |
| Rate limiting on tracking | ❌ | Not implemented | — |
| Tracking only when EN_ROUTE | ❌ | Not enforced | — |
| Client tracking view | ❌ | Not implemented | — |
| Heartbeat mechanism | ❌ | Not implemented | — |
| **POD (Proof of Delivery)** | | | |
| Signature capture | ✅ | `Driver\JobController@storeSignature` | `POST /driver/jobs/{order}/signature` |
| Signature storage (PNG) | ✅ | Stored as file via `Storage::disk('public')` | — |
| Optional delivery photo | ❌ | Field exists in model, not in controller | — |
| GPS coordinates on delivery | ✅ | `ProofOfDelivery` model (gps_lat, gps_lng) | — |
| **Invoices** | | | |
| Invoice generation (PDF) | ✅ | `InvoiceService@generate` | DomPDF via Blade template |
| Invoice numbering | ✅ | `Invoice::generateInvoiceNumber()` | Format: `INV-YYYYMM-XXXX` |
| Invoice download (customer) | ✅ | `Customer\InvoiceController@download` | `GET /customer/invoices/{invoice}/download` |
| Invoice email | ✅ | `Driver\JobController@storeSignature` triggers | Sent on delivery |
| Resend invoice (admin) | ✅ | `Admin\OrderController@resendInvoice` | `POST /admin/orders/{order}/resend-invoice` |
| **Statements** | | | |
| Weekly statements | ❌ | Not implemented | — |
| Statement PDF generation | ❌ | Not implemented | — |
| **Payments (Yoco)** | | | |
| Yoco checkout creation | ✅ | `YocoService@createCheckout` | Via customer order pay flow |
| Webhook processing | ✅ | `YocoWebhookController` | `POST /webhooks/yoco` |
| Webhook signature verification | ✅ | `YocoService@verifyWebhookSignature` | HMAC-SHA256 |
| Payment status tracking | ✅ | `Payment` model | pending/completed/failed/refunded |
| Webhook idempotency | ❌ | Not implemented | — |
| Payment amount verification | ❌ | Not checked against order total | — |
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
| Ops view (stuck orders) | ❌ | Not implemented | — |
| Force status with reason | ❌ | Not implemented | — |
| **Notifications** | | | |
| Order placed email | ✅ | `emails/order-placed.blade.php` | On order creation |
| Invoice email | ✅ | `emails/invoice.blade.php` | On delivery/invoice generation |
| Automated lifecycle emails | ❌ | Not implemented (only 2 templates) | — |
| Notification templates table | ❌ | Not implemented | — |
| Notification log | ❌ | Not implemented (email_log exists) | — |
| **Security** | | | |
| Role-based middleware | ✅ | `RoleMiddleware` | admin,staff / driver / customer |
| Ownership checks (customer) | ✅ | Controllers check customer_id | — |
| Ownership checks (driver) | ✅ | Controllers check driver_id | — |
| Rate limiting (login) | ✅ | `LoginController` (5 per 300s) | — |
| Rate limiting (other endpoints) | ❌ | Not implemented | — |
| CORS configuration | ❌ | Default Laravel config | — |
| File upload validation | ❌ | Minimal (product images only) | — |
| **Observability** | | | |
| Audit logging | ✅ | `AuditLog` model | actor, action, entity, meta |
| Email logging | ✅ | `EmailLog` model | to, subject, status, error |
| Structured request logging | ❌ | Not implemented | — |
| Health endpoint | ❌ | Not implemented | — |
| **Testing** | | | |
| Feature tests | ❌ | Only skeleton `ExampleTest` | — |
| Unit tests | ❌ | Only skeleton `ExampleTest` | — |

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
| Driver: signature | ✅ | `DriverApiController` | `POST /api/driver/orders/{order}/signature` |
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
| orders | ✅ | customer_id → customers, delivery_address_id → addresses, driver_id → users | order_number | ✅ |
| order_items | ✅ | order_id → orders, product_id → products | — | ✅ |
| payments | ✅ | order_id → orders, customer_id → customers | — | ✅ |
| invoices | ✅ | order_id → orders | invoice_no | ✅ |
| quotes | ✅ | customer_id → customers | — | ✅ |
| quote_items | ✅ | quote_id → quotes, product_id → products | — | ✅ |
| driver_locations | ✅ | — (no FK constraints) | — | created_at only |
| proof_of_deliveries | ✅ | order_id → orders | — | ✅ |
| settings | ✅ | — | key | ✅ |
| audit_logs | ✅ | actor_user_id → users | — | ✅ |
| email_logs | ✅ | — | — | ✅ |
| delivery_areas | ✅ | — | — | ✅ |
| contact_messages | ✅ | — | — | ✅ |
