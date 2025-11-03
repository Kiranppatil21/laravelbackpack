Title: Statutory accuracy follow-up — TDS / PF / ESIC canonicalization

Summary
-------
This follow-up ticket captures required work to make statutory aggregates (TDS, PF, ESIC) legally accurate and auditable. Current implementation uses pragmatic heuristics and best-effort aggregations from `payrolls` and `expenses`. We need domain verification and deterministic data collection for production-grade statutory reports.

Goals / Acceptance criteria
---------------------------
- Replace heuristic estimates with authoritative formulas/fields or explicit per-payroll/per-expense columns (source-of-truth fields persisted).
- All statutory values (TDS, PF employer/employee, ESIC employer/employee) must be traceable back to source rows (payroll_id or expense_id) and their raw values in the database.
- Add unit & feature tests that assert exact expected values for canonical cases (including employer/employee splits and exemptions).
- Add a migration (if required) to add explicit columns to `payrolls` or `expenses` so values are stored at time of payroll/payment.
- Update `docs/FINANCE.md` to reflect authoritative formulas and input fields once approved.

Tasks
-----
1. Domain sign-off
   - Owners: Finance SME, Payroll team, Legal (as needed)
   - Deliverable: A short spec stating formulas and authoritative column names.

2. Data model changes (if required)
   - Add explicit columns to `payrolls` (e.g., `tds_amount`, `pf_employee`, `pf_employer`, `esic_employee`, `esic_employer`) and/or to `expenses` (e.g., `tds_withheld`, `tds_rate`, `tds_paid_at`), with migration and seeder for test data.
   - Backfill strategy for historical data: optional, documented, and flagged as out-of-scope for initial release.

3. Controller & aggregation changes
   - Replace heuristic math in `downloadStatutoryReportAdHoc` with direct sums of explicit columns. Keep fallback heuristics only if columns absent but mark as non-authoritative.
   - Add logging when fallback heuristics are used in production.

4. Tests & QA
   - Add PHPUnit tests covering canonical scenarios (single employee payroll with explicit TDS/PF/ESIC, vendor expense with TDS, multiple rates, zero-amount exemptions).
   - Add a Cypress E2E test that visits the Inertia page, requests ad-hoc CSV for a known fixture period, and validates the downloaded CSV contents match expected values.

5. Documentation & release notes
   - Update `docs/FINANCE.md` with spec, migration notes, and example fixture commands.
   - Add a short PR template checklist for statutory-reporting changes.

Risks / Notes
------------
- Legal/regulatory correctness is required; this ticket must not be closed without sign-off from Finance/Payroll SME.
- Backfilling historical data can be expensive; plan migration and choose appropriate timeframe.

Suggested PR checklist
----------------------
- [ ] Migration created (if new columns required) and reversible.
- [ ] Tests added & passing (unit, feature, E2E where applicable).
- [ ] Docs updated and reviewer(s) from Finance assigned.
- [ ] Logging/monitoring added for fallback heuristics.

Suggested label(s)
------------------
- area/finance
- needs/finance-review
- priority/high

Owner / Suggested assignee
--------------------------
- @finance-team (placeholder)

File path (this ticket)
-----------------------
`docs/PHASE6_STATUTORY_ACCURACY_TICKET.md`
