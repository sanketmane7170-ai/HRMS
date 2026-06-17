@extends('layouts.backend')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="fw-bold text-primary mb-0">Increment Letter</h4>
            <button onclick="window.print()" class="btn btn-outline-success d-print-none">
                <i class="fas fa-download me-1"></i> Download PDF
            </button>
        </div>

        <div class="card mt-3 shadow-sm bg-white border-0">
            <div class="card-body p-5 text-dark" id="increment-letter-content">
                <div class="text-center mb-4">
                    <h5 class="fw-bold text-uppercase text-dark">Performance Evaluation Result</h5>
                    <p class="mb-0">This is to inform you about your performance-based salary increment.</p>
                </div>


                <!-- HR Criteria -->
                <div class="mb-4">
                    <h5 class="text-dark fw-bold">HR Evaluation</h5>
                    <h6><strong>Total HR Avg Score:</strong>
                        {{ $hrAvgScore !== null ? number_format($hrAvgScore, 2) : 'Pending' }} / 10
                    </h6>
                    <h6><strong>HR Increment Level:</strong>
                        {{ $hrCriteria?->label ?? 'Pending' }}
                    </h6>
                    <h6><strong>Total Increment %:</strong>
                        {{ isset($hrCriteria->increment_percent) ? $hrCriteria->increment_percent . '%' : '0%' }}
                    </h6>

                    <h6 class="fw-bold mt-3 mb-2 text-dark">HR Increment Breakdown</h6>
                    <table class="table table-bordered light">
                        <thead class="table-light">
                            <tr>
                                <th>Component</th>
                                <th class="text-end">Old</th>
                                <th class="text-end">Increment %</th>
                                <th class="text-end">Increment Amt</th>
                                <th class="text-end">New</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['basic', 'housing', 'transport', 'other', 'incentive'] as $field)
                            <tr>
                                <td>{{ ucfirst($field) }}</td>
                                <td class="text-end">₹{{ number_format($salaryValues[$field], 2) }}</td>
                                <td class="text-end">{{ $percentages[$field] ?? 0 }}%</td>
                                <td class="text-end">₹{{ number_format($increments[$field], 2) }}</td>
                                <td class="text-end">₹{{ number_format($newAmounts[$field], 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="table-success fw-bold">
                                <td>Total</td>
                                <td class="text-end">₹{{ number_format(array_sum($salaryValues), 2) }}</td>
                                <td class="text-end">{{ $percentages['total'] ?? 0 }}%</td>
                                <td class="text-end">₹{{ number_format(array_sum($increments), 2) }}</td>
                                <td class="text-end">₹{{ number_format(array_sum($newAmounts), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>


                <p class="mt-4">
                    Please consider this as a formal notification of your updated salary components based on your performance.
                    Kindly reach out to HR for more details.
                </p>

                <div class="mt-5 text-end">
                    <p class="mb-1"><strong>Authorized By:</strong></p>
                    <p class="text-muted">Human Resources Department</p>
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