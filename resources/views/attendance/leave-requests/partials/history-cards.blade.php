<div class="row leave-history-mobile-slider" id="leaveHistoryCardsSlider">
    @forelse (($leaveHistoryCards ?? collect()) as $leaveHistoryCard)
        <div class="col-xxl-3 col-xl-4 col-sm-6 leave-history-mobile-slide">
            <div class="card">
                <div class="card-body">
                    <div class="clearfix d-flex">
                        <div class="avatar avatar-sm rounded me-3 p-2">
                            <img src="{{ asset('assets/images/logo/figma.avif') }}" alt="Leave Type">
                        </div>
                        <div class="clearfix">
                            <h6 class="mb-0 fw-semibold">{{ $leaveHistoryCard['title'] ?? 'Leave Request' }}</h6>
                            <span class="small">{{ $leaveHistoryCard['period_label'] ?? '-' }}</span>
                        </div>
                    </div>
                    <p class="my-3">{{ $leaveHistoryCard['reason'] ?? '-' }}</p>
                    <div class="widget-timeline1 leave-history-timeline">
                        <ul class="timeline">
                            @foreach (($leaveHistoryCard['timeline'] ?? []) as $timelineItem)
                                <li>
                                    <span class="timeline-status">
                                        @if (($timelineItem['date_label'] ?? '') === 'Waiting')
                                            <span class="badge badge-sm badge-secondary light leave-history-waiting-badge">Waiting</span>
                                        @else
                                            {{ $timelineItem['date_label'] ?? '' }}
                                        @endif
                                    </span>
                                    <div class="timeline-badge {{ $timelineItem['badge_class'] ?? 'border-dark' }}"></div>
                                    <div class="timeline-panel">
                                        <span>{{ $timelineItem['title'] ?? '-' }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between flex-wrap">
                    <p class="mb-0 fw-medium">Due <span class="text-purple">: {{ $leaveHistoryCard['due_date_label'] ?? '-' }}</span></p>
                    <span class="badge badge-sm {{ $leaveHistoryCard['status_badge_class'] ?? 'badge-primary light' }}">
                        {{ $leaveHistoryCard['status_label'] ?? 'Pending' }}
                    </span>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <span class="text-gray">Belum ada history leave request.</span>
                </div>
            </div>
        </div>
    @endforelse
</div>
