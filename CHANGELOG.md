# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]
- Phase 4: Attendance & Payroll
  - Add `PayrollCalculator` service supporting old/new Indian tax regimes, EPF, and professional tax overrides.
  - Payslip PDF generation (Blade template + Dompdf integration).
  - Attendance models, controllers, and React/Inertia pages for check-ins and payslip listing.
  - Unit and feature tests for payroll calculations and professional tax mapping.
  - CI improvements: build Vite assets in PHPUnit job, pin PHP version for CI, and seed minimal test roles for stable E2E runs.

