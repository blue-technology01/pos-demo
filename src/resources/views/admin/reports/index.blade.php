@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard/report/index.css') }} " data-turbo-track="reload">
    <style>
        .report-date svg,
        .rnc-icon svg,
        .rnc-arrow {
            display: inline-block;
            vertical-align: middle;
            flex-shrink: 0;
        }

        .report-date svg {
            width: 16px;
            height: 16px;
            margin-right: 6px;
            color: #6b7280;
        }
        .rnc-icon svg {
            width: 20px;
            height: 20px;
        }

        .rnc-arrow {
            width: 18px;
            height: 18px;
            color: #9ca3af;
            margin-left: auto;
        }
    </style>
@endpush

@section('title', 'Reports Overview')

@section('content')
    {{-- Page Header --}}
    <div class="report-header">
        <div>
            <h1 class="report-title">Reports</h1>
            <p class="report-subtitle">Summary of your POS performance</p>
        </div>
        <div class="report-date">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>{{ now()->format('d M Y') }}</span>
        </div>
    </div>

    {{-- Revenue Overview Chart - Only ONE --}}
    <div class="report-chart">
        <div class="report-chart-header">
            <div>
                <div class="report-chart-title">Revenue Overview</div>
                <div class="report-chart-sub">Last 7 days performance</div>
            </div>
        </div>
        <div id="reportChart"></div>
    </div>

    {{-- Report Navigation Cards --}}
    <div class="report-nav-grid">
        <a href="{{ route('admin.revent-tracking') }}" class="report-nav-card">
            <div class="rnc-icon icon-blue">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <div class="rnc-body">
                <div class="rnc-title">Revenue Tracking</div>
                <div class="rnc-desc">Daily, weekly and monthly revenue breakdown by date</div>
            </div>
            <svg class="rnc-arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>

        <a href="{{ route('admin.sale-person') }}" class="report-nav-card">
            <div class="rnc-icon icon-green">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="rnc-body">
                <div class="rnc-title">Sales by Person</div>
                <div class="rnc-desc">Track individual staff performance and commission</div>
            </div>
            <svg class="rnc-arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>

        <a href="{{ route('admin.top-product') }}" class="report-nav-card">
            <div class="rnc-icon icon-orange">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                    <polygon points="12 22.08 12 12 3 6.92 3 17.08 12 22.08"></polygon>
                    <polygon points="12 12 21 6.92 21 17.08 12 22.08"></polygon>
                    <polygon points="12 2 3 6.92 12 12 21 6.92 12 2"></polygon>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="rnc-body">
                <div class="rnc-title">Top Products</div>
                <div class="rnc-desc">Best selling products ranked by quantity and revenue</div>
            </div>
            <svg class="rnc-arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('assets/js/dashboard/chart/report-chart.js') }}"></script>

    <script>
        (function() {
            // Prevent multiple initializations
            if (window.reportPageInitialized) return;
            window.reportPageInitialized = true;

            function initializeReportPage() {
                // Initialize the revenue chart
                if (typeof initializeReportChart === 'function') {
                    initializeReportChart();
                }
            }

            // Turbo compatibility (Hotwire)
            document.addEventListener('turbo:load', initializeReportPage);
            document.addEventListener('turbo:render', initializeReportPage);

            // Fallback for normal loads
            if (document.readyState === 'complete') {
                initializeReportPage();
            } else {
                document.addEventListener('DOMContentLoaded', initializeReportPage);
            }
        })();
    </script>
@endpush q
