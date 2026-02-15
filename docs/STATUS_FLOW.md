# Concreto Order Status Flow

## Order Statuses

```
DRAFT → PENDING_PAYMENT → PAID/PLACED → ASSIGNED → ACCEPTED → LOADED → IN_TRANSIT → ARRIVED → DELIVERED → INVOICED
                                                                                                    ↓
                                                                                              CANCELLED / REFUNDED
```

## Allowed Transitions

| From | To | Trigger | Actor |
|------|----|---------|-------|
| `DRAFT` | `PENDING_PAYMENT` | Order created (COD or pay-before-dispatch account) | System (OrderService) |
| `DRAFT` | `PLACED` | Order created (account customer, no pay-before-dispatch) | System (OrderService) |
| `PENDING_PAYMENT` | `PLACED` | Yoco webhook payment.succeeded | System (YocoWebhookController) |
| `PLACED` | `ASSIGNED` | Admin assigns driver | Admin (OrderController) |
| `ASSIGNED` | `ACCEPTED` | Driver accepts job | Driver (JobController) |
| `ACCEPTED` | `LOADED` | Driver marks loaded | Driver (JobController) |
| `LOADED` | `IN_TRANSIT` | Driver marks in transit | Driver (JobController) |
| `IN_TRANSIT` | `ARRIVED` | Driver marks arrived | Driver (JobController) |
| `ARRIVED` | `DELIVERED` | Driver submits POD signature | Driver (JobController) |
| Any non-terminal | `CANCELLED` | Admin cancels order | Admin (OrderController) |

## Terminal Statuses

- `DELIVERED` - Order completed, invoice generated
- `CANCELLED` - Order cancelled
- `REFUNDED` - Payment refunded

## Status Descriptions

| Status | Description |
|--------|-------------|
| `DRAFT` | Order created but not yet submitted/paid |
| `PENDING_PAYMENT` | Awaiting payment (COD or pay-before-dispatch customers) |
| `PAID` | Payment confirmed (currently unused - jumps to PLACED) |
| `PLACED` | Order confirmed and ready for dispatch |
| `ASSIGNED` | Driver assigned by admin |
| `ACCEPTED` | Driver accepted the delivery job |
| `LOADED` | Goods loaded onto delivery vehicle |
| `IN_TRANSIT` | Driver en route to delivery address |
| `ARRIVED` | Driver arrived at delivery location |
| `DELIVERED_PENDING_SIGNATURE` | Awaiting POD signature (currently unused) |
| `DELIVERED` | Delivery complete, POD captured, invoice generated |
| `CANCELLED` | Order cancelled |
| `REFUNDED` | Payment refunded |

## Current Issues (Pre-Hardening)

1. **No server-side transition validation** - Any status can be set to any other status via `updateStatus()`
2. **PAID status unused** - Payment success goes directly to PLACED
3. **DELIVERED_PENDING_SIGNATURE unused** - Signature flow goes ARRIVED → DELIVERED
4. **No rollback protection** - Once a status is set, there's no undo mechanism
5. **Admin can set any status** - No validation on admin status changes (should require reason for audit)

## Hardened Transition Map (To Be Implemented)

```php
const ALLOWED_TRANSITIONS = [
    'DRAFT'              => ['PENDING_PAYMENT', 'PLACED', 'CANCELLED'],
    'PENDING_PAYMENT'    => ['PLACED', 'CANCELLED'],
    'PLACED'             => ['ASSIGNED', 'CANCELLED'],
    'ASSIGNED'           => ['ACCEPTED', 'PLACED', 'CANCELLED'],  // PLACED = unassign
    'ACCEPTED'           => ['LOADED', 'CANCELLED'],
    'LOADED'             => ['IN_TRANSIT', 'CANCELLED'],
    'IN_TRANSIT'         => ['ARRIVED', 'CANCELLED'],
    'ARRIVED'            => ['DELIVERED', 'CANCELLED'],
    'DELIVERED'          => [],  // Terminal
    'CANCELLED'          => ['REFUNDED'],
    'REFUNDED'           => [],  // Terminal
];
```
