'use strict';

// Class definition
var datatable;

var catalog_datatable = (function() {
    // Private functions
    var options = {
        // datasource definition
        data: {
            type: 'remote',
            source: {
                read: {
                    url: $('#catalogs_datatable').data('action'), // تغییر اینجا برای کاتالوگ‌ها
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        )
                    },
                    map: function(raw) {
                        // sample data mapping
                        var dataSet = raw;
                        if (typeof raw.data !== 'undefined') {
                            dataSet = raw.data;
                        }
                        return dataSet;
                    },
                    params: {
                        query: $('#filter-catalog-form').serializeJSON() // تغییر فرم به فرم فیلتر کاتالوگ‌ها
                    }
                }
            },
            pageSize: 10,
            serverPaging: true,
            serverFiltering: true,
            serverSorting: true
        },

        layout: {
            scroll: true
        },

        rows: {
            autoHide: false
        },

        // columns definition
        columns: [
            {
                field: 'id',
                title: '#',
                sortable: false,
                width: 20,
                selector: {
                    class: ''
                },
                textAlign: 'center'
            },
            {
                field: 'image',
                title: 'تصویر',
                sortable: false,
                width: 80,
                template: function(row) {
                    return (
                        '<img class="post-thumb" src="' +
                        row.image +
                        '" alt="' +
                        row.title +
                        '">'
                    );
                }
            },
            {
                field: 'title',
                title: 'عنوان',
                width: 200,
                template: function(row) {
                    return row.title;
                }
            },
            {
                field: 'description',
                title: 'توضیحات',
                width: 300,
                template: function(row) {
                    return row.description;
                }
            },
            {
                field: 'created_at',
                sortable: 'desc',
                title: 'تاریخ ایجاد',
                template: function(row) {
                    return '<span class="ltr">' + row.created_at + '</span>';
                }
            },
            {
                field: 'actions',
                title: 'عملیات',
                textAlign: 'center',
                sortable: false,
                width: 200,
                overflow: 'visible',
                autoHide: false,
                template: function(row) {
                    return (
                        '<a href="' +
                        row.links.edit +
                        '" class="btn btn-warning waves-effect waves-light">ویرایش</a>\
                        <a href="catalog/' +
                        row.id +
                        '/delete" class="btn btn-danger waves-effect waves-light">حذف</a>'
                    );
                }
            }
        ]
    };

    var initDatatable = function() {
        // enable extension
        options.extensions = {
            // boolean or object (extension options)
            checkbox: true
        };

        datatable = $('#catalogs_datatable').KTDatatable(options); // تغییر ID به catalogs_datatable

        $('#filter-catalog-form .datatable-filter').on('change', function() {
            formDataToUrl('filter-catalog-form'); // تغییر فرم به فرم فیلتر کاتالوگ‌ها
            datatable.setDataSourceQuery(
                $('#filter-catalog-form').serializeJSON()
            );
            datatable.reload();
        });

        datatable.on('datatable-on-click-checkbox', function(e) {
            var ids = datatable.checkbox().getSelectedId();
            var count = ids.length;

            $('#datatable-selected-rows').html(count);

            if (count > 0) {
                $('.datatable-actions').collapse('show');
            } else {
                $('.datatable-actions').collapse('hide');
            }
        });

        datatable.on('datatable-on-reloaded', function(e) {
            $('.datatable-actions').collapse('hide');
        });
    };

    // Add delete event listener
    $(document).on('click', '.delete-catalog', function () {
        var catalogId = $(this).data('id');
        if (confirm('آیا مطمئن هستید که می‌خواهید این کاتالوگ را حذف کنید؟')) {
            $.ajax({
                url: '/catalog/' + catalogId + '/delete',
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    toastr.success('کاتالوگ با موفقیت حذف شد.');
                    datatable.reload();
                },
                error: function (xhr) {
                    toastr.error('خطایی در حذف کاتالوگ رخ داد: ' + xhr.statusText);
                }
            });
        }
    });



    return {
        // public functions
        init: function() {
            initDatatable();
        }
    };
})();

jQuery(document).ready(function() {
    catalog_datatable.init();
});

$('#catalog-multiple-delete-form').on('submit', function(e) {
    e.preventDefault();

    $('#multiple-delete-modal').modal('hide');

    var formData = new FormData(this);
    var ids = datatable.checkbox().getSelectedId();

    ids.forEach(function(id) {
        formData.append('ids[]', id);
    });

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        success: function(data) {
            toastr.success('کاتالوگ‌های انتخاب شده با موفقیت حذف شدند.');
            datatable.reload();
        },
        beforeSend: function(xhr) {
            block('#main-card');
            xhr.setRequestHeader(
                'X-CSRF-TOKEN',
                $('meta[name="csrf-token"]').attr('content')
            );
        },
        complete: function() {
            unblock('#main-card');
        },
        cache: false,
        contentType: false,
        processData: false
    });
});

$('#catalog-export-form').on('submit', function(e) {
    e.preventDefault();

    let formData = datatable.getDataSourceParam();
    let queryString = $.param(formData);

    let formData2 = new FormData(this);
    let queryString2 = new URLSearchParams(formData2).toString();

    let url = `${$(this).attr('action')}?${queryString}&${queryString2}`;

    window.open(url);
});
