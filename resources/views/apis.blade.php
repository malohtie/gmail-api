@extends('base')

@section('title', 'Api')

@section('menu')
    @include('menu')
@endsection

@section('css')
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.5/r-2.2.6/sl-1.3.1/datatables.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="p-2">
                <div class="alert alert-info" role="alert">
                    <code>
                        add this callback XXXX/accounts/callback
                    </code>
                </div>
                <div class="form-group">
                    <label for="name">name:</label>
                    <input type="text" id="name" class="form-control"/>
                </div>
                <div class="form-group">
                    <label for="client_id">client id:</label>
                    <input type="text" id="client_id" class="form-control"/>
                </div>
                <div class="form-group">
                    <label for="client_secret">client secret:</label>
                    <input type="text" id="client_secret" class="form-control"/>
                </div>
                <button id="add" class="btn btn-primary">ADD</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table id="apis" class="table table-sm table-hover table-striped" style="width:100%">
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript"
            src="https://cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.5/r-2.2.6/sl-1.3.1/datatables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <script>
        const buttons = [
            {
                text: 'reload',
                action: function (e, dt, node, config) {
                    dt.ajax.reload();
                }
            }
        ];

        const columns = [
            {title: "ID", data: "id", className: "text-center"},
            {title: "name", data: "name", className: "text-center"},
            {title: "total accounts", data: "accounts_count", className: "text-center"},
            {
                title: "is active", data: "is_active", className: "text-center", render: function (data) {
                    return data ? 'yes' : 'no';
                }
            },
            {
                title: "action",
                data: null,
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    const status = `<button data-id="${row.id}" data-action="${!row.is_active ? 1 : 0}" class="btn btn-sm btn-warning status">${row.is_active ? 'disable' : 'enable'}</button>&nbsp;`;
                    // del = `<button data-id="${row.id}" class="btn btn-sm btn-danger del">DELETE</button>&nbsp;`;
                    return status;
                }
            },
        ];

        let table = $("#apis").DataTable({
            dom: "<'row'<'col-sm-5'B><'col-sm-2'><'col-sm-5'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: buttons,
            columns: columns,
            scrollCollapse: true,
            Destroy: true,
            responsive: true,
            paging: false,
            order: [[0, "asc"]],
            processing: true,
            ajax: ' {{ route('api.index') }}',
            rowCallback: function (row, data, index) {
                if (!data.is_active) {
                    $(row).addClass("table-danger");
                    return row;
                }
            },
        }).on("click", ".status", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            const status = $(btn).data('action');

            $(btn).attr("disabled", true);
            $.ajax({
                url: '{{ route('api.status', ':id') }}'.replace(':id', id),
                type: "patch",
                dataType: "json",
                data: {
                    status
                }
            }).done(function (res) {
                table.ajax.reload();
            }).always(function () {
                $(btn).attr("disabled", false);
            });
        })/*.on("click", ".del", function (e) {
            e.preventDefault();
            const btn = this;
            const id = $(btn).data('id');
            if(confirm('Are You Sure ?')) {
                $(btn).attr("disabled", true);
                $.ajax({
                    url: ''.replace(':id', id),
                    type: "delete",
                    dataType: "json",
                }).done(function (res) {
                    if (res.status)
                        table.ajax.reload();
                    else
                        Swal.fire({
                            icon: "error",
                            title: "Oops",
                            text: "Failed"
                        });
                }).always(function () {
                    $(btn).attr("disabled", false);
                });
            }
        })*/;

        $("#add").click(function (e) {
            e.preventDefault();

            const name = $("#name").val();
            const client_id = $("#client_id").val();
            const client_secret = $("#client_secret").val();

            if (name && client_id && client_secret) {
                const btn = this;
                $(btn).attr('disabled', true).html("ADDING " + spinner);
                $.ajax({
                    url: '{{ route('api.add') }}',
                    type: "post",
                    dataType: "json",
                    data: {
                        name,
                        client_id,
                        client_secret
                    }
                }).done(function (res) {
                    if (res.status)
                        Swal.fire({
                            icon: "success",
                            title: "Done",
                            text: "Added"
                        }).then(() => {
                            table.ajax.reload();
                        });
                    else
                        Swal.fire({
                            icon: "error",
                            title: "Oops",
                            text: res.error
                        });
                }).fail((err) => {
                    Swal.fire({
                        icon: "error",
                        title: "Oops",
                        text: "Something went wrong"
                    });
                }).always(() => $(btn).html("ADD").attr("disabled", false));
            }
        });
    </script>
@endsection
