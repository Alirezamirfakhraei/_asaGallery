@extends('back.layouts.master')

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="col-12">
                    <h3 class="content-header-title">لیست فاکتورها</h3>
                </div>
            </div>

            <div class="content-body">
                <section class="card">
                    <div class="card-body">
                        <form method="GET" action="" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" id="live-search" class="form-control mb-3" placeholder="جستجو بر اساس کد فاکتور یا نام مشتری...">
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>کد فاکتور</th>
                                    <th>نام مشتری</th>
                                    <th>تاریخ</th>
                                    <th>مبلغ کل</th>
                                    <th>نوع فاکتور</th>
                                    <th class="text-center">عملیات</th>
                                </tr>
                                </thead>
                                <tbody id="invoice-table-body">
                                @foreach($invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->code }}</td>
                                        <td>{{ $invoice->customerName }}</td>
                                        <td>{{ $invoice->date }}</td>
                                        <td>{{ number_format($invoice->sumPrice) }}</td>
                                        <td>{{ $invoice->type }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-info" title="نمایش جزئیات">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.invoices.edit', $invoice->id) }}" class="btn btn-sm btn-outline-primary" title="ویرایش">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.invoices.destroy', $invoice->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('آیا از حذف این فاکتور مطمئن هستید؟')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                            </table>
                        </div>

                        {{-- صفحه‌بندی --}}
                        <div class="mt-2" id="pagination-wrapper">
                            {{ $invoices->links() }}
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#live-search').on('input', function () {
            let query = $(this).val().trim();

            if (query.length > 0) {
                $('#pagination-wrapper').hide();  // مخفی کردن صفحه‌بندی هنگام جستجو
            } else {
                $('#pagination-wrapper').show();  // نمایش صفحه‌بندی وقتی سرچ خالی شد
            }

            $.ajax({
                url: '{{ route("admin.invoices.search") }}',
                type: 'GET',
                data: { query: query },
                success: function (data) {
                    $('#invoice-table-body').html(data.html);
                }
            });
        });
    </script>
@endpush
