# Smart Locker Availability and Renewal API

All protected routes require `Authorization: Bearer <sanctum-token>`.

## Availability calendar

`GET /api/smart-lockers/{lockerId}/availability?from=YYYY-MM-DD&to=YYYY-MM-DD`

Dates are inclusive in the response. Omit both dates to receive today through the next 90 days. The requested range cannot exceed 366 days.

```json
{
  "status": "success",
  "data": {
    "smart_locker_id": 12,
    "from": "2026-10-09",
    "to": "2026-10-12",
    "dates": [
      { "date": "2026-10-09", "is_available": true, "reason": null },
      { "date": "2026-10-10", "is_available": false, "reason": "booked" },
      { "date": "2026-10-11", "is_available": false, "reason": "admin_block" },
      { "date": "2026-10-12", "is_available": true, "reason": null }
    ]
  }
}
```

`GET /api/smart-lockers` and `GET /api/smart-lockers/{lockerId}` also return `is_available` and a date-sensitive `status` for today. An admin maintenance status makes every date unavailable.

## Book a locker

`POST /api/smart-lockers/book`

```json
{
  "smart_locker_id": 12,
  "payment_gateway": "bbl",
  "duration_months": 12,
  "start_date": "2026-11-01",
  "address_id": 25,
  "custom_address_text": null
}
```

The API checks admin-blocked dates and active or pending locker bookings again when creating the booking. Dates use a half-open booking interval: `start_date` is included and `end_date` is the first day after the paid period. A date conflict returns HTTP 409.

## Near-expiry services and renewal

`GET /api/main/expiring-services` includes existing products plus locker contracts expiring within 30 days. Locker items include:

```json
{
  "service_type": "smart_locker",
  "booking_id": 842,
  "smart_locker_id": 12,
  "booking_number": "LCK-202610-ABCDE",
  "locker_number": "PR-001",
  "end_date": "2026-11-01",
  "can_renew": true,
  "monthly_price": 1500,
  "renewal": {
    "method": "POST",
    "endpoint": "/api/smart-lockers/bookings/842/renew",
    "required_fields": ["duration_months", "payment_gateway"],
    "payment_gateway_options": ["bbl"]
  }
}
```

When the customer taps **ต่ออายุ**, the app can request the desired term and then open the returned payment URL in its payment WebView:

`POST /api/smart-lockers/bookings/{bookingId}/renew`

```json
{ "duration_months": 12, "payment_gateway": "bbl" }
```

The response contains a new `booking_number`, renewal dates, `total_amount`, and `payment_url`. The existing contract is extended only after the BBL payment webhook confirms payment. Renewal is available to the contract owner within 90 days of expiry; only one unpaid renewal may exist for a contract at a time.
