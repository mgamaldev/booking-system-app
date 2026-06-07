# Booking System Bottleneck Analysis

This review focused on N+1 query risk, missing indexes, synchronous blocking work, uncached hot reads, and transaction scopes that can hold locks longer than necessary.

## 1. Recurring booking creation does O(n) database work inside one transaction

### (a) The problem

Recurring booking creation loops over every generated recurrence date inside a single transaction:

- `app/Strategies/BookingStrategies/RecurringBookingStrategy.php:23` opens the transaction.
- `app/Strategies/BookingStrategies/RecurringBookingStrategy.php:40` loops all recurrence dates.
- `app/Strategies/BookingStrategies/RecurringBookingStrategy.php:41` calls `Slot::firstOrCreate()` once per date.
- `app/Strategies/BookingStrategies/RecurringBookingStrategy.php:48` checks `Booking::where(...)->exists()` once per date.
- `app/Strategies/BookingStrategies/RecurringBookingStrategy.php:56` inserts one booking per date.

The older factory path has the same shape:

- `app/Factories/RecurringBookingFactory.php:18` opens the transaction.
- `app/Factories/RecurringBookingFactory.php:27` loops all dates.
- `app/Factories/RecurringBookingFactory.php:28` queries the slot once per date.
- `app/Factories/RecurringBookingFactory.php:30` checks booking occupancy once per date.
- `app/Factories/RecurringBookingFactory.php:38` inserts one booking per date.

This is both an N+1-style query pattern and a large transaction scope. A one-year weekly recurrence performs roughly 150 database operations inside one transaction before considering model events and timestamps.

### (b) The scale trigger

This becomes painful when users create long recurrences or when many users book the same time windows concurrently. Weekly bookings over a year, monthly bookings over multiple years, or bulk import flows will hold the transaction open while repeatedly selecting and inserting rows. On MySQL/InnoDB, concurrent requests for overlapping slots will wait longer on locks, and the work scales linearly with the number of recurrence dates.

### (c) The mitigation

Replace the per-date loop queries with set-based reads and a shorter write transaction:

- Add a unique slot index: `slots_date_start_time_end_time_unique` on `slots(date, start_time, end_time)`.
- Preload all matching slots in one query keyed by `date|start_time|end_time`.
- Bulk insert missing slots, then reload them once.
- Check all occupied booking rows in one query with `whereIn('slot_id', $slotIds)->whereIn('status', ['pending', 'confirmed'])`.
- Bulk insert bookings after validation.
- Keep only the final inserts inside `DB::transaction()`.

The booking occupancy query should use the implemented composite index `bookings_slot_status_type_idx`.

## 2. Slot occupancy checks were not backed by the right query shape/index

### (a) The problem

The one-to-one strategy previously checked slot availability through a relationship subquery even though `bookings.slot_id` is already stored on the booking row. The group strategy also counts active participants by slot, type, and status:

- `app/Strategies/BookingStrategies/OneToOneBookingStrategy.php:18` rejects occupied slots.
- `app/Strategies/BookingStrategies/OneToOneBookingStrategy.php:28` now uses a direct `slot_id` predicate.
- `app/Strategies/BookingStrategies/GroupBookingStrategy.php:34` counts group participants by `slot_id`.
- `app/Strategies/BookingStrategies/GroupBookingStrategy.php:35` filters `type`.
- `app/Strategies/BookingStrategies/GroupBookingStrategy.php:36` filters active statuses.
- `database/migrations/2026_04_28_214326_create_bookings_table.php:17` creates the foreign-key index for `slot_id`, but there was no composite index for `slot_id + status + type`.

Without a composite index, the database can use `slot_id` but may still scan all bookings for that slot to evaluate status/type. The previous one-to-one relationship subquery added unnecessary SQL complexity.

### (b) The scale trigger

This becomes painful for popular slots and resources. For example, if a class, room, or appointment slot accumulates hundreds or thousands of canceled/historical bookings, every capacity check must inspect more rows than necessary. At high write concurrency, slower occupancy checks increase the race window between availability validation and insert.

### (c) The mitigation

Implemented:

- Added `bookings_slot_status_type_idx` on `bookings(slot_id, status, type)` in `database/migrations/2026_06_06_211000_add_slot_status_type_index_to_bookings_table.php`.
- Changed one-to-one availability checks to use `where('slot_id', $slotId)->whereIn('status', ['pending', 'confirmed'])` in `app/Strategies/BookingStrategies/OneToOneBookingStrategy.php:28`.
- Applied the same direct predicate in `app/Factories/OneToOneBookingFactory.php`.
- Added `tests/Unit/BookingAvailabilityPerformanceTest.php` to prove the index exists and the availability query no longer uses a relationship subquery or join.

For stricter concurrency control, add a database-level uniqueness rule for active one-to-one bookings. On MySQL this usually needs either an application-level lock around `slot:{slot_id}` or a generated column that normalizes active statuses for a unique index.

## 3. Booking list reads over-fetch relations and are not cached

### (a) The problem

Every `Booking` model load globally eager-loads `slot`, `resource`, and `customer`, and every serialized booking appends `duration`:

- `app/Models/Booking.php:34` sets `protected $with = ['slot', 'resource', 'customer']`.
- `app/Models/Booking.php:36` appends `duration`.
- `app/Models/Booking.php:57` computes duration from the slot relation.
- `app/Repositories/BookingRepository.php:30` exposes paginated `all()`.
- `app/Repositories/BookingRepository.php:32` returns `Booking::query()->paginate()` with no cache and no projection.

The global eager loading avoids classic N+1 queries, but it creates an over-fetch problem on hot list reads. Any booking query, including admin lists or internal lookups that only need booking columns, pays for three relation queries and duration serialization.

### (b) The scale trigger

This becomes painful when booking list endpoints receive steady traffic or when pages include larger page sizes. At 100 requests per second, even a default 15-row page turns into 400 database queries per second for the base booking query plus three relation queries per request. The rows returned also include more data than many list views need.

### (c) The mitigation

Recommended:

- Remove `protected $with` from `App\Models\Booking`.
- Keep eager loading explicit with `Booking::query()->withRelations()` only in endpoints that return relation data.
- Add a cached repository method for hot list reads with a key like `bookings:index:v1:page:{page}:per_page:{perPage}:query:{sha1(request()->getQueryString())}` and a short TTL such as 30 seconds.
- Invalidate cache from `Booking::saved` and `Booking::deleted` model events, or use cache tags if the configured production cache driver supports them.
- Use column projections for list endpoints, for example `bookings.id`, `status`, `slot_id`, `customer_id`, `resource_id`, and only selected relation columns.

This keeps relation loading intentional and prevents frequent read endpoints from repeatedly assembling the same booking list under load.
