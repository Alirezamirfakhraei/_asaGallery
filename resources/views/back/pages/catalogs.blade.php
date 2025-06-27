@extends('back.layouts.master')
@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-9 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper col-12">
                                <ol class="breadcrumb no-border">
                                    <li class="breadcrumb-item">مدیریت
                                    </li>
                                    <li class="breadcrumb-item">صفحات
                                    </li>
                                    <li class="breadcrumb-item active">کاتالوگ ها
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="main-card" class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title">لیست کاتالوگ‌ها</h4>
                        <a href="{{route('admin.catalog.create')}}" class="btn btn-primary">افزودن کاتالوگ</a>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <div class="mb-2 collapse datatable-actions">
                                <div class="d-flex align-items-center">
                                    <div class="font-weight-bold text-danger mr-3"><span id="datatable-selected-rows">0</span> مورد انتخاب شده: </div>

                                    <button class="btn btn-danger mr-2" type="button" data-toggle="modal" data-target="#multiple-delete-modal">حذف همه</button>
                                </div>
                            </div>
                            <div class="datatable datatable-bordered datatable-head-custom" id="catalogs_datatable" data-action="{{ route('admin.catalog.apiIndex') }}"></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>



@endsection

@include('back.partials.plugins', ['plugins' => ['datatable']])


@push('scripts')
    <script src="{{ asset('back/assets/js/pages/catalogs/index.js') }}?v=2"></script>
@endpush
