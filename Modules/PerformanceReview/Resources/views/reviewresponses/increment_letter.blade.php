@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="card mt-3 shadow-sm bg-white border-0">
            <div class="card-body p-5 text-dark" id="increment-letter-content" style="font-family: 'Georgia', serif;">

                <!-- Download Button -->
                <div class="d-flex justify-content-end mb-3 d-print-none">
                    <button onclick="window.print()" class="btn btn-outline-success">
                        <i class="fas fa-download me-1"></i> Download PDF
                    </button>
                </div>

                <!-- Date -->
                <div class="mb-4 text-end">
                    <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($effectiveDate)->format('d-F-Y') }}</p>
                </div>

                <!-- Recipient -->
                <div class="mb-4">
                    <p><strong>To:</strong> {{ $user->name ?? 'Employee Name' }}</p>
                </div>

                <!-- Subject -->
                <h5 class="fw-bold mb-3 text-uppercase">RE: Probationary Performance Review</h5>

                <!-- Salutation -->
                <p class="mb-4">Dear {{ $user->name ?? 'Employee' }},</p>

                <!-- Opening Statement -->
                <p class="mb-3">
                    Following your probationary performance review & in accordance with our current
                    remuneration structures, we are pleased to inform you that your gross monthly
                    remuneration package and title have been revised as below, effective from {{ \Carbon\Carbon::parse($effectiveDate)->format('d-F-Y') }}.
                </p>

                <!-- Salary Structure Table -->
                <h6 class="fw-bold mt-4 mb-2">Description & Compensation</h6>

                <table class="table table-bordered light">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Existing</th>
                            <th class="text-end">Revised</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($designationBefore) && !empty($designationAfter))
                        <tr>
                            <td>Designation</td>
                            <td class="text-end">{{ $designationBefore }}</td>
                            <td class="text-end">{{ $designationAfter }}</td>
                        </tr>
                        @endif

                        @foreach($salaryComponents as $label => $value)
                        <tr>
                            <td>{{ ucfirst($label) }}</td>
                            <td class="text-end">AED {{ number_format($value['existing'], 2) }}</td>
                            <td class="text-end">AED {{ number_format($value['revised'], 2) }}</td>
                        </tr>
                        @endforeach

                        <tr class="table-success fw-bold">
                            <td>Total (AED)</td>
                            <td class="text-end">AED {{ number_format(collect($salaryComponents)->sum('existing'), 2) }}</td>
                            <td class="text-end">AED {{ number_format(collect($salaryComponents)->sum('revised'), 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Closing Statements -->
                <p class="mt-4">
                    We hope to see a continued high standard of service from you in the years ahead and we
                    continue to hope to provide sustainable career development opportunities, wishing you a
                    successful career with us.
                </p>

                <p class="mt-3">
                    We are glad to have you as a part of our team and look forward to future growth together.
                </p>

                <!-- Signatures -->
                <div class="row mt-5">
                    <div class="col-md-6">
                        <p class="mb-0"><strong>Sr. Manager – HR & Employee Services</strong></p>
                        <p class="text-muted">(Aditi Sharma)</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-0"><strong>Founder & Chief Taster</strong></p>
                        <p class="text-muted">(Peter Samaha)</p>
                    </div>
                </div>

                <!-- Acknowledgement -->
                <div class="mt-5">
                    <h6 class="fw-bold">Acknowledgement:</h6>
                    <p>Signature: ______________________</p>
                    <p>Date: ___________________________</p>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Print Styles -->
<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #increment-letter-content,
        #increment-letter-content * {
            visibility: visible;
        }

        #increment-letter-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .d-print-none {
            display: none !important;
        }
    }
</style>
@endsection
