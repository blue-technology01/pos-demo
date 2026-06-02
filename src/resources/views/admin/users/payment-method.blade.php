@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/user/payment-method.css') }}">
@endpush

@section('title', 'Payment Methods')

@section('content')
<div class="pm-wrapper">

    <div class="pm-header">
        <h2>Payment Methods</h2>
        <button class="btn-add" id="open-pm-modal">+ Add Method</button>
    </div>

    <div class="pm-table-wrap">
        <table class="pm-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Method Name</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>Cash</td>
                    <td>cash</td>
                    <td><span class="status active">Active</span></td>
                    <td>
                        <button class="btn edit" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                        </button>
                        <button class="btn delete" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </button>
                    </td>
                </tr>
                @for($i = 0; $i < 3; $i++)
                    <tr>
                        <td>{{ $i + 2 }}</td>
                        <td>ABA</td>
                        <td>cash</td>
                        <td><span class="status active">Active</span></td>
                        <td>
                            <button class="btn edit" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                </svg>
                            </button>
                            <button class="btn delete" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>

<div id="pm-dialog" title="Add New Payment Method" style="display:none;">
    <form id="pm-form" action="#" method="POST">
        @csrf
        <div class="form-group">
            <label for="pm_name">Method Name</label>
            <input type="text" id="pm_name" name="name" placeholder="e.g., ABA " required>
        </div>

        <div class="form-group">
            <label for="pm_code">Code</label>
            <input type="text" id="pm_code" name="code" placeholder="e.g., cc" required>
        </div>

        <div class="form-group">
            <label for="pm_status">Status</label>
            <select id="pm_status" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </form>
</div>
@endsection
@push('scripts')
    {{-- <script src="{{ asset('assets/js/dashboard/users/payment-method.js') }}"></script> --}}
@endpush
