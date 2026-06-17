<div class="modal-dialog ">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __trans('edit_promotion') }}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('backend.user-promotions.update', $userPromotion) }}" datatable="true" method="POST" class="ajax-form-submit">
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="row">

                    <!-- User -->
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">{{ __trans('user') }}</label>
                            <select name="user_id" id="user_id" class="form-control select2" required>
                                <option value="">{{ __trans('select_user') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $userPromotion->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Old Designation -->
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="old_designation_id" class="form-label">{{ __trans('old_designation') }}</label>
                            <select disabled name="old_designation_id" id="old_designation_id" class="form-control select2">
                                <option value="">{{ __trans('select_old_designation') }}</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}" {{ $userPromotion->old_designation_id == $designation->id ? 'selected' : '' }}>
                                        {{ $designation->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- New Designation -->
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="new_designation_id" class="form-label">{{ __trans('new_designation') }}</label>
                            <select name="new_designation_id" id="new_designation_id" class="form-control select2" required>
                                <option value="">{{ __trans('select_new_designation') }}</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}" {{ $userPromotion->new_designation_id == $designation->id ? 'selected' : '' }}>
                                        {{ $designation->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Promotion Date -->
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="promotion_date" class="form-label">{{ __trans('promotion_date') }}</label>
                            <input type="date" name="promotion_date" id="promotion_date" class="form-control"
                                   value="{{ $userPromotion->promotion_date }}" required>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="remarks" class="form-label">{{ __trans('remarks') }}</label>
                            <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="{{ __trans('optional') }}">{{ $userPromotion->remarks }}</textarea>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">{{ __trans('close') }}</button>
                <button type="submit" class="btn btn-info waves-effect waves-light">{{ __trans('update') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
    initselect2search();
</script>
