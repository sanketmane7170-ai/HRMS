
@extends('layouts.backend')

@section('content')


<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">{{__trans('expense_view')}}</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('backend.dashboard')}}">{{__trans('dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('backend.expense.index')}}">{{__trans('my_expenses')}}</a></li>
                        <li class="breadcrumb-item active">{{__trans('expense_view')}}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    @can('Permission Expense')
                   
                    @if(
                        (auth()->user()->hasRole("LM") && $expense->lm_status == \Modules\Expense\Enums\ExpenseStatus::Pending->value)
                    || (auth()->user()->hasRole(App\Models\User::ROLE_HR) && $expense->hr_status == \Modules\Expense\Enums\ExpenseStatus::Pending->value )
                    || (auth()->user()->hasRole(App\Models\User::ROLE_ADMIN) && $expense->status->value == \Modules\Expense\Enums\ExpenseStatus::Pending->value)
                    )


                    <a class="btn btn-success action-button" href="{{route('backend.expense.action',[$expense,'approve'])}}" method="POST" data-alert="{{__trans('are_you_sure_want_to_apporve_expense?')}}" redirect>
                        <i class="fas fa-check"></i> {{__trans('approve')}}
                    </a>
                    <a class="btn btn-danger edit-button" href="{{route('backend.expense.action',[$expense,'reject'])}}" method="POST">
                        <i class="fas fa-times"></i> {{__trans('reject')}}
                    </a>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
        @php
        $authUser =$expense->user;
        @endphp
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td> <strong>{{__trans('date')}}</strong> </td>
                                    <td> {{formatDate($expense->date)}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('expense_type')}}</strong> </td>
                                    <td> {{$expense->type->name}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('name')}}</strong> </td>
                                    <td> {{$expense->name}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('employee_name')}}</strong> </td>
                                    <td> {{$expense->user->name}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('creator_name')}}</strong> </td>
                                    <td> {{$expense->creator->name}}</td>
                                </tr>

                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <td> <strong>{{__trans('amount')}}</strong> </td>
                                    <td> {{$expense->amount}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('payment_mode')}}</strong> </td>
                                    <td> {{$expense->payment_mode}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('lm_status')}}</strong> </td>
                                    <td>  {{__trans($expense->lm_status)}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('hr_status')}}</strong> </td>
                                    <td> {{__trans($expense->hr_status)}}</td>
                                </tr>
                                <tr>
                                    <td> <strong>{{__trans('status')}}</strong> </td>
                                    <td> {!! $expense->status->getHtml()!!}</td>
                                </tr>
                               

                            </table>
                        </div>

                        

                     
                        <div class="col-md-12 mt-2 p-4">
                            <div class="mb-3">
                                <h6>{{__trans('Uploaded Documents')}}</h6>
                            </div>
                            <div class="col-md-6 mt-6">

                                @foreach($expense->documents as $document)
                                <li id="{{ $document->id }}" class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ asset('uploads/expense/'. $expense->user_id.'/'. $document->document) }}" target="_blank">
                                        {{ $document->document_name }}
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteDocument({{ $document->id }},{{ $expense->user_id }})">Delete</button>
                                </li>
                                @endforeach


                            </div>
                        </div>

                        <div class="col-md-12 mt-2 p-4">
                            <label for="reason"> <strong>{{__trans('remark')}}</strong></label>
                            <p>
                                {{$expense->remark}}
                            </p>
                            @if($expense->file_path)
                            <a href="{{asset($expense->file_path)}}" target="_blank">{{__trans('view_document')}}</a>
                            @endif
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="editModal">

</div>

<script>
    function deleteDocument(documentId, user_id) {
        if (confirm('Are you sure you want to delete this document?')) {
            fetch(`/expense_documents/${documentId}/${user_id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // location.reload();
                        $('#' + documentId).remove();
                    } else {
                        alert('Failed to delete document');
                    }
                });
        }
    }
</script>

@endsection