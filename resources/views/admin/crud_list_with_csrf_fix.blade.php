{{-- This file is used to override the standard Backpack list view to include CSRF fixes --}}

@extends(backpack_view('crud.list'))

{{-- Include CSRF fix for popup forms --}}
@include('admin.csrf_fix')