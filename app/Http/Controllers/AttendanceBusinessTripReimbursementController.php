<?php

namespace App\Http\Controllers;

use App\Models\BusinessTrip;
use App\Models\BusinessTripLifecycleLog;
use App\Models\BusinessTripReimbursement;
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

class AttendanceBusinessTripReimbursementController extends Controller
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
        $businessTrip->loadMissing('reimbursements');

        return view('staff_attendance.business-trips.create-reimbursement', [
            'businessTrip' => $businessTrip,
            'businessTripReimbursementRows' => $this->buildReimbursementFormRows($businessTrip),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, BusinessTrip $businessTrip): RedirectResponse
    {
        $validated = $request->validate([
            'reimbursement_ids' => ['array'],
            'reimbursement_ids.*' => ['nullable', 'string'],
            'reimbursement_dates' => ['required', 'array', 'min:1'],
            'reimbursement_dates.*' => ['required', 'date_format:d/m/Y'],
            'reimbursement_amounts' => ['required', 'array', 'min:1'],
            'reimbursement_amounts.*' => ['required', 'string', 'max:50'],
            'reimbursement_categories' => ['required', 'array', 'min:1'],
            'reimbursement_categories.*' => ['required', 'string', 'max:100'],
            'reimbursement_notes' => ['array'],
            'reimbursement_notes.*' => ['nullable', 'string', 'max:5000'],
            'existing_receipt_paths' => ['array'],
            'existing_receipt_paths.*' => ['nullable', 'string', 'max:255'],
            'reimbursement_receipts' => ['array'],
            'reimbursement_receipts.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $authenticatedUser = Auth::user();
        $employeeId = trim((string) ($authenticatedUser?->employee?->id ?? ''));
        if ($employeeId === '') {
            return back()
                ->withInput()
                ->withErrors(['employee' => 'Data karyawan tidak ditemukan.']);
        }

        $reimbursementRows = $this->validatedReimbursementRows($validated, $request, $businessTrip);
        if ($reimbursementRows->isEmpty()) {
            throw ValidationException::withMessages([
                'reimbursement_dates' => 'Minimal satu reimbursement wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($authenticatedUser, $businessTrip, $employeeId, $reimbursementRows): void {
            $keptReimbursementIds = [];

            foreach ($reimbursementRows as $reimbursementRow) {
                $reimbursement = $this->reimbursementForRow($businessTrip, $reimbursementRow['id']);
                $oldReceiptPath = is_string($reimbursement->receipt_path) ? trim($reimbursement->receipt_path) : '';

                $reimbursement->fill([
                    'business_trip_id' => $businessTrip->id,
                    'requested_by' => $employeeId,
                    'expense_date' => $reimbursementRow['expense_date'],
                    'category' => $reimbursementRow['category'],
                    'amount' => $reimbursementRow['amount'],
                    'notes' => $reimbursementRow['notes'],
                    'receipt_path' => $reimbursementRow['receipt_path'],
                    'status' => $reimbursement->exists ? $reimbursement->status : 'pending',
                ]);
                $reimbursement->save();

                if ($oldReceiptPath !== '' && $oldReceiptPath !== $reimbursementRow['receipt_path']) {
                    Storage::disk('public')->delete($oldReceiptPath);
                }

                $keptReimbursementIds[] = $reimbursement->id;
            }

            $businessTrip->reimbursements()
                ->whereNotIn('id', $keptReimbursementIds)
                ->get()
                ->each(function (BusinessTripReimbursement $reimbursement): void {
                    if (is_string($reimbursement->receipt_path) && trim($reimbursement->receipt_path) !== '') {
                        Storage::disk('public')->delete(trim($reimbursement->receipt_path));
                    }

                    $reimbursement->delete();
                });

            $this->markReimbursementSubmitted($businessTrip, $authenticatedUser instanceof User ? $authenticatedUser : null, $reimbursementRows);
        });

        return redirect()
            ->route('attendance.business-trips.show', $businessTrip)
            ->with('success', 'Reimbursement berhasil disimpan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BusinessTripReimbursement $businessTripReimbursement): void
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessTripReimbursement $businessTripReimbursement): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessTripReimbursement $businessTripReimbursement): void
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessTripReimbursement $businessTripReimbursement): void
    {
        //
    }

    private function buildReimbursementFormRows(BusinessTrip $businessTrip): Collection
    {
        $reimbursementRows = $businessTrip->reimbursements
            ->sortBy('expense_date')
            ->map(fn (BusinessTripReimbursement $reimbursement): array => [
                'id' => $reimbursement->id,
                'expense_date' => $reimbursement->expense_date?->format('d/m/Y') ?? '',
                'amount' => $this->formatRupiah((float) $reimbursement->amount),
                'category' => (string) $reimbursement->category,
                'notes' => (string) $reimbursement->notes,
                'receipt_path' => (string) $reimbursement->receipt_path,
                'receipt_url' => is_string($reimbursement->receipt_path) && trim($reimbursement->receipt_path) !== ''
                    ? asset('storage/'.ltrim(trim($reimbursement->receipt_path), '/'))
                    : null,
            ])
            ->values();

        if ($reimbursementRows->isNotEmpty()) {
            return $reimbursementRows;
        }

        return collect([[
            'id' => '',
            'expense_date' => '',
            'amount' => '',
            'category' => 'accommodation',
            'notes' => '',
            'receipt_path' => '',
            'receipt_url' => null,
        ]]);
    }

    private function validatedReimbursementRows(array $validated, Request $request, BusinessTrip $businessTrip): Collection
    {
        $receiptFiles = $request->file('reimbursement_receipts', []);

        return collect($validated['reimbursement_dates'])
            ->map(function (string $date, int $index) use ($validated, $receiptFiles, $businessTrip): array {
                $amount = $this->parseRupiah($validated['reimbursement_amounts'][$index] ?? '');
                $receiptPath = trim((string) ($validated['existing_receipt_paths'][$index] ?? ''));
                $receiptFile = $receiptFiles[$index] ?? null;

                if ($receiptFile !== null && $receiptFile->isValid()) {
                    $receiptPath = $this->storeReceiptFile($receiptFile, $businessTrip);
                }

                if ($receiptPath === '') {
                    throw ValidationException::withMessages([
                        'reimbursement_receipts' => 'Receipt reimbursement wajib diupload.',
                    ]);
                }

                return [
                    'id' => trim((string) ($validated['reimbursement_ids'][$index] ?? '')),
                    'expense_date' => Carbon::createFromFormat('d/m/Y', $date)->toDateString(),
                    'amount' => $amount,
                    'category' => trim((string) ($validated['reimbursement_categories'][$index] ?? '')),
                    'notes' => trim((string) ($validated['reimbursement_notes'][$index] ?? '')) ?: null,
                    'receipt_path' => $receiptPath,
                ];
            })
            ->filter(fn (array $reimbursementRow): bool => $reimbursementRow['amount'] > 0)
            ->values();
    }

    private function storeReceiptFile(mixed $receiptFile, BusinessTrip $businessTrip): string
    {
        $originalName = $receiptFile->getClientOriginalName();
        $sanitizedName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = strtolower((string) $receiptFile->getClientOriginalExtension());
        $storedFileName = now()->format('YmdHis').'_'.Str::random(8).'_'.$sanitizedName.'.'.$extension;
        $storedPath = $receiptFile->storeAs('business-trip-reimbursement-receipts/'.$businessTrip->id, $storedFileName, 'public');

        if ($storedPath === false) {
            throw ValidationException::withMessages([
                'reimbursement_receipts' => 'Gagal menyimpan receipt reimbursement.',
            ]);
        }

        return $storedPath;
    }

    private function reimbursementForRow(BusinessTrip $businessTrip, string $reimbursementId): BusinessTripReimbursement
    {
        if ($reimbursementId === '') {
            return new BusinessTripReimbursement;
        }

        return $businessTrip->reimbursements()
            ->whereKey($reimbursementId)
            ->firstOrNew();
    }

    private function markReimbursementSubmitted(BusinessTrip $businessTrip, ?User $actor, Collection $reimbursementRows): void
    {
        BusinessTripLifecycleLog::query()->updateOrCreate(
            [
                'business_trip_id' => $businessTrip->id,
                'event_key' => 'reimbursement_submitted',
            ],
            [
                'phase' => 'post_trip_settlement',
                'step_order' => 7,
                'title' => 'Reimbursement Submitted',
                'status' => 'complete',
                'actor_id' => $actor?->id,
                'happened_at' => now(),
                'metadata' => [
                    'reimbursement_count' => $reimbursementRows->count(),
                    'reimbursement_total' => $reimbursementRows->sum(fn (array $reimbursementRow): float => $reimbursementRow['amount']),
                ],
            ]
        );
    }

    private function parseRupiah(?string $value): float
    {
        $numericValue = preg_replace('/[^\d]/', '', (string) $value);

        return (float) ($numericValue ?: 0);
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp. '.number_format($amount, 0, ',', '.');
    }
}
