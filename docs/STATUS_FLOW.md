# Concreto Order Status Flow

## Order Statuses

```
DRAFT → PENDING_PAYMENT → PLACED → ASSIGNED → ACCEPTED → LOADED → IN_TRANSIT → ARRIVED → DELIVERED
                                                                                            ↓
                                                                                      CANCELLED / REFUNDED
```

## Allowed Transitions (Enforced Server-Side)

```php
const ALLOWED_TRANSITIONS = [
    'DRAFT'              => ['PENDING_PAYMENT', 'PLACED', 'CANCELLED'],
    'PENDING_PAYMENT'    => ['PLACED', 'CANCELLED'],
    'PAID'               => ['PLACED', 'CANCELLED'],
    'PLACED'             => ['ASSIGNED', 'CANCELLED'],
    'ASSIGNED'           => ['ACCEPTED', 'PLACED', 'CANCELLED'],  // PLACED = unassign
    'ACCEPTED'           => ['LOADED', 'CANCELLED'],
    'LOADED'             => ['IN_TRANSIT', 'CANCELLED'],
    'IN_TRANSIT'         => ['ARRIVED', 'CANCELLED'],
    'ARRIVED'            => ['DELIVERED', 'CANCELLED'],
    'DELIVERED'          => [],  // Terminal
    'DELIVERED_PENDING_SIGNATURE' => ['DELIVERED', 'CANCELLED'],
    'CANCELLED'          => ['REFUNDED'],
    'REFUNDED'           => [],  // Terminal
];
```

## Transition Details

| From | To | Trigger | Actor | Validation |
|------|----|---------|-------|------------|
| `DRAFT` | `PENDING_PAYMENT` | Order created (COD or pay-before-dispatch) | System (`OrderService`) | Automatic based on customer type |
| `DRAFT` | `PLACED` | Order created (account customer, no pay-before-dispatch) | System (`OrderService`) | Automatic based on customer type |
| `PENDING_PAYMENT` | `PLACED` | Yoco webhook `payment.succeeded` | System (`YocoWebhookController`) | Signature verified, amount verified, idempotent on event_id |
| `PLACED` | `ASSIGNED` | Admin assigns driver | Admin (`OrderController`) | Driver must have role=driver |
| `ASSIGNED` | `ACCEPTED` | Driver accepts job | Driver (`JobController`) | Must be assigned driver (403 otherwise) |
| `ASSIGNED` | `PLACED` | Admin unassigns driver | Admin (`OrderController`) | Via `forceStatus` with reason |
| `ACCEPTED` | `LOADED` | Driver marks loaded | Driver (`JobController`) | `lockForUpdate` + `canTransitionTo` |
| `LOADED` | `IN_TRANSIT` | Driver marks in transit | Driver (`JobController`) | `lockForUpdate` + `canTransitionTo` |
| `IN_TRANSIT` | `ARRIVED` | Driver marks arrived | Driver (`JobController`) | `lockForUpdate` + `canTransitionTo` |
| `ARRIVED` | `DELIVERED` | Driver submits POD signature | Driver (`JobController`) | Requires valid signature image, triggers invoice |
| Any non-terminal | `CANCELLED` | Admin cancels order | Admin (`OrderController`) | `canTransitionTo` validated |
| `CANCELLED` | `REFUNDED` | Admin processes refund | Admin | Via status update |

## Terminal Statuses

- `DELIVERED` - Order completed, POD captured, invoice generated and emailed
- `CANCELLED` - Order cancelled (can transition to REFUNDED)
- `REFUNDED` - Payment refunded (final)

## Admin Force Status

Admin can bypass normal transition rules using `forceStatus`:
- Requires a mandatory `reason` string
- Logs `force_status_changed` to `audit_logs` with from/to/reason
- Available at `POST /admin/orders/{order}/force-status`

## Notifications Triggered on Status Changes

| Status Change | Notification | Template |
|--------------|-------------|----------|
| Order created | Order Confirmed | `emails.order-placed` |
| PENDING_PAYMENT → PLACED | Payment Received | `emails.payment-received` |
| → LOADED | Order Loaded | `emails.order-loaded` |
| → IN_TRANSIT | Order En Route | `emails.order-en-route` |
| → DELIVERED | Order Delivered | `emails.order-delivered` |

All notifications use 3-attempt retry with exponential backoff and are logged to `notification_logs`.

## Status Descriptions

| Status | Description |
|--------|-------------|
| `DRAFT` | Order created but not yet submitted/paid |
| `PENDING_PAYMENT` | Awaiting payment (COD or pay-before-dispatch customers) |
| `PAID` | Payment confirmed (transitional - jumps to PLACED) |
| `PLACED` | Order confirmed and ready for dispatch |
| `ASSIGNED` | Driver assigned by admin |
| `ACCEPTED` | Driver accepted the delivery job |
| `LOADED` | Goods loaded onto delivery vehicle |
| `IN_TRANSIT` | Driver en route to delivery address |
| `ARRIVED` | Driver arrived at delivery location |
| `DELIVERED_PENDING_SIGNATURE` | Awaiting POD signature (fallback state) |
| `DELIVERED` | Delivery complete, POD captured, invoice generated |
| `CANCELLED` | Order cancelled |
| `REFUNDED` | Payment refunded |
