<div class="modal-dialog modal-lg">
    <div class="modal-content" style="border: var(--bs-modal-border-width) solid var(--bs-modal-border-color);
    border-radius: var(--bs-modal-border-radius);">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('employee_payslip')}}</h4>
            <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
        </div>
        <form action="{{route('backend.payroll.user.user-salaries.storeovertime',$user)}}" datatable="true" method="POST" class="ajax-form-submit reset">
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="text-md-end mb-2">
                        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Download" onclick="saveAsPDF()"><span class="fa fa-download"></span></a>
                        <!-- <a title="Mail Send" href="#" class="btn btn-sm btn-warning"><span class="fa fa-paper-plane"></span></a> -->
                    </div>
                    <div class="invoice" id="printableArea">
                    {!! $template !!}
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<script>
    loadAjaxSelect2();
    initselect2search();
</script>
<script src="{{asset('assets/backend/js/gklveshel.js')}}"></script>
<script>
    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: 'PaySlip',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A4'
            }
        };
        html2pdf().set(opt).from(element).save();
    }

    function onChangeOvertime(data) {
        var type = '';
        type = data.options[data.selectedIndex].text;
        switch (type) {
            case 'OT1':
                $('#rate , #rateperhour').val('1.25');
                break;
            case 'OT2':
                $('#rate , #rateperhour').val('1.25');
                break;
            case 'OT3':
                $('#rate , #rateperhour').val('1.50');
                break;
            case 'OT4':
                $('#rate , #rateperhour').val('1.50');
                break;
            default:
                $('#rate , #rateperhour').val('0');
        }
    }
</script>