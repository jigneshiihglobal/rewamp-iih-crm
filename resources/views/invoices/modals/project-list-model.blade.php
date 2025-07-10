<div class="modal fade" id="project_log_list_model" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered notes-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Project List</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="d-flex">
                    <div class="flex-fill me-2 w-50">
                        <label for="project_list" class="form-label">Project List:</label>
                        <select id="select_project_list" class="form-select select_project_list select2"
                            aria-label="Project List">
                            <option value="" disabled selected>Select a project</option>
                        </select>
                    </div>
                    <div class="flex-fill me-2 w-50">
                        <label for="dateRange" class="form-label">Select Date Range:</label>
                        <input type="text" id="dateRange" class="form-control select_date_range"
                            placeholder="Select date range" />
                    </div>
                </div>

                <!-- Nav tabs -->
                <ul class="nav nav-pills nav-fill mt-2" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="project-log-tab" data-bs-toggle="tab"
                            data-bs-target="#projectLog" type="button" role="tab" aria-controls="projectLog"
                            aria-selected="true">
                            Listing View
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="project-graph-tab" data-bs-toggle="tab"
                            data-bs-target="#projectGraph" type="button" role="tab" aria-controls="projectGraph"
                            aria-selected="false">
                            Graph View
                        </button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content d-flex" id="myTabContent">
                    <div class="tab-pane fade show active flex-fill p-0" id="projectLog" role="tabpanel"
                        aria-labelledby="project-log-tab">
                        <div class="project-log-title">
                            <div class="project-log-date">
                                <h6>Date</h6>
                            </div>
                            <div class="project-log-date">
                                <h6>Time</h6>
                            </div>
                        </div>
                        <div id="projectLogListModal"></div> <!-- Container for logs -->
                        <div id="employeeDetails" class="mt-3" style="display: none;">
                            <h6>Employee Details</h6>
                            <div id="employeeDetailContent"></div>
                        </div>
                    </div>
                    <div class="tab-pane fade flex-fill p-1" id="projectGraph" role="tabpanel"
                        aria-labelledby="project-graph-tab">
                        <div id="projectLogGraph"></div> <!-- Container for logs -->
                        {{-- <h6>Project Graph</h6> --}}
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
