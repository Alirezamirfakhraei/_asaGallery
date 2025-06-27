<div class="card">
    <div class="card-header filter-card">
        <h4 class="card-title">فیلتر کردن</h4>
        <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
        <div class="heading-elements">
            <ul class="list-inline mb-0">
                <li><a data-action="collapse"><i class="feather icon-chevron-down"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="card-content collapse {{ request()->except('page') ? 'show' : '' }}">
        <div class="card-body">
            <div class="clients-list-filter">
                <form id="filter-clients-form" method="GET"
                      action="{{ route('admin.nila.getClients') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label>نام کلاینت</label>
                            <fieldset class="form-group">
                                <input class="form-control datatable-filter" name="name" value="{{ request('name') }}">
                            </fieldset>
                        </div>

                        <div class="col-md-3">
                            <label>ایمیل</label>
                            <fieldset class="form-group">
                                <input class="form-control datatable-filter" name="email" value="{{ request('email') }}">
                            </fieldset>
                        </div>

                        <div class="col-md-3">
                            <label>وضعیت</label>
                            <fieldset class="form-group">
                                <select class="form-control datatable-filter" name="status">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>
                                        همه
                                    </option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                        فعال
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        غیر فعال
                                    </option>
                                </select>
                            </fieldset>
                        </div>

                        <div class="col-md-3">
                            <label>تاریخ ثبت</label>
                            <fieldset class="form-group">
                                <input type="date" class="form-control datatable-filter" name="created_from" value="{{ request('created_from') }}" placeholder="از تاریخ">
                                <input type="date" class="form-control datatable-filter mt-1" name="created_to" value="{{ request('created_to') }}" placeholder="تا تاریخ">
                            </fieldset>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
