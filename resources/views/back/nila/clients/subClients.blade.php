@extends('back.layouts.master')

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="col-12">
                    <h3 class="content-header-title">لیست خروجی ساب کلاینت‌ها</h3>
                </div>
            </div>

            <div class="content-body">
                <section class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>نام</th>
                                    <th>نام کاربری</th>
                                    <th>وضعیت</th>
                                    <th class="text-center">عملیات</th>
                                </tr>
                                </thead>
                                <tbody id="client-table-body">
                                @foreach($subClients as $subClient)
                                    <tr>
                                        <td>{{ $subClient->fullName }}</td>
                                        <td>{{ $subClient->username }}</td>
                                        <td>{{ $subClient->status }}</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('admin.subClient.destroy', $subClient->id) }}" style="display:inline-block;" onsubmit="return confirm('آیا مطمئن هستید؟')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف ویزیتور">
                                                    <i class="fa fa-remove"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                            </table>
                        </div>

                        {{-- صفحه‌بندی --}}


                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
