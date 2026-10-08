@php
    $mailSummaryAccount = $account ?? null;
    $mailAccounts = collect($mailAccounts ?? []);
    $selectedMailAccountId = $selectedMailAccountId ?? $mailSummaryAccount?->id;
    $mailSummaryEmail = $mailSummaryAccount?->email ?? '-';
    $mailSummaryStorage = $mailStorageLabel ?? '-';
    $mailSummaryInboxCount = $totalInboxCount ?? null;
    $mailSummaryUnreadCount = $unreadCount ?? null;
    $mailSummaryFolder = $folder ?? 'inbox';
    $mailRoutePrefix = $mailRoutePrefix ?? 'applicant.email';
@endphp

<div class="row">
    <div class="col-md-6 col-sm-6">
        <div class="card avtivity-card mail-account-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <span class="avatar avatar-lg avatar-success rounded-circle border-0">
                        <i class="fa-regular fa-envelope fs-3"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="fs-14 mb-2">Email Account</p>
                        <div class="mail-account-picker" data-mail-account-picker>
                            <button type="button" class="mail-account-trigger title fs-20 fw-semibold" data-mail-account-trigger aria-label="Pilih email account">
                                <span>{{ $mailSummaryEmail }}</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <div class="mail-account-menu d-none" data-mail-account-menu>
                                <form method="POST" action="{{ route($mailRoutePrefix.'.select') }}">
                                    @csrf
                                    <input type="hidden" name="folder" value="{{ $mailSummaryFolder }}">
                                    <select name="mail_access_account_id" class="form-control mail-account-select2 js-skip-selectpicker" data-placeholder="Cari email account" aria-label="Pilih email account">
                                        <option value=""></option>
                                        @foreach ($mailAccounts as $mailAccountOption)
                                            <option value="{{ $mailAccountOption->id }}" @selected((string) $selectedMailAccountId === (string) $mailAccountOption->id)>
                                                {{ $mailAccountOption->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                    <div class="progress-bar bg-success position-absolute rounded bottom-0" style="width: 100%; height:5px;" aria-label="Email account active" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-6">
        <div class="card overflow-hidden avtivity-card">
            <div class="card-body">
                <div class="d-flex gap-md-4 gap-3 align-items-center">
                    <span class="avatar avatar-lg avatar-secondary rounded-circle border-0">
                        <i class="fa-solid fa-database fs-3"></i>
                    </span>
                    <div>
                        <p class="fs-14 mb-2">Storage Email</p>
                        <span class="title text-black fs-28 fw-semibold">{{ $mailSummaryStorage }}</span>
                    </div>
                </div>
                <div class="progress position-absolute bottom-0 start-0 w-100" style="height:5px;">
                    <div class="progress-bar rounded bg-secondary" style="width: 100%; height:5px;" aria-label="Email storage usage" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
