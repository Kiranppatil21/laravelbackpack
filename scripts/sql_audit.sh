#!/usr/bin/env bash
# Quick grep-based audit for raw SQL usage patterns that may need review
set -euo pipefail

echo "Scanning PHP files for raw SQL patterns (whereRaw, selectRaw, DB::raw, unprepared, whereRaw)..."
grep -RIn --include="*.php" -E "\bwhereRaw\b|\bselectRaw\b|DB::raw|unprepared\(|joinRaw|->raw\(" || true

echo "\nReview the listed locations for user-input interpolation. Many usages are safe when using parameter bindings." 
