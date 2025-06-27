@extends('back.layouts.master')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('back/app-assets/excel-css/excel.css') }}">
@endpush

@section('content')
    <div class="form-container">
        <div class="upload-files-container">
            <div class="drag-file-area">
                <span class="material-icons-outlined upload-icon"> آپلود فایل </span>

            </div>
            <span class="cannot-upload-message">
                <span class="material-icons-outlined">error</span>
                Please select a file first
                <span class="material-icons-outlined cancel-alert-button">cancel</span>
            </span>
            <div class="file-block">
                <div class="file-info">
                    <span class="material-icons-outlined file-icon">description</span>
                    <span class="file-name"> </span> | <span class="file-size"> </span>
                </div>
                <span class="material-icons remove-file-icon">delete</span>
                <div class="progress-bar"></div>
            </div>

            {{-- فرم آپلود فایل اکسل --}}
            <form action="{{ route('admin.products.save') }}" method="POST" enctype="multipart/form-data" class="form-excel">
                @csrf
                <div class="mb-3">
                    <label for="excelFile" class="form-label">فایل موجودی جدید را آپلود کنید</label>
                    <input class="form-control" type="file" id="file" name="file" accept=".xls,.xlsx,.csv" required>
                </div>
                <button type="submit" class="btn btn-primary pt-10">بارگذاری فایل</button>
            </form>

            <br>

            {{-- لینک دانلود فایل نمونه --}}
            <a href="{{ route('admin.products.excel.download') }}" id="download-link" class="btn btn-primary download-file">
                دانلود فایل نمونه
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('back/assets/js/pages/products/excel.js') }}?v=11"></script>
    <script>
        document.getElementById('download-link').addEventListener('click', function(event) {
            event.preventDefault();
            window.location.href = this.href;
        });
    </script>
@endpush
