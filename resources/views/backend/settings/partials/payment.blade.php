<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col">
                <h4> {{__trans('payment_settings')}}</h4>
            </div>
            <div class="col-auto">
            </div>
        </div>
    </div>
    <div class="card-body">
        <form action="{{route('backend.settings.payment.post')}}" method="POST" enctype="multipart/form-data" class="ajax-form-submit">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('stripe_key')}}</label>
                        <input type="text" name="stripe_key" class="form-control @error('stripe_key') is-invalid @enderror" value="{{old('stripe_key',getSetting('stripe_key'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('stripe_secret')}}</label>
                        <input type="text" name="stripe_secret" class="form-control @error('stripe_secret') is-invalid @enderror" value="{{old('stripe_secret',getSetting('stripe_secret'))}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> {{__trans('stripe_currency')}}</label>
                        <input type="text" name="stripe_currency" class="form-control @error('stripe_currency') is-invalid @enderror" value="{{old('stripe_currency',getSetting('stripe_currency'))}}">
                    </div>
                </div>
            </div>
            <div class=" text-end mt-4">
                <button type="submit" class="btn btn-primary">{{__trans('save_payment_settings')}} </button>
            </div>
        </form>
    </div>
</div>
