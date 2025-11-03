# Finance & Compliance API

This document describes the Phase 6 finance endpoints added to the API: invoices, payments, statutory reports (GST), and profitability.

Base path: /api/finance

Endpoints
---------

1) Create invoice

POST /api/finance/invoices

Request JSON:
{
  "client_id": 1,            // optional
  "issued_date": "2025-10-10",
  "due_date": "2025-10-31",
  "currency": "INR",
  "items": [
    { "description": "Design work", "qty": 10, "unit_price": 1000, "tax_rate": 18 }
  ]
}

Response: 201 Created
Returns created invoice with items and payments.

Curl example:

```bash
curl -X POST https://example.com/api/finance/invoices \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"client_id":1, "issued_date":"2025-10-01","items":[{"description":"Service","qty":2,"unit_price":500,"tax_rate":18}] }'
```

2) Record payment

POST /api/finance/invoices/{invoice}/payments

Request JSON:
{
  "amount": 1000,
  "paid_at": "2025-10-15T10:00:00Z",
  "method": "bank_transfer",
  "reference": "TX123"
}

Response: 201 Created (payment resource)

Curl example:

```bash
curl -X POST https://example.com/api/finance/invoices/123/payments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount":1000, "method":"bank_transfer", "reference":"TX123"}'
```

3) Generate statutory report (GST / TDS / PF / ESIC)

POST /api/finance/reports/statutory

Request JSON:
{
  "type": "gst",          // currently accepts: gst, tds, pf, esic
  "period_start": "2025-10-01",
  "period_end": "2025-10-31"
}

Response: 201 Created
Returns a `StatutoryReport` model with `payload` containing `summary.rows` which includes aggregated rows per tax rate (for GST).

Generate & download CSV (from the UI): the API creates a report and then the frontend opens the download URL for the CSV.

3.b) Ad-hoc CSV download (no persistence)

POST /api/finance/reports/statutory/download

Request JSON (body):
{
  "type": "gst",          // gst, tds, pf, esic
  "period_start": "2025-10-01",
  "period_end": "2025-10-31"
}

Response: 200 with CSV body (Content-Type: text/csv). This endpoint computes aggregates on-the-fly and streams a CSV without creating a StatutoryReport record. Use this when you want a quick export for a period without persisting the report.

Important assumptions and behavior for ad-hoc (and generated) statutory exports:

- GST: aggregated from `invoice_line_items` joined to `invoices` by `invoice_id`. CSV rows include `tax_rate`, `taxable_value`, and `tax_amount` per tax rate.
- TDS: aggregates employee TDS from `payrolls.tax` (if `payrolls` table exists). For vendor TDS we look for a `tds`-like column on an `expenses` table (common names: `tds`, `tds_withheld`, `tds_amount`) and sum it; if no explicit column exists we do NOT assume a default vendor TDS rate (vendor TDS will be reported as 0.0).
- PF: if `payrolls` contains an explicit EPF/ PF column (common names: `epf`, `employee_epf`, `epf_employee`) it will be summed. Otherwise the export will provide a best-effort estimate using the heuristic `estimated_pf = sum(gross) * 0.4 * 0.12` (assumes 40% of gross is basic pay and EPF is 12% of basic). This is an approximation — persist explicit PF columns for accurate reporting.
- ESIC: if `payrolls` contains an explicit `esic` or `esic_employer` column it will be summed. Otherwise a pragmatic default employer+employee estimate of 4.75% of gross is used (`estimated_esic = sum(gross) * 0.0475`). Adjust this for your jurisdiction.

Notes on testing: the ad-hoc endpoint returns the CSV body directly when running in the testing environment so PHPUnit tests can assert CSV headers and content. In production it streams the CSV (same CSV format).

4) Download statutory report CSV

GET /api/finance/reports/statutory/{report}/download

Response: streamed CSV file. Columns depend on the report type; GST reports include `tax_rate`, `taxable_value`, `tax_amount` per row.

Curl example:

```bash
# generate the report (obtain `id` from response)
RESP=$(curl -s -X POST https://example.com/api/finance/reports/statutory \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"type":"gst","period_start":"2025-10-01","period_end":"2025-10-31"}')
ID=$(echo "$RESP" | jq -r '.id')

curl -L -H "Authorization: Bearer $TOKEN" https://example.com/api/finance/reports/statutory/${ID}/download -o gst-report-${ID}.csv
```

5) Profitability summary

GET /api/finance/reports/profitability?period_start=2025-10-01&period_end=2025-10-31

Response JSON:
{
  "revenue": 100000.0,
  "costs": 60000.0,
  "breakdown": { "payroll": 50000.0, "expenses": 10000.0 },
  "gross_margin": 40000.0,
  "margin_percent": 40.0,
  "by_client": [ { "client_id": 1, "revenue": 70000.0 }, ... ]
}

Notes & Implementation details
-------------------------------
- Payroll: the profitability endpoint will include payroll cost by summing `payrolls.gross` across the requested periods (if `payrolls` table exists).
- Expenses: if the repo has an `expenses` table (assumed columns `amount` and `date`) it will be included; otherwise expenses are treated as 0.
- CSV: the downloaded CSV is a simple export of the report payload. For GST the CSV includes header rows and then one row per tax rate.

Next steps / enhancements
-------------------------
- Add CSV export for profitability and allow filtering by client in the UI.
- Implement deeper statutory report logic for TDS / PF / ESIC using payroll and vendor payment data.
- Add pagination and CSV export controls in the admin UI.

