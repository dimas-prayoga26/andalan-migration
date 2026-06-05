<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\BusinessTripCashAdvance;
use App\Models\BusinessTripLifecycleLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BusinessTripCashAdvanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): void
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(BusinessTrip $businessTrip): View
    {
        $businessTrip->loadMissing('cashAdvances');

        return view('attendance.business-trips.create-cash-advance', [
            'businessTrip' => $businessTrip,
            'businessTripCashAdvanceRows' => $this->buildCashAdvanceFormRows($businessTrip),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, BusinessTrip $businessTrip): RedirectResponse
    {
        $validated = $request->validate([
            'cash_advance_ids' => ['array'],
            'cash_advance_ids.*' => ['nullable', 'string'],
            'request_dates' => ['required', 'array', 'min:1'],
            'request_dates.*' => ['required', 'string', 'max:25', 'regex:/^\d{2}\/\d{2}\/\d{4}( - \d{2}\/\d{2}\/\d{4})?$/'],
            'request_amounts' => ['required', 'array', 'min:1'],
            'request_amounts.*' => ['required', 'string', 'max:50'],
            'request_breakdowns' => ['required', 'array', 'min:1'],
            'request_breakdowns.*' => ['required', 'string', 'max:100'],
            'request_notes' => ['array'],
            'request_notes.*' => ['nullable', 'string', 'max:5000'],
            'request_amount_realized' => ['array'],
            'request_amount_realized.*' => ['nullable', 'string', 'max:50'],
            'existing_attachment_paths' => ['array'],
            'existing_attachment_paths.*' => ['nullable', 'string', 'max:255'],
            'request_attachments' => ['array'],
            'request_attachments.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $authenticatedUser = Auth::user();
        $employeeId = trim((string) ($authenticatedUser?->employee?->id ?? ''));
        if ($employeeId === '') {
            return back()
                ->withInput()
                ->withErrors(['employee' => 'Data karyawan tidak ditemukan.']);
        }

        $cashAdvanceRows = $this->validatedCashAdvanceRows($validated, $request, $businessTrip);
        if ($cashAdvanceRows->isEmpty()) {
            throw ValidationException::withMessages([
                'request_dates' => 'Minimal satu cash advance wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($authenticatedUser, $businessTrip, $cashAdvanceRows, $employeeId): void {
            $keptCashAdvanceIds = [];

            foreach ($cashAdvanceRows as $cashAdvanceRow) {
                $cashAdvance = $this->cashAdvanceForRow($businessTrip, $cashAdvanceRow['id']);
                $oldAttachmentPath = is_string($cashAdvance->attachment_path) ? trim($cashAdvance->attachment_path) : '';

                $cashAdvance->fill([
                    'business_trip_id' => $businessTrip->id,
                    'requested_by' => $employeeId,
                    'date_needed' => $cashAdvanceRow['date_needed'],
                    'date_needed_until' => $cashAdvanceRow['date_needed_until'],
                    'category' => $cashAdvanceRow['category'],
                    'amount_requested' => $cashAdvanceRow['amount_requested'],
                    'amount_realized' => $cashAdvanceRow['amount_realized'],
                    'notes' => $cashAdvanceRow['notes'],
                    'attachment_path' => $cashAdvanceRow['attachment_path'],
                    'status' => $cashAdvance->exists ? $cashAdvance->status : 'pending',
                ]);
                $cashAdvance->save();

                if ($oldAttachmentPath !== '' && $oldAttachmentPath !== $cashAdvanceRow['attachment_path']) {
                    Storage::disk('public')->delete($oldAttachmentPath);
                }

                $keptCashAdvanceIds[] = $cashAdvance->id;
            }

            $businessTrip->cashAdvances()
                ->whereNotIn('id', $keptCashAdvanceIds)
                ->get()
                ->each(function (BusinessTripCashAdvance $cashAdvance): void {
                    if (is_string($cashAdvance->attachment_path) && trim($cashAdvance->attachment_path) !== '') {
                        Storage::disk('public')->delete(trim($cashAdvance->attachment_path));
                    }

                    $cashAdvance->delete();
                });

            $this->markCashAdvanceSubmitted($businessTrip, $authenticatedUser instanceof User ? $authenticatedUser : null, $cashAdvanceRows);
            $this->syncTripReportLifecycleFromCashAdvances($businessTrip, $authenticatedUser instanceof User ? $authenticatedUser : null);
        });

        return redirect()
            ->route('attendance.business-trips.show', $businessTrip)
            ->with('success', 'Cash advance berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessTripCashAdvance $businessTripCashAdvance): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessTripCashAdvance $businessTripCashAdvance): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessTripCashAdvance $businessTripCashAdvance): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessTripCashAdvance $businessTripCashAdvance): void
    {
        //
    }

    private function buildCashAdvanceFormRows(BusinessTrip $businessTrip): Collection
    {
        $cashAdvanceRows = $businessTrip->cashAdvances
            ->sortBy('date_needed')
            ->map(function (BusinessTripCashAdvance $cashAdvance): array {
                $cashAdvanceApproved = strtolower(trim((string) $cashAdvance->status)) === 'approved';

                return [
                    'id' => $cashAdvance->id,
                    'date_needed' => $this->formatCashAdvanceDateRange($cashAdvance),
                    'finance_date' => $cashAdvance->approved_at?->format('d/m/Y') ?? '',
                    'amount_requested' => $this->formatRupiah((float) $cashAdvance->amount_requested),
                    'category' => (string) $cashAdvance->category,
                    'notes' => (string) $cashAdvance->notes,
                    'amount_realized' => $cashAdvance->amount_realized !== null ? $this->formatRupiah((float) $cashAdvance->amount_realized) : '',
                    'attachment_path' => (string) $cashAdvance->attachment_path,
                    'attachment_url' => is_string($cashAdvance->attachment_path) && trim($cashAdvance->attachment_path) !== ''
                        ? Storage::disk('public')->url(trim($cashAdvance->attachment_path))
                        : null,
                    'amount_approved' => $cashAdvance->amount_approved !== null ? $this->formatRupiah((float) $cashAdvance->amount_approved) : '',
                    'finance_notes' => (string) $cashAdvance->finance_notes,
                    'is_approved' => $cashAdvanceApproved,
                ];
            })
            ->values();

        if ($cashAdvanceRows->isNotEmpty()) {
            return $cashAdvanceRows;
        }

        return collect([[
            'id' => '',
            'date_needed' => '',
            'date_needed_until' => '',
            'finance_date' => '',
            'amount_requested' => '',
            'category' => 'accommodation',
            'notes' => '',
            'amount_realized' => '',
            'attachment_path' => '',
            'attachment_url' => null,
            'amount_approved' => '',
            'finance_notes' => '',
            'is_approved' => false,
        ]]);
    }

    private function validatedCashAdvanceRows(array $validated, Request $request, BusinessTrip $businessTrip): Collection
    {
        $attachmentFiles = $request->file('request_attachments', []);

        return collect($validated['request_dates'])
            ->map(function (string $date, int $index) use ($attachmentFiles, $businessTrip, $validated): array {
                $amountRequested = $this->parseRupiah($validated['request_amounts'][$index] ?? '');
                $dateRange = $this->parseCashAdvanceDateRange($date);
                $attachmentPath = trim((string) ($validated['existing_attachment_paths'][$index] ?? ''));
                $attachmentFile = $attachmentFiles[$index] ?? null;

                if ($attachmentFile !== null && $attachmentFile->isValid()) {
                    $attachmentPath = $this->storeAttachmentFile($attachmentFile, $businessTrip);
                }

                return [
                    'id' => trim((string) ($validated['cash_advance_ids'][$index] ?? '')),
                    'date_needed' => $dateRange['start_date'],
                    'date_needed_until' => $dateRange['end_date'],
                    'amount_requested' => $amountRequested,
                    'category' => trim((string) ($validated['request_breakdowns'][$index] ?? '')),
                    'notes' => trim((string) ($validated['request_notes'][$index] ?? '')) ?: null,
                    'amount_realized' => $this->nullableParsedRupiah($validated['request_amount_realized'][$index] ?? null),
                    'attachment_path' => $attachmentPath !== '' ? $attachmentPath : null,
                ];
            })
            ->filter(fn (array $cashAdvanceRow): bool => $cashAdvanceRow['amount_requested'] > 0)
            ->values();
    }

    /**
     * @return array{start_date: string, end_date: string}
     */
    private function parseCashAdvanceDateRange(string $dateRange): array
    {
        $dateParts = collect(explode(' - ', trim($dateRange)))
            ->map(fn (string $date): string => trim($date))
            ->filter()
            ->values();

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $dateParts->get(0, ''))->startOfDay();
            $endDate = Carbon::createFromFormat('d/m/Y', $dateParts->get(1, $dateParts->get(0, '')))->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'request_dates' => 'Format tanggal cash advance tidak valid.',
            ]);
        }

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'request_dates' => 'Tanggal akhir cash advance tidak boleh sebelum tanggal awal.',
            ]);
        }

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }

    private function storeAttachmentFile(mixed $attachmentFile, BusinessTrip $businessTrip): string
    {
        $originalName = $attachmentFile->getClientOriginalName();
        $sanitizedName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = strtolower((string) $attachmentFile->getClientOriginalExtension());
        $storedFileName = now()->format('YmdHis').'_'.Str::random(8).'_'.$sanitizedName.'.'.$extension;
        $storedPath = $attachmentFile->storeAs('business-trip-cash-advance-attachments/'.$businessTrip->id, $storedFileName, 'public');

        if ($storedPath === false) {
            throw ValidationException::withMessages([
                'request_attachments' => 'Gagal menyimpan attachment cash advance.',
            ]);
        }

        return $storedPath;
    }

    private function formatCashAdvanceDateRange(BusinessTripCashAdvance $cashAdvance): string
    {
        if ($cashAdvance->date_needed === null) {
            return '';
        }

        $startDate = $cashAdvance->date_needed->format('d/m/Y');
        $endDate = $cashAdvance->date_needed_until?->format('d/m/Y') ?? $startDate;

        return $startDate === $endDate ? $startDate : $startDate.' - '.$endDate;
    }

    private function cashAdvanceForRow(BusinessTrip $businessTrip, string $cashAdvanceId): BusinessTripCashAdvance
    {
        if ($cashAdvanceId === '') {
            return new BusinessTripCashAdvance;
        }

        return $businessTrip->cashAdvances()
            ->whereKey($cashAdvanceId)
            ->firstOrNew();
    }

    private function markCashAdvanceSubmitted(BusinessTrip $businessTrip, ?User $actor, Collection $cashAdvanceRows): void
    {
        BusinessTripLifecycleLog::query()
            ->updateOrCreate(
                [
                    'business_trip_id' => $businessTrip->id,
                    'event_key' => 'cash_advance_submitted',
                ],
                [
                    'phase' => 'pre_trip_preparation',
                    'step_order' => 3,
                    'title' => 'Cash Advance Submitted',
                    'status' => 'waiting',
                    'actor_id' => $actor?->id,
                    'happened_at' => now(),
                    'metadata' => [
                        'cash_advance_count' => $cashAdvanceRows->count(),
                        'cash_advance_total' => $cashAdvanceRows->sum(fn (array $cashAdvanceRow): float => $cashAdvanceRow['amount_requested']),
                    ],
                ]
            );
    }

    private function syncTripReportLifecycleFromCashAdvances(BusinessTrip $businessTrip, ?User $actor): void
    {
        $businessTrip->load('cashAdvances');

        if ($businessTrip->cashAdvances->isEmpty()) {
            return;
        }

        $allCashAdvanceReportsCompleted = $businessTrip->cashAdvances
            ->every(fn (BusinessTripCashAdvance $cashAdvance): bool => $cashAdvance->amount_realized !== null
                && (float) $cashAdvance->amount_realized > 0
                && trim((string) $cashAdvance->attachment_path) !== '');

        if (! $allCashAdvanceReportsCompleted) {
            return;
        }

        BusinessTripLifecycleLog::query()->updateOrCreate(
            [
                'business_trip_id' => $businessTrip->id,
                'event_key' => 'trip_report',
            ],
            [
                'phase' => 'post_trip_settlement',
                'step_order' => 6,
                'title' => 'Trip Report & Task Submitted',
                'status' => 'complete',
                'actor_id' => $actor?->id,
                'happened_at' => now(),
                'metadata' => [
                    'cash_advance_report_count' => $businessTrip->cashAdvances->count(),
                    'cash_advance_realized_total' => $businessTrip->cashAdvances->sum(fn (BusinessTripCashAdvance $cashAdvance): float => (float) $cashAdvance->amount_realized),
                ],
            ]
        );

        BusinessTripLifecycleLog::query()->updateOrCreate(
            [
                'business_trip_id' => $businessTrip->id,
                'event_key' => 'reimbursement_submitted',
            ],
            [
                'phase' => 'post_trip_settlement',
                'step_order' => 7,
                'title' => 'Reimbursement Submitted',
                'status' => 'pending',
                'actor_id' => null,
                'happened_at' => null,
                'metadata' => [
                    'ready_after_trip_report_completed_at' => now()->toDateTimeString(),
                ],
            ]
        );
    }

    private function parseRupiah(?string $value): float
    {
        $numericValue = preg_replace('/[^\d]/', '', (string) $value);

        return (float) ($numericValue ?: 0);
    }

    private function nullableParsedRupiah(?string $value): ?float
    {
        $parsedValue = $this->parseRupiah($value);

        return $parsedValue > 0 ? $parsedValue : null;
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp. '.number_format($amount, 0, ',', '.');
    }
}
