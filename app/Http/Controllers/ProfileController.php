<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilePasswordUpdateRequest;
use App\Http\Requests\ProfilePhotoUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\EmployeeAddress;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileController extends Controller
{
    public function index(): View
    {
        return view('profile', $this->profileData());
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User, 403);

        $validated = $request->validated();
        $authenticatedUser->loadMissing([
            'employee.profile',
            'employee.latestAddress',
        ]);

        DB::transaction(function () use ($authenticatedUser, $validated): void {
            $authenticatedUser->forceFill([
                'phone' => $this->blankToNull($validated['phone'] ?? null),
                'email' => $validated['email'],
            ])->save();

            $employee = $authenticatedUser->employee;

            if ($employee === null) {
                return;
            }

            EmployeeProfile::query()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'name' => $this->blankToNull($validated['name'] ?? null),
                    'gender' => $this->blankToNull($validated['gender'] ?? null),
                    'marital_status' => $this->blankToNull($validated['marital_status'] ?? null),
                    'date_of_birth' => $this->blankToNull($validated['birth'] ?? null),
                ],
            );

            $addressLine = $this->blankToNull($validated['address'] ?? null);
            $latestAddress = $employee->latestAddress;

            if ($latestAddress instanceof EmployeeAddress) {
                $latestAddress->forceFill(['address_line' => $addressLine])->save();

                return;
            }

            if ($addressLine !== null) {
                EmployeeAddress::query()->create([
                    'employee_id' => $employee->id,
                    'address_line' => $addressLine,
                ]);
            }
        });

        return back()->with('profile_status', 'Profile berhasil diperbarui.');
    }

    public function updatePassword(ProfilePasswordUpdateRequest $request): RedirectResponse
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User, 403);

        $validated = $request->validated();

        $authenticatedUser->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Password berhasil diperbarui. Silakan login kembali.');
    }

    public function updatePhoto(ProfilePhotoUpdateRequest $request): JsonResponse
    {
        $authenticatedUser = $request->user();

        abort_unless($authenticatedUser instanceof User, 403);

        $uploadedFile = $request->file('profile_picture');

        if (! $uploadedFile instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'profile_picture' => 'Foto profile wajib diunggah.',
            ]);
        }

        $authenticatedUser->loadMissing('employee.profile');
        $employee = $authenticatedUser->employee;

        if ($employee === null) {
            throw ValidationException::withMessages([
                'profile_picture' => 'Data employee tidak ditemukan.',
            ]);
        }

        $storedPath = $this->storeProfilePictureFile($uploadedFile, $employee->id);
        $oldProfilePicturePath = trim((string) ($employee->profile?->profile_picture_path ?? ''));

        try {
            DB::transaction(function () use ($employee, $storedPath): void {
                EmployeeProfile::query()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    ['profile_picture_path' => $storedPath],
                );
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPath);

            throw $exception;
        }

        $this->deleteStoredProfilePicture($oldProfilePicturePath, $storedPath);

        return response()->json([
            'message' => 'Foto profile berhasil diperbarui.',
            'avatar_url' => $this->avatarUrl($storedPath),
            'profile_picture_path' => $storedPath,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function profileData(): array
    {
        $profileData = [
            'profilePageAvatarUrl' => asset('assets/default_user.jpg'),
            'profilePageDisplayName' => '-',
            'profilePagePositionName' => '-',
            'profilePageEmployeeCode' => '-',
            'profilePageDepartmentName' => '-',
            'profilePageJoinDateLabel' => '-',
            'profileFormName' => '-',
            'profileFormGender' => '-',
            'profileFormMaritalStatus' => '-',
            'profileFormBirth' => '-',
            'profileFormBirthValue' => '',
            'profileFormPhone' => '-',
            'profileFormEmail' => '-',
            'profileFormCurrentPosition' => '-',
            'profileFormCompany' => '-',
            'profileFormBaseOffice' => '-',
            'profileFormAddress' => '-',
        ];

        $authenticatedUserId = Auth::id();
        $authenticatedUser = is_string($authenticatedUserId) || is_int($authenticatedUserId)
            ? User::query()
                ->select(['id', 'username', 'email', 'phone'])
                ->with([
                    'employee:id,user_id,employee_code',
                    'employee.profile:id,employee_id,name,gender,marital_status,date_of_birth,profile_picture_path',
                    'employee.deployment:id,employee_id,current_position_id,current_department_id,current_company_id,current_office_location_id,join_date',
                    'employee.deployment.company:id,name',
                    'employee.deployment.department:id,name',
                    'employee.deployment.officeLocation:id,name',
                    'employee.deployment.position:id,name',
                    'employee.deployment.positions:id,name',
                    'employee.latestAddress' => static function ($query): void {
                        $query->select([
                            'employee_addresses.id',
                            'employee_addresses.employee_id',
                            'employee_addresses.address_line',
                            'employee_addresses.created_at',
                        ]);
                    },
                ])
                ->find($authenticatedUserId)
            : null;

        if ($authenticatedUser instanceof User) {
            $profileData['profilePageAvatarUrl'] = $this->avatarUrl(
                $authenticatedUser->employee?->profile?->profile_picture_path,
            );
            $profileData['profilePageDisplayName'] = $this->displayName($authenticatedUser);
            $profileData['profilePagePositionName'] = $this->primaryPositionName($authenticatedUser);
            $profileData['profilePageEmployeeCode'] = $this->employeeCode($authenticatedUser);
            $profileData['profilePageDepartmentName'] = $this->departmentName($authenticatedUser);
            $profileData['profilePageJoinDateLabel'] = $this->joinDateLabel($authenticatedUser);
            $profileData['profileFormName'] = $this->displayName($authenticatedUser);
            $profileData['profileFormGender'] = $this->genderLabel($authenticatedUser->employee?->profile?->gender);
            $profileData['profileFormMaritalStatus'] = $this->maritalStatusLabel($authenticatedUser->employee?->profile?->marital_status);
            $profileData['profileFormBirth'] = $this->birthDateLabel($authenticatedUser);
            $profileData['profileFormBirthValue'] = $this->birthDateInputValue($authenticatedUser);
            $profileData['profileFormPhone'] = $this->profileValue($authenticatedUser->phone);
            $profileData['profileFormEmail'] = $this->profileValue($authenticatedUser->email);
            $profileData['profileFormCurrentPosition'] = $this->primaryPositionName($authenticatedUser);
            $profileData['profileFormCompany'] = $this->companyName($authenticatedUser);
            $profileData['profileFormBaseOffice'] = $this->baseOfficeName($authenticatedUser);
            $profileData['profileFormAddress'] = $this->addressLine($authenticatedUser);
        }

        return $profileData;
    }

    private function displayName(User $user): string
    {
        $employeeProfileName = trim((string) ($user->employee?->profile?->name ?? ''));
        if ($employeeProfileName !== '') {
            return $employeeProfileName;
        }

        $username = trim((string) $user->username);
        if ($username !== '') {
            return $username;
        }

        $emailName = trim((string) explode('@', (string) $user->email)[0]);

        return $emailName !== '' ? $emailName : '-';
    }

    private function primaryPositionName(User $user): string
    {
        $deployment = $user->employee?->deployment;
        $primaryPositionName = $deployment?->positions
            ?->first(static fn (Position $position): bool => (bool) ($position->pivot?->is_primary ?? false))
            ?->name;

        if (is_string($primaryPositionName) && trim($primaryPositionName) !== '') {
            return trim($primaryPositionName);
        }

        $currentPositionName = $deployment?->position?->name;

        return is_string($currentPositionName) && trim($currentPositionName) !== ''
            ? trim($currentPositionName)
            : '-';
    }

    private function employeeCode(User $user): string
    {
        $employeeCode = trim((string) ($user->employee?->employee_code ?? ''));

        return $employeeCode !== '' ? $employeeCode : '-';
    }

    private function departmentName(User $user): string
    {
        $departmentName = $user->employee?->deployment?->department?->name;

        return is_string($departmentName) && trim($departmentName) !== ''
            ? trim($departmentName)
            : '-';
    }

    private function joinDateLabel(User $user): string
    {
        $joinDate = $user->employee?->deployment?->join_date;

        return $joinDate !== null ? $joinDate->format('d M Y') : '-';
    }

    private function birthDateLabel(User $user): string
    {
        $dateOfBirth = $user->employee?->profile?->date_of_birth;

        if ($dateOfBirth instanceof Carbon) {
            return $dateOfBirth->format('d M Y');
        }

        if (is_string($dateOfBirth) && trim($dateOfBirth) !== '') {
            try {
                return Carbon::parse($dateOfBirth)->format('d M Y');
            } catch (Throwable) {
                return '-';
            }
        }

        return '-';
    }

    private function birthDateInputValue(User $user): string
    {
        $dateOfBirth = $user->employee?->profile?->date_of_birth;

        if ($dateOfBirth instanceof Carbon) {
            return $dateOfBirth->format('Y-m-d');
        }

        if (is_string($dateOfBirth) && trim($dateOfBirth) !== '') {
            try {
                return Carbon::parse($dateOfBirth)->format('Y-m-d');
            } catch (Throwable) {
                return '';
            }
        }

        return '';
    }

    private function companyName(User $user): string
    {
        $companyName = $user->employee?->deployment?->company?->name;

        return $this->profileValue($companyName);
    }

    private function baseOfficeName(User $user): string
    {
        $officeName = $user->employee?->deployment?->officeLocation?->name;

        return $this->profileValue($officeName);
    }

    private function addressLine(User $user): string
    {
        $addressLine = $user->employee?->latestAddress?->address_line;

        return $this->profileValue($addressLine);
    }

    private function maritalStatusLabel(mixed $maritalStatus): string
    {
        $maritalStatus = strtolower(trim((string) $maritalStatus));

        return match ($maritalStatus) {
            'single', 'lajang' => 'Lajang',
            'married', 'menikah' => 'Menikah',
            default => '',
        };
    }

    private function genderLabel(mixed $gender): string
    {
        $gender = strtolower(trim((string) $gender));

        return match ($gender) {
            'male' => 'Male',
            'female' => 'Female',
            default => '',
        };
    }

    private function profileValue(mixed $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : '-';
    }

    private function blankToNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function storeProfilePictureFile(UploadedFile $profilePictureFile, string $employeeId): string
    {
        $originalName = $profilePictureFile->getClientOriginalName();
        $sanitizedName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = strtolower((string) ($profilePictureFile->getClientOriginalExtension() ?: $profilePictureFile->extension()));
        $extension = $extension !== '' ? $extension : 'jpg';
        $storedFileName = now()->format('YmdHis').'_'.Str::random(8).'_'.$sanitizedName.'.'.$extension;
        $storedPath = $profilePictureFile->storeAs('profile-pictures/'.$employeeId, $storedFileName, 'public');

        if ($storedPath === false) {
            throw ValidationException::withMessages([
                'profile_picture' => 'Gagal menyimpan foto profile.',
            ]);
        }

        return $storedPath;
    }

    private function deleteStoredProfilePicture(string $oldProfilePicturePath, string $newProfilePicturePath): void
    {
        $oldProfilePicturePath = trim($oldProfilePicturePath);

        if ($oldProfilePicturePath === '' || $oldProfilePicturePath === $newProfilePicturePath) {
            return;
        }

        if (Str::startsWith($oldProfilePicturePath, ['http://', 'https://'])) {
            return;
        }

        $storagePath = ltrim($oldProfilePicturePath, '/');
        $storagePath = Str::startsWith($storagePath, 'storage/')
            ? Str::after($storagePath, 'storage/')
            : $storagePath;

        if (! Str::startsWith($storagePath, 'profile-pictures/')) {
            return;
        }

        Storage::disk('public')->delete($storagePath);
    }

    private function avatarUrl(mixed $profilePicturePath): string
    {
        $defaultAvatarUrl = asset('assets/default_user.jpg');
        $profilePicturePath = trim((string) $profilePicturePath);

        if ($profilePicturePath === '') {
            return $defaultAvatarUrl;
        }

        if (Str::startsWith($profilePicturePath, ['http://', 'https://'])) {
            return $profilePicturePath;
        }

        $publicPath = ltrim($profilePicturePath, '/');
        $storagePath = Str::startsWith($publicPath, 'storage/')
            ? Str::after($publicPath, 'storage/')
            : $publicPath;

        if (Storage::disk('public')->exists($storagePath)) {
            return asset('storage/'.$storagePath);
        }

        return File::exists(public_path($publicPath)) ? asset($publicPath) : $defaultAvatarUrl;
    }
}
