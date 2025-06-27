@extends('back.layouts.master')

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="col-12">
                    <h3 class="content-header-title">لیست خروجی کلاینت‌ها</h3>
                </div>
            </div>

            <div class="content-body">
                <section class="card">
                    <div class="card-body">
                        <form method="GET" action="" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" id="live-search" class="form-control mb-3" placeholder="جستجو بر اساس نام یا کد طرف حساب...">
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>کد طرف حساب</th>
                                    <th>نام</th>
                                    <th>کد ERP</th>
                                    <th>تلفن</th>
                                    <th>آدرس</th>
                                    <th class="text-center">عملیات</th>
                                </tr>
                                </thead>
                                <tbody id="client-table-body">
                                @foreach($clients as $client)
                                    <tr>
                                        <td>{{ $client->code }}</td>
                                        <td>{{ $client->name }}</td>
                                        <td>{{ $client->erpCode }}</td>
                                        <td>{{ $client->mobile }}</td>
                                        <td>{{ $client->address }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.subClients.add.view', $client->id) }}" class="btn btn-sm btn-outline-primary" title="ثبت اکانت">
                                                <i class="fa fa-plus"></i>
                                            </a>

                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                            </table>
                        </div>

                        {{-- صفحه‌بندی --}}
                        <div class="mt-2" id="pagination-wrapper">
                            {{ $clients->links() }}
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
                url: '{{ route("admin.clients.search") }}',
                type: 'GET',
                data: { query: query },
                success: function (data) {
                    $('#client-table-body').html(data.html);
                }
            });
        });
    </script>
@endpush
