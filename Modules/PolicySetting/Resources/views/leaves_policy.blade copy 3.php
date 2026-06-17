<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{__trans('leave_policy')}}</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
            <div class="container mt-4">
                <!-- Tabs for UAE and KSA -->
                <ul class="nav nav-tabs mb-3" id="leaveTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="uae-tab" data-bs-toggle="tab" data-bs-target="#uae"
                            type="button" role="tab" aria-controls="uae" aria-selected="true">UAE</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ksa-tab" data-bs-toggle="tab" data-bs-target="#ksa" type="button"
                            role="tab" aria-controls="ksa" aria-selected="false">KSA</button>
                    </li>
                </ul>

                <div class="tab-content" id="leaveTabsContent">
                    <!-- UAE Tab Content -->
                    <div class="tab-pane fade show active" id="uae" role="tabpanel" aria-labelledby="uae-tab">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card text-center shadow-sm border-0" data-bs-toggle="modal"
                                    data-bs-target="#policyModal">
                                    <div class="card-body">
                                        <h5 class="card-title">Vacation</h5>
                                        <p class="card-text">An employee is entitled...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center shadow-sm border-0" data-bs-toggle="modal"
                                    data-bs-target="#policyModal">
                                    <div class="card-body">

                                        <h5 class="card-title">Sick</h5>
                                        <p class="card-text">An employee is entitled...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center shadow-sm border-0" data-bs-toggle="modal"
                                    data-bs-target="#policyModal">
                                    <div class="card-body">

                                        <h5 class="card-title">Maternity</h5>
                                        <p class="card-text">A female employee is entitled...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center shadow-sm border-0" data-bs-toggle="modal"
                                    data-bs-target="#policyModal">
                                    <div class="card-body">

                                        <h5 class="card-title">Parental</h5>
                                        <p class="card-text">An employee is entitled...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center shadow-sm border-0" data-bs-toggle="modal"
                                    data-bs-target="#policyModal">
                                    <div class="card-body">

                                        <h5 class="card-title">Hajj</h5>
                                        <p class="card-text">An employee may be granted...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center shadow-sm border-0" data-bs-toggle="modal"
                                    data-bs-target="#policyModal">
                                    <div class="card-body">
                                        <h5 class="card-title">Study</h5>
                                        <p class="card-text">An employee who is study...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center shadow-sm border-0" data-bs-toggle="modal"
                                    data-bs-target="#policyModal">
                                    <div class="card-body">
                                        <h5 class="card-title">Compassionate</h5>
                                        <p class="card-text">An employee is entitled...</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card text-center shadow-sm border-0" data-bs-toggle="modal"
                                    data-bs-target="#policyModal">
                                    <div class="card-body">
                                        <h5 class="card-title">Sabbatical</h5>
                                        <p class="card-text">An per 'UAE Labour Law'...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KSA Tab Content -->
                    <div class="tab-pane fade" id="ksa" role="tabpanel" aria-labelledby="ksa-tab">
                        <div class="row g-3">
                            <!-- KSA-specific content can go here -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="policyModal" tabindex="-1" aria-labelledby="policyModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="policyModalLabel">Policy Details - Vacation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="container mt-4">
                                <!-- Tabs for UAE and KSA -->
                                <ul class="nav nav-tabs mb-3" id="policyTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="uae-tab" data-bs-toggle="tab"
                                            data-bs-target="#uae2" type="button" role="tab" aria-controls="uae"
                                            aria-selected="true">UAE</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="ksa-tab" data-bs-toggle="tab"
                                            data-bs-target="#ksa2" type="button" role="tab" aria-controls="ksa"
                                            aria-selected="false">KSA</button>
                                    </li>
                                </ul>

                                <!-- Tab Content Wrapper -->
                                <div class="tab-content" id="policyTabsContent">
                                    <!-- UAE Tab Content -->
                                    <div class="tab-pane fade show active" id="uae2" role="tabpanel"
                                        aria-labelledby="uae-tab">
                                        <div class="row">
                                            <!-- Sidebar -->
                                            <div class="col-md-3">
                                                <div class="list-group" id="policyList">
                                                    <button type="button"
                                                        class="list-group-item list-group-item-action active"
                                                        data-policy="vacation">Vacation</button>
                                                    <button type="button" class="list-group-item list-group-item-action"
                                                        data-policy="sick">Sick</button>
                                                    <button type="button" class="list-group-item list-group-item-action"
                                                        data-policy="maternity">Maternity</button>
                                                    <button type="button" class="list-group-item list-group-item-action"
                                                        data-policy="parental">Parental</button>
                                                    <button type="button" class="list-group-item list-group-item-action"
                                                        data-policy="hajj">Hajj</button>
                                                    <button type="button" class="list-group-item list-group-item-action"
                                                        data-policy="study">Study</button>
                                                    <button type="button" class="list-group-item list-group-item-action"
                                                        data-policy="compassionate">Compassionate</button>
                                                    <button type="button" class="list-group-item list-group-item-action"
                                                        data-policy="sabbatical">Sabbatical</button>
                                                </div>
                                            </div>

                                            <!-- Main Content -->
                                            <div class="col-md-9">
                                                <div class="alert alert-info" role="alert" id="policyAlert">
                                                    This template is compliant with labor law guidelines in United Arab
                                                    Emirates.
                                                </div>

                                                <!-- Policy Details -->
                                                <div id="policyDetails">
                                                    <h5>Policy Details</h5>
                                                    <div class="mb-2">
                                                        <strong>Policy Name:</strong> <span
                                                            id="policyName">Vacation</span>
                                                    </div>
                                                    <div>
                                                        <strong>Description:</strong>
                                                        <p id="policyDescription">
                                                            An employee is entitled to a fully paid annual leave of 30
                                                            days, if they have completed one year of service and 2 days
                                                            per month, if they have completed six months of service, but
                                                            not one year.
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Accordion for Additional Details -->
                                                <div class="accordion" id="policyDetailsAccordion">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="allowanceHeading">
                                                            <button class="accordion-button" type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#leaveAllowance" aria-expanded="true"
                                                                aria-controls="leaveAllowance">
                                                                Leave Allowance
                                                            </button>
                                                        </h2>
                                                        <div id="leaveAllowance"
                                                            class="accordion-collapse collapse show"
                                                            aria-labelledby="allowanceHeading"
                                                            data-bs-parent="#policyDetailsAccordion">
                                                            <div class="accordion-body">
                                                                Details about leave allowance go here.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="payRateHeading">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#leavePayRate"
                                                                aria-expanded="false" aria-controls="leavePayRate">
                                                                Leave Pay Rate
                                                            </button>
                                                        </h2>
                                                        <div id="leavePayRate" class="accordion-collapse collapse"
                                                            aria-labelledby="payRateHeading"
                                                            data-bs-parent="#policyDetailsAccordion">
                                                            <div class="accordion-body">
                                                                Details about leave pay rate go here.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="restrictionsHeading">
                                                            <button class="accordion-button collapsed" type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#policyRestrictions"
                                                                aria-expanded="false"
                                                                aria-controls="policyRestrictions">
                                                                Policy Restrictions
                                                            </button>
                                                        </h2>
                                                        <div id="policyRestrictions" class="accordion-collapse collapse"
                                                            aria-labelledby="restrictionsHeading"
                                                            data-bs-parent="#policyDetailsAccordion">
                                                            <div class="accordion-body">
                                                                Details about policy restrictions go here.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- KSA Tab Content -->
                                    <div class="tab-pane fade" id="ksa2" role="tabpanel" aria-labelledby="ksa-tab">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="alert alert-warning" role="alert">
                                                    This section is specific to labor law guidelines in Saudi Arabia.
                                                </div>
                                                <!-- KSA-specific content can go here -->
                                                <h5>Policy Details for KSA</h5>
                                                <p>Content for KSA-specific labor policies can be added here.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>