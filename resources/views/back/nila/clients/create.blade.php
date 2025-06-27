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
                        <form action="{{ route('admin.nila.client.store') }}" method="POST">
                            @csrf
                            @method('post')
                            {{-- اطلاعات اصلی کلاینت --}}
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>نام</label>
                                    <input type="text" name="name" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>تلفن</label>
                                    <input type="text" name="tel" class="form-control">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>شهر</label>
                                    <input type="text" name="city" class="form-control">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="custtype">نوع مشتری</label>
                                    <select name="custtype" id="custtype" class="form-control" required>
                                        <option value="">انتخاب کنید</option>
                                        <option value="0">بدهکار</option>
                                        <option value="2">بستانکار</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>آدرس</label>
                                <textarea name="address" class="form-control"></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label><input type="checkbox" name="isPurchaser"> خریدار</label>
                                </div>
                                <div class="form-group col-md-3">
                                    <label><input type="checkbox" name="isSeller"> فروشنده</label>
                                </div>
                            </div>

                            {{-- تیک برای نمایش فرم ساب‌کلاینت --}}
                            <div class="form-group col-md-6">
                                <label><input type="checkbox" id="hasSubClient" name="hasSubClient" value="1"> دارای ساب‌کلاینت</label>
                            </div>

                            {{-- فرم ساب‌کلاینت‌ها --}}
                            <div id="subClientContainer" style="display: none;">
                                <hr>
                                <h4>ساب‌کلاینت‌ها</h4>
                                <div id="subClientFieldsWrapper">
                                    <div class="sub-client-fields border rounded p-2 mb-2">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>نام کامل</label>
                                                <input type="text" name="sub_clients[0][fullName]" class="form-control">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>رمز عبور</label>
                                                <input type="password" name="sub_clients[0][password]" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>وضعیت</label>
                                                <select name="sub_clients[0][status]" class="form-control">
                                                    <option value="active">فعال</option>
                                                    <option value="inactive">غیرفعال</option>
                                                </select>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-subclient">حذف</button>
                                    </div>
                                </div>
                                <button type="button" id="addSubClientBtn" class="btn btn-warning mt-2">افزودن ساب‌کلاینت دیگر</button>
                                <hr>
                            </div>
                            <button type="submit" class="btn btn-primary">ثبت کلاینت</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasSubClientCheckbox = document.getElementById('hasSubClient');
            const subClientContainer = document.getElementById('subClientContainer');
            const subClientFieldsWrapper = document.getElementById('subClientFieldsWrapper');
            const addSubClientBtn = document.getElementById('addSubClientBtn');

            let subClientIndex = 1;

            if (hasSubClientCheckbox.checked) {
                subClientContainer.style.display = 'block';
            }

            hasSubClientCheckbox.addEventListener('change', function() {
                subClientContainer.style.display = this.checked ? 'block' : 'none';
            });

            addSubClientBtn.addEventListener('click', function() {
                const newFields = document.createElement('div');
                newFields.classList.add('sub-client-fields', 'border', 'rounded', 'p-2', 'mb-2');

                newFields.innerHTML = `
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>نام کامل</label>
                            <input type="text" name="sub_clients[${subClientIndex}][fullName]" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label>رمز عبور</label>
                            <input type="password" name="sub_clients[${subClientIndex}][password]" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>وضعیت</label>
                            <select name="sub_clients[${subClientIndex}][status]" class="form-control">
                                <option value="active">فعال</option>
                                <option value="inactive">غیرفعال</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-subclient">حذف</button>
                    <hr>
                `;

                subClientFieldsWrapper.appendChild(newFields);
                subClientIndex++;
            });

            subClientFieldsWrapper.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-subclient')) {
                    e.target.closest('.sub-client-fields').remove();
                }
            });
        });
    </script>
@endpush
