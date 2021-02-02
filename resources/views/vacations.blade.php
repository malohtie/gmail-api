@extends('base')

@section('title', 'Vacations')

@section('menu')
    @include('menu')
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
@endsection

@section('body')
    <div class="row">
        <div class="col-sm-12">
           <div class="form-group">
               <label for="accounts">Accounts:</label>
               <select id="accounts" class="form-control selectpicker" data-live-search="true" data-actions-box="true" multiple>
                   @foreach($accounts as $account)
                       <option value="{{ $account->id }}">{{ $account->email }}</option>
                   @endforeach
               </select>
           </div>
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="body">Body Html:</label>
                <textarea id="body" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <button id="make" class="btn btn-primary">MAKE</button>
            </div>
        </div>
        <div class="col-sm-12">
            <h5>Result:</h5>
            <hr>
            <table id="table" class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody id="result">
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <script>
        $("#make").on("click", function (e) {
            e.preventDefault();
            const accounts = $('#accounts').val();
            if (accounts.length > 0) {
                const subject = $("#subject").val();
                const body = $("#body").val();
                if(subject && body)
                {
                    $("#result").html("");
                    $.each(accounts, (i, v) => {
                        const email = $("#accounts option[value="+v+"]").text();
                        $("#result").append(
                            `<tr>
                                <td>${email}</td>
                                <td id="res_${v}">${spinner}</td>
                            </tr>`
                        );

                        $.ajax({
                            url: "{{ route('vacations.make', ':id') }}".replace(":id", v),
                            method: "post",
                            dataType: "json",
                            data: {
                                from,
                                subject,
                                body
                            }
                        }).done(function (res) {
                            $("#res_"+v).html(`<p class="${res.status ? 'text-success' : 'text-danger'}">${res.message}</p>`)
                        }).fail(() => console.log("ERROR " + v));
                    })
                }
                else
                {
                    Swal.fire({
                        icon: "error",
                        title: "Oops",
                        text: "Something Missing"
                    });
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops",
                    text: "Select Accounts"
                });
            }
        })
    </script>
@endsection
