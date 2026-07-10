<div class="project-kanban-page">
    <div class="row kanban-bx">
        <div class="col">
            <div class="kanbanPreview-bx">
                <div class="sub-card align-items-center d-flex justify-content-between mb-4">
                    <div>
                        <h4 class="fs-20 mb-0 font-w600">To-Do List (<span class="totalCount">3</span>)</h4>
                    </div>
                    <div class="plus-bx">
                        <a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
                    </div>
                </div>

                <div class="draggable-zone dropzoneContainer">
                    <div class="card draggable-handle draggable">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="sub-title">
                                    <svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="5" cy="5" r="5" fill="#FFA7D7"/>
                                    </svg>
                                    Designer
                                </span>
                                <div class="dropdown">
                                    <div class="btn-link" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis-h text-muted"></i>
                                    </div>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="javascript:void(0)">View</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Edit</a>
                                    </div>
                                </div>
                            </div>
                            <p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Create wireframe for landing page phase 1</a></p>
                            <div class="progress default-progress my-4">
                                <div class="progress-bar bg-design progress-animated" style="width: 45%; height:10px;" role="progressbar">
                                    <span class="sr-only">45% Complete</span>
                                </div>
                            </div>
                            <div class="row justify-content-between align-items-center kanban-user">
                                <ul class="users col-6">
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
                                </ul>
                                <div class="col-6 d-flex justify-content-end">
                                    <span class="fs-14"><i class="far fa-clock me-2"></i>Due in 4 days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card draggable-handle draggable">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-warning">
                                    <svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="5" cy="5" r="5" fill="#FFCF6D"/>
                                    </svg>
                                    Important
                                </span>
                                <div class="dropdown">
                                    <div class="btn-link" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis-h text-muted"></i>
                                    </div>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="javascript:void(0)">View</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Edit</a>
                                    </div>
                                </div>
                            </div>
                            <p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Visual graphic for presentation to client</a></p>
                            <div class="progress default-progress my-4">
                                <div class="progress-bar bg-warning progress-animated" style="width: 30%; height:10px;" role="progressbar">
                                    <span class="sr-only">30% Complete</span>
                                </div>
                            </div>
                            <div class="row justify-content-between align-items-center kanban-user">
                                <ul class="users col-6">
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
                                </ul>
                                <div class="col-6 d-flex justify-content-end">
                                    <span class="fs-14"><i class="far fa-clock me-2"></i>Due in 6 days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card draggable-handle draggable">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-success">
                                    <svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="5" cy="5" r="5" fill="#09BD3C"/>
                                    </svg>
                                    Database
                                </span>
                                <div class="dropdown">
                                    <div class="btn-link" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis-h text-muted"></i>
                                    </div>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="javascript:void(0)">View</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Edit</a>
                                    </div>
                                </div>
                            </div>
                            <p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Setup database for API connection</a></p>
                            <div class="progress default-progress my-4">
                                <div class="progress-bar bg-success progress-animated" style="width: 25%; height:10px;" role="progressbar">
                                    <span class="sr-only">25% Complete</span>
                                </div>
                            </div>
                            <div class="row justify-content-between align-items-center kanban-user">
                                <ul class="users col-6">
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic222.jpg') }}" alt=""></li>
                                </ul>
                                <div class="col-6 d-flex justify-content-end">
                                    <span class="fs-14"><i class="far fa-clock me-2"></i>Due today</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="kanbanPreview-bx">
                <div class="sub-card align-items-center d-flex justify-content-between mb-4">
                    <div>
                        <h4 class="fs-20 mb-0 font-w600">In Progress (<span class="totalCount">2</span>)</h4>
                    </div>
                    <div class="plus-bx">
                        <a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
                    </div>
                </div>

                <div class="draggable-zone dropzoneContainer">
                    <div class="card draggable-handle draggable">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-primary">
                                    <svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="5" cy="5" r="5" fill="#2f4cdd"/>
                                    </svg>
                                    Development
                                </span>
                                <div class="dropdown">
                                    <div class="btn-link" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis-h text-muted"></i>
                                    </div>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="javascript:void(0)">View</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Edit</a>
                                    </div>
                                </div>
                            </div>
                            <p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Integrate attendance recap filters</a></p>
                            <div class="progress default-progress my-4">
                                <div class="progress-bar bg-primary progress-animated" style="width: 65%; height:10px;" role="progressbar">
                                    <span class="sr-only">65% Complete</span>
                                </div>
                            </div>
                            <div class="row justify-content-between align-items-center kanban-user">
                                <ul class="users col-6">
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
                                </ul>
                                <div class="col-6 d-flex justify-content-end">
                                    <span class="fs-14"><i class="far fa-clock me-2"></i>Due in 2 days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card draggable-handle draggable">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-info">
                                    <svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="5" cy="5" r="5" fill="#13b8d7"/>
                                    </svg>
                                    Content
                                </span>
                                <div class="dropdown">
                                    <div class="btn-link" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis-h text-muted"></i>
                                    </div>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="javascript:void(0)">View</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Edit</a>
                                    </div>
                                </div>
                            </div>
                            <p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Prepare monthly campaign copy</a></p>
                            <div class="progress default-progress my-4">
                                <div class="progress-bar bg-info progress-animated" style="width: 55%; height:10px;" role="progressbar">
                                    <span class="sr-only">55% Complete</span>
                                </div>
                            </div>
                            <div class="row justify-content-between align-items-center kanban-user">
                                <ul class="users col-6">
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
                                </ul>
                                <div class="col-6 d-flex justify-content-end">
                                    <span class="fs-14"><i class="far fa-clock me-2"></i>Due tomorrow</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="kanbanPreview-bx">
                <div class="sub-card align-items-center d-flex justify-content-between mb-4">
                    <div>
                        <h4 class="fs-20 mb-0 font-w600">Review (<span class="totalCount">2</span>)</h4>
                    </div>
                    <div class="plus-bx">
                        <a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
                    </div>
                </div>

                <div class="draggable-zone dropzoneContainer">
                    <div class="card draggable-handle draggable">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-warning">
                                    <svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="5" cy="5" r="5" fill="#FFCF6D"/>
                                    </svg>
                                    QA
                                </span>
                                <div class="dropdown">
                                    <div class="btn-link" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis-h text-muted"></i>
                                    </div>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="javascript:void(0)">View</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Edit</a>
                                    </div>
                                </div>
                            </div>
                            <p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Review overtime task update flow</a></p>
                            <div class="progress default-progress my-4">
                                <div class="progress-bar bg-warning progress-animated" style="width: 85%; height:10px;" role="progressbar">
                                    <span class="sr-only">85% Complete</span>
                                </div>
                            </div>
                            <div class="row justify-content-between align-items-center kanban-user">
                                <ul class="users col-6">
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic222.jpg') }}" alt=""></li>
                                </ul>
                                <div class="col-6 d-flex justify-content-end">
                                    <span class="fs-14"><i class="far fa-clock me-2"></i>Due today</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card draggable-handle draggable">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-danger">
                                    <svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="5" cy="5" r="5" fill="#f94687"/>
                                    </svg>
                                    Fix
                                </span>
                                <div class="dropdown">
                                    <div class="btn-link" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis-h text-muted"></i>
                                    </div>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="javascript:void(0)">View</a>
                                        <a class="dropdown-item" href="javascript:void(0)">Edit</a>
                                    </div>
                                </div>
                            </div>
                            <p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Align badge status on attendance recap</a></p>
                            <div class="progress default-progress my-4">
                                <div class="progress-bar bg-danger progress-animated" style="width: 70%; height:10px;" role="progressbar">
                                    <span class="sr-only">70% Complete</span>
                                </div>
                            </div>
                            <div class="row justify-content-between align-items-center kanban-user">
                                <ul class="users col-6">
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic33.jpg') }}" alt=""></li>
                                </ul>
                                <div class="col-6 d-flex justify-content-end">
                                    <span class="fs-14"><i class="far fa-clock me-2"></i>Due in 1 day</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="kanbanPreview-bx">
                <div class="sub-card align-items-center d-flex justify-content-between mb-4">
                    <div>
                        <h4 class="fs-20 mb-0 font-w600">Done (<span class="totalCount">1</span>)</h4>
                    </div>
                    <div class="plus-bx">
                        <a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
                    </div>
                </div>

                <div class="draggable-zone dropzoneContainer">
                    <div class="card draggable-handle draggable">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-success">
                                    <svg class="me-2" width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="5" cy="5" r="5" fill="#09BD3C"/>
                                    </svg>
                                    Completed
                                </span>
                                <div class="dropdown">
                                    <div class="btn-link" data-bs-toggle="dropdown">
                                        <i class="fa fa-ellipsis-h text-muted"></i>
                                    </div>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="javascript:void(0)">View</a>
                                    </div>
                                </div>
                            </div>
                            <p class="font-w600 fs-18"><a href="javascript:void(0);" class="text-black">Task list filter and calendar sync</a></p>
                            <div class="progress default-progress my-4">
                                <div class="progress-bar bg-success progress-animated" style="width: 100%; height:10px;" role="progressbar">
                                    <span class="sr-only">100% Complete</span>
                                </div>
                            </div>
                            <div class="row justify-content-between align-items-center kanban-user">
                                <ul class="users col-6">
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic11.jpg') }}" alt=""></li>
                                    <li><img src="{{ asset('assets-workload/images/contacts/pic22.jpg') }}" alt=""></li>
                                </ul>
                                <div class="col-6 d-flex justify-content-end">
                                    <span class="fs-14"><i class="far fa-check-circle me-2"></i>Finished</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="kanbanPreview-bx">
                <div class="sub-card align-items-center d-flex justify-content-between mb-4">
                    <div>
                        <h4 class="fs-20 mb-0 font-w600">Backlog (<span class="totalCount">0</span>)</h4>
                    </div>
                    <div class="plus-bx">
                        <a href="javascript:void(0)"><i class="fas fa-plus"></i></a>
                    </div>
                </div>

                <div class="draggable-zone dropzoneContainer">
                    <div class="kanban-empty-state"></div>
                </div>
            </div>
        </div>
    </div>
</div>
