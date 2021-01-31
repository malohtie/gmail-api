@extends('base')

@section('title', 'Accounts')

@section('menu')
    @include('menu')
@endsection

@section('css')
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.5/r-2.2.6/sl-1.3.1/datatables.min.css"/>
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <div class="p-2">
                <div class="form-group">
                    <label for="data">Add Gmail Accounts</label>
                    <textarea id="data" class="form-control" rows="10" placeholder="...@gmail.com"></textarea>
                </div>
                <button id="add" class="btn btn-primary">ADD</button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table id="accounts" class="table table-sm table-hover table-striped" style="width:100%">
                </table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript"
            src="https://cdn.datatables.net/v/bs4/dt-1.10.20/b-1.6.5/r-2.2.6/sl-1.3.1/datatables.min.js"></script>
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
            {title: "account", data: "email", className: "text-center"},
            {
                title: "is auth", data: "token", className: "text-center", render: function (data) {
                    return data ? 'yes' : 'no';
                }
            },
            {
                title: "is active", data: "is_active", className: "text-center", render: function (data) {
                    return data ? 'yes' : 'no';
                }
            },
            {
                title: "Action",
                data: null,
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    const status = `<button data-id="${row.id}" class="btn btn-sm btn-warning>${row.is_active ? 'disable' : 'enable'}</button>&nbsp;`;
                    let auth = '';
                    if (row.is_active && !row.token) {
                        auth = `<button data-id="${row.id}" class="btn btn-sm btn-primary>${row.is_active ? 'disable' : 'enable'}</button>&nbsp;`;
                    }
                    return status + auth;
                }
            },
        ];

        let table = $("#accounts").DataTable({
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
            ajax: ' {{ route('account.index') }}',
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

            $(btn).attr("disabled", true).html(spinner);
            const apikey = $("#apikey").val();
            $.ajax({
                url: '{{ route('account.status', ':id') }}'.replace(':id', id),
                type: "post",
                dataType: "json",
                data: {
                    apikey,
                    domain,
                    ipadr,
                    id,
                }
            }).done(function (res) {
                Swal.fire({
                    icon: res.status ? 'success' : 'error',
                    title: res.status ? 'Done' : 'Oops',
                    text: res.data
                })
            }).always(function () {
                $(btn).attr("disabled", false).html("SET RDNS");
            });
        });


        $("#add").click(function (e) {
            e.preventDefault();

            const accounts = [...new Set($("#data").val().split("\n"))];
            if (accounts) {
                const btn = this;
                $(btn).attr('disabled', true).html("ADDING " + spinner);
                $.ajax({
                    url: '{{ route('account.add') }}',
                    type: "post",
                    dataType: "json",
                    data: {
                        accounts
                    }
                }).done(function (res) {
                    if (res.status)
                        Swal.fire({
                            icon: "success",
                            title: "Done",
                            text: "Added " + res.nb
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
