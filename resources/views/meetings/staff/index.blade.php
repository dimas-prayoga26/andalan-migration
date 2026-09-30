@extends('layouts.main')

@section('title', 'Zoom Meeting')

@section('navbarTitle', 'Zoom Meeting')

@section('css')
<style>
    .meeting-section {
        margin-bottom: 2rem;
    }

    .meeting-section-header {
        margin-bottom: 1rem;
    }

    .meeting-card-meta {
        display: grid;
        gap: 0.35rem;
        color: #6e7a8a;
        font-size: 0.925rem;
    }

    .meeting-card-meta i {
        color: #2444c3;
        width: 1.15rem;
    }

    .meeting-card-empty {
        border: 1px dashed #dfe3ec;
        border-radius: 0.75rem;
        color: #7b8495;
        padding: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="row meeting-section">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center meeting-section-header">
            <h5 class="mb-0">Scheduled</h5>
        </div>
    </div>

    @forelse ($scheduledMeetings as $meeting)
        <div class="col-xxl-3 col-xl-4 col-sm-6">
            <div class="card overflow-hidden" data-meeting-card="{{ $meeting['id'] }}">
                <div class="card-body p-xxl-4">
                    <div class="d-flex justify-content-between mb-4 align-items-center gap-2">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm p-2">
                                <img src="{{ asset('assets/images/logo/large/zoom.png') }}" width="35" alt="">
                            </div>
                            <div class="ms-3">
                                <span class="d-block">{{ $meeting['title'] }}</span>
                                <small class="text-muted">{{ $meeting['subtitle'] }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-square btn-primary light btn-sm">
                            <i class="bi bi-grid"></i>
                        </button>
                    </div>
                    <h2 class="fw-semibold pt-1 mb-3">{{ $meeting['date_time'] }}</h2>
                    <div class="meeting-card-meta">
                        <span>
                            <i class="bi bi-people me-1"></i>
                            <span class="js-meeting-joined-count" data-meeting-id="{{ $meeting['id'] }}">{{ $meeting['joined_count'] }} Staff Joined</span>
                        </span>
                        <span>
                            <i class="bi bi-check2-circle me-1"></i>
                            {{ $meeting['task_count'] }} Tasks
                        </span>
                    </div>
                </div>
                @if ($meeting['meeting_link'])
                    <form method="POST" action="{{ $meeting['join_url'] }}" target="_blank" class="m-3 mt-0 mb-2 js-join-meeting-form" data-meeting-id="{{ $meeting['id'] }}">
                        @csrf
                        <button type="submit" class="btn light btn-primary w-100 btn-lg js-join-meeting-button">Join Now</button>
                    </form>
                @else
                    <button type="button" class="btn light btn-primary mt-0 m-3 mb-2 btn-lg" disabled>Join Now</button>
                @endif
                <div class="mb-3"></div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="meeting-card-empty">Belum ada meeting terjadwal.</div>
        </div>
    @endforelse
</div>

<div class="row meeting-section">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center meeting-section-header">
            <h5 class="mb-0">Completed</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#completedMeetingFilterModal">
                <i class="bi bi-sliders me-2"></i>{{ $completedMonthLabel }} {{ $completedYear }}
            </button>
        </div>
    </div>

    @forelse ($completedMeetings as $meeting)
        <div class="col-xxl-3 col-xl-4 col-sm-6">
            <div class="card overflow-hidden">
                <div class="card-body p-xxl-4">
                    <div class="d-flex justify-content-between mb-4 align-items-center gap-2">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm p-2">
                                <img src="{{ asset('assets/images/logo/large/zoom.png') }}" width="35" alt="">
                            </div>
                            <div class="ms-3">
                                <span class="d-block">{{ $meeting['title'] }}</span>
                                <small class="text-muted">{{ $meeting['subtitle'] }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-square btn-success light btn-sm">
                            <i class="bi bi-grid"></i>
                        </button>
                    </div>
                    <h2 class="fw-semibold pt-1 mb-3">{{ $meeting['date_time'] }}</h2>
                    <div class="meeting-card-meta">
                        <span>
                            <i class="bi bi-people me-1"></i>
                            {{ $meeting['joined_count'] }} Staff Joined
                        </span>
                        <span>
                            <i class="bi bi-check2-circle me-1"></i>
                            {{ $meeting['task_count'] }} Tasks
                        </span>
                    </div>
                </div>
                <a href="{{ $meeting['details_url'] }}" class="btn light btn-success mt-0 m-3 mb-2 btn-lg">Meeting Details</a>
                <div class="mb-3"></div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="meeting-card-empty">Belum ada meeting yang selesai.</div>
        </div>
    @endforelse
</div>

<div class="modal fade" id="completedMeetingFilterModal" tabindex="-1" aria-labelledby="completedMeetingFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="GET" action="{{ route('zoom-meeting.index') }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="completedMeetingFilterModalLabel">Filter Completed Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="completed_month">Month</label>
                    <select class="form-select mb-3" id="completed_month" name="completed_month">
                        @foreach ($monthOptions as $monthNumber => $monthName)
                            <option value="{{ $monthNumber }}" @selected((int) $completedMonth === (int) $monthNumber)>{{ $monthName }}</option>
                        @endforeach
                    </select>

                    <label class="form-label" for="completed_year">Year</label>
                    <select class="form-select" id="completed_year" name="completed_year">
                        @foreach ($yearOptions as $yearOption)
                            <option value="{{ $yearOption }}" @selected((int) $completedYear === (int) $yearOption)>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn light btn-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form.matches('.js-join-meeting-form')) {
            return;
        }

        event.preventDefault();

        var button = form.querySelector('.js-join-meeting-button');
        var originalText = button ? button.textContent : '';
        var meetingWindow = window.open('', '_blank');

        if (button) {
            button.disabled = true;
            button.textContent = 'Joining...';
        }

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    if (!response.ok) {
                        throw payload;
                    }

                    return payload;
                });
            })
            .then(function (payload) {
                document.querySelectorAll('.js-meeting-joined-count[data-meeting-id="' + payload.meeting_id + '"]').forEach(function (element) {
                    element.textContent = payload.joined_label;
                });

                if (meetingWindow && payload.meeting_link) {
                    meetingWindow.location.href = payload.meeting_link;
                    return;
                }

                if (payload.meeting_link) {
                    window.open(payload.meeting_link, '_blank', 'noopener');
                }
            })
            .catch(function (error) {
                if (meetingWindow) {
                    meetingWindow.close();
                }

                alert(error && error.message ? error.message : 'Gagal join meeting.');
            })
            .finally(function () {
                if (button) {
                    button.disabled = false;
                    button.textContent = originalText || 'Join Now';
                }
            });
    });
</script>
@endsection
