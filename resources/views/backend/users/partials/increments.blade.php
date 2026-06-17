<div class="row align-items-center mb-3">
    <div class="col"></div>
    <div class="col-auto">
            <!-- <a href="{{route('backend.asset.assign-user',$user)}}" class="btn btn-primary btn-md edit-button">
                <i class="fas fa-plus"></i> {{__trans('increment')}}
            </a> -->
    </div>
</div>
<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="card">
            <table class="table text-center table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{__trans('increment')}}</th>
                        <th>{{__trans('increment_date')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $usersalaryincrement = Modules\Payroll\Entities\UserSalaryIncrement::where('user_id', $user->id)->get();
                    @endphp
                    @foreach ($usersalaryincrement as $row=> $increment)
                    <tr style="color:white;">
                        <td>{{$row+1}}</td>
                        <td>{{$increment->increment}}</td>
                        <td>{{$increment->increment_date ?? 'N/A'}}</td>
                    </tr>
                    @endforeach


                </tbody>
            </table>
        </div>
    </div>
</div>
