@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css"
          href="{{ asset('back/app-assets/plugins/jquery-tagsinput/jquery.tagsinput.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/plugins/jquery-ui/jquery-ui.css') }}">
@endpush

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
                                    <li class="breadcrumb-item">مدیریت کاتالوگ ها
                                    </li>
                                    <li class="breadcrumb-item active">ایجاد کاتالوگ
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <!-- Description -->
                <section id="description" class="card">
                    <div class="card-header">
                        <h4 class="card-title">ایجاد کاتالوگ</h4>
                    </div>

                    <div id="main-card" class="card-content">
                        <div class="card-body">
                            <div class="col-12 col-md-10 offset-md-1">
                                <form class="form" id="catalog-edit-form" action="{{ route('admin.catalog.store') }}"
                                      method="post" enctype="multipart/form-data">
                                    @csrf
                                    @method('post')
                                    <div class="form-body">
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>عنوان</label>
                                                    <input type="text" class="form-control" name="title">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <div class="form-group">
                                                    <label>لینک دانلود</label>
                                                    <input type="text" class="form-control" name="link">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>توضیحات</label>
                                                    <input type="text" class="form-control" name="meta_title">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>url</label>
                                                    <input id="slug" type="text" class="form-control" name="slug">
                                                    <p>
                                                        <small>
                                                            <a id="generate-post-slug" href="#">ایجاد خودکار</a>
                                                            <span id="slug-spinner"
                                                                  class="spinner-grow spinner-grow-sm text-success"
                                                                  role="status" style="display: none;">
                                                                <span class="sr-only">Loading...</span>
                                                            </span>
                                                        </small>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>توضیحات سئو</label>
                                                    <textarea class="form-control" name="meta_description"
                                                              rows="3"></textarea>
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <fieldset class="form-group">
                                                    <label>کلمات کلیدی</label>
                                                    <input id="tags" type="text" name="tags" class="form-control">
                                                </fieldset>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <fieldset class="form-group">
                                                    <label>تصویر شاخص</label>
                                                    <div class="custom-file">
                                                        <input id="image" type="file" accept="image/*" name="image"
                                                               class="custom-file-input">
                                                        <label class="custom-file-label" for="image"></label>
                                                        <p><small>بهترین اندازه<span
                                                                    class="text-danger">{{ config('front.imageSizes.postImage') }}</span>
                                                                پیکسل میباشد.</small></p>
                                                    </div>
                                                </fieldset>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>تاریخ انتشار</label>
                                                    <input autocomplete="off" type="text" class="form-control"
                                                           id="publish_date_picker">
                                                    <input type="hidden" name="created_at">
                                                </div>
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <fieldset class="checkbox">
                                                    <div class="vs-checkbox-con vs-checkbox-primary">
                                                        <input type="checkbox" name="published">
                                                        <span class="vs-checkbox">
                                                            <span class="vs-checkbox--check">
                                                                <i class="vs-icon feather icon-check"></i>
                                                            </span>
                                                        </span>
                                                        <span>انتشار کاتالوگ؟</span>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <button type="submit"
                                                        class="btn btn-primary mr-1 mb-1 waves-effect waves-light">
                                                    انتشار کاتالوگ
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
                <!--/ Description -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('back/app-assets/plugins/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-tagsinput/jquery.tagsinput.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('back/app-assets/plugins/jquery-ui/jquery-ui.js') }}"></script>

    <script src="{{ asset('back/assets/js/pages/pages/edit.js') }}"></script>
@endpush
