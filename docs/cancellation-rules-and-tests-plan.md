# Cancellation Rules and Tests Plan

This document is a planning guide only. It describes how to add cancellation behavior, cancellation fees, and the missing unhappy-path tests without writing the implementation here.

## Goal

Add a real cancellation flow for bookings that can be tested as isolated booking-rule logic. The important cases are:

- Successful cancellation of an active future booking.
- Cancellation fee calculation.
- Rejecting cancellation for a past slot.
- Rejecting cancellation when the booking is already canceled.
- Keeping the existing slot-reuse behavior separate from the cancellation guard.

The existing test named around "slot when existing booking is canceled" proves that a canceled booking does not block a new booking for the same slot. That is useful, but it does not prove that the system rejects canceling the same booking twice.

## Suggested Design

Add a small cancellation service or strategy, separate from the creation strategies.

Recommended shape:

- `BookingCancellationStrategy` or `BookingCancellationService`
- Inject a booking repository.
- Inject a clock or pass the current time into the method.
- Keep DB writes inside the repository.
- Keep the cancellation rules inside the strategy/service.
- Keep notification, event, or queue behavior outside the isolated unit tests.

The cancellation rule object should not call Eloquent statics directly. It should ask the repository for the booking and persist the result through the repository. This keeps the unit tests free from DB and queue dependencies.

## Repository Responsibilities

The repository double used in tests should be able to answer these questions:

- Find a booking by id.
- Return the booking with its slot date and start time.
- Persist a canceled status.
- Persist or expose the calculated cancellation fee, if the fee is stored.

Production repository responsibilities can still use Eloquent internally, but the cancellation service should only depend on an interface.

## Cancellation Fee Rule

Decide the business rule before writing tests. A simple first rule could be:

- No fee when cancellation is more than 24 hours before the slot starts.
- Fixed fee when cancellation is within 24 hours of the slot start.
- Full or higher fee after the slot has started, although this may be rejected as a past-slot cancellation instead.

Pick exact numbers before implementation. For example:

- Fee amount: 50.
- Fee currency: whatever the booking system already uses, or no currency if money is not modeled yet.
- Fee storage: either a `cancellation_fee` field on `bookings`, a separate value object returned by the service, or a separate payment/fee table later.

For a first implementation, prefer returning the fee from the cancellation service and storing it on the booking only if the product needs to show it later.

## Required Production Changes

Suggested order:

1. Add a cancellation method to the booking rule layer.
2. Add repository methods needed by that rule.
3. Add fee calculation as a small dedicated rule or value object if the calculation has more than one branch.
4. Add persistence for the canceled status.
5. Add persistence for the cancellation fee only if the product requirement says the fee must be saved.
6. Add controller/API wiring after the rule-level unit tests pass.

Do not start with the controller. Start with the rule because the required missing cases are business rules.

## Required Unit Tests

Write these as Pest unit tests with repository doubles, not factories and not `RefreshDatabase`.

### Cancellation Fee Unit

Purpose: prove the fee rule works without touching bookings, slots, DB, or queue.

Cases to cover:

- Future cancellation outside the fee window returns zero fee.
- Future cancellation inside the fee window returns the configured fee.
- Boundary time is explicit, such as exactly 24 hours before the slot.

Keep this test focused on the fee rule only. It should not cancel a booking.

### Successful Cancellation

Purpose: prove a cancelable booking changes to canceled and returns the expected result.

Setup with repository double:

- Booking status is `pending` or `confirmed`.
- Slot date/time is in the future.
- Current time is before the slot.

Expected behavior:

- Repository receives a cancellation update.
- Returned booking/result has status `canceled`.
- Fee is included if fee calculation belongs to this flow.

### Past-Slot Unhappy Path

Purpose: prove a booking cannot be canceled after the slot has started or passed.

Setup with repository double:

- Booking status is `pending` or `confirmed`.
- Slot date/time is before the current time.

Expected behavior:

- Throws a clear exception, such as `Cannot cancel a past booking.`
- Repository does not persist any cancellation update.
- Fee is not charged unless the product explicitly says late cancellation should still charge.

### Already-Canceled Unhappy Path

Purpose: prove double cancellation is rejected.

Setup with repository double:

- Booking status is already `canceled`.
- Slot can be future or past, but future is cleaner because it isolates the status rule.

Expected behavior:

- Throws a clear exception, such as `Booking is already canceled.`
- Repository does not persist any cancellation update.
- Fee is not recalculated or charged again.

This is different from the existing slot-reuse test. Slot reuse checks whether a new booking may use a slot after an old booking was canceled. Already-canceled guard checks whether the same booking can be canceled twice.

## Suggested Test File Split

Use separate files so each concern stays easy to read:

- `tests/Unit/CancellationFeeTest.php`
- `tests/Unit/BookingCancellationStrategyTest.php`

The fee test should not know about repositories. The cancellation strategy test should use an in-memory repository double.

## Suggested Implementation Order

1. Write the cancellation-fee tests first.
2. Implement the fee rule until those tests pass.
3. Write the cancellation strategy tests with an in-memory repository double.
4. Implement the cancellation strategy using injected repository dependencies.
5. Add DB-backed repository methods after the isolated tests pass.
6. Add feature/API tests only after the business rules are covered.

## Acceptance Checklist

- Cancellation fee has isolated unit coverage.
- Past-slot cancellation is rejected.
- Already-canceled cancellation is rejected.
- Existing canceled bookings still do not block new slot bookings.
- Cancellation tests do not use `RefreshDatabase`.
- Cancellation tests do not use model factories.
- Cancellation tests do not dispatch events or queues.
- Repository doubles are injected directly into the cancellation rule.
