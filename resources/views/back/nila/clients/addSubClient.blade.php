@extends('back.layouts.master')

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-wrapper">
            <div class="content-body">
                <section class="card">
                    <div class="card-body">
                        <div class="content-header row">
                            <div class="col-12">
                                <h3 class="content-header-title">
                                    ثبت اکانت برای کلاینت: <span style="color: #ff5252;">{{ $client->name }}</span>
                                </h3>
                            </div>
                        </div>
                        <hr>
                        <form action="{{ route('admin.subClients.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="client_id" value="{{ $client->id }}">

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>نام کاربری</label>
                                    <input type="text" class="form-control" value="{{ $client->code }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>نام</label>
                                    <input type="text" name="name" class="form-control" placeholder="نام کاربر خود را وارد کنید">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>رمز عبور</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>وضعیت</label>
                                <select name="status" class="form-control">
                                    <option value="1">فعال</option>
                                    <option value="0">غیرفعال</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success">ایجاد اکانت</button>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">بازگشت</a>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
