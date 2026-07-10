<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProfilePageStructureTest extends TestCase
{
    public function test_profile_page_has_static_password_fields_without_portfolio_or_forgot_password_links(): void
    {
        $profile = File::get(resource_path('views/profile.blade.php'));
        $provider = File::get(app_path('Providers/AppServiceProvider.php'));
        $controller = File::get(app_path('Http/Controllers/ProfileController.php'));
        $photoRequest = File::get(app_path('Http/Requests/ProfilePhotoUpdateRequest.php'));
        $routes = File::get(base_path('routes/web.php'));

        $this->assertStringContainsString("Route::get('/profile', [ProfileController::class, 'index'])->name('profile');", $routes);
        $this->assertStringContainsString("Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');", $routes);
        $this->assertStringContainsString("Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');", $routes);
        $this->assertStringContainsString("Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');", $routes);
        $this->assertStringContainsString("use App\Http\Controllers\ProfileController;", $routes);
        $this->assertStringNotContainsString("View::composer('profile'", $provider);
        $this->assertStringContainsString('use App\Http\Requests\ProfilePasswordUpdateRequest;', $controller);
        $this->assertStringContainsString('use App\Http\Requests\ProfilePhotoUpdateRequest;', $controller);
        $this->assertStringContainsString('use App\Http\Requests\ProfileUpdateRequest;', $controller);
        $this->assertStringContainsString('use Illuminate\Support\Facades\Storage;', $controller);
        $this->assertStringContainsString('use Illuminate\Support\Facades\Hash;', $controller);
        $this->assertStringContainsString('use App\Models\EmployeeAddress;', $controller);
        $this->assertStringContainsString('use App\Models\EmployeeProfile;', $controller);
        $this->assertStringContainsString('public function update(ProfileUpdateRequest $request): RedirectResponse', $controller);
        $this->assertStringContainsString('public function updatePassword(ProfilePasswordUpdateRequest $request): RedirectResponse', $controller);
        $this->assertStringContainsString('public function updatePhoto(ProfilePhotoUpdateRequest $request): JsonResponse', $controller);
        $this->assertStringContainsString("Auth::guard('web')->logout();", $controller);
        $this->assertStringContainsString('$request->session()->invalidate();', $controller);
        $this->assertStringContainsString('$request->session()->regenerateToken();', $controller);
        $this->assertStringContainsString("->route('login')", $controller);
        $this->assertStringContainsString('Password berhasil diperbarui. Silakan login kembali.', $controller);
        $this->assertStringContainsString('storeProfilePictureFile', $controller);
        $this->assertStringContainsString('deleteStoredProfilePicture', $controller);
        $this->assertStringContainsString("Storage::disk('public')->delete(\$storagePath)", $controller);
        $this->assertStringContainsString("return asset('storage/'.\$storagePath);", $controller);
        $this->assertStringContainsString("'password' => Hash::make(\$validated['password'])", $controller);
        $this->assertStringContainsString('DB::transaction(function () use ($authenticatedUser, $validated): void', $controller);
        $this->assertStringContainsString('EmployeeProfile::query()->updateOrCreate', $controller);
        $this->assertStringContainsString('EmployeeAddress::query()->create', $controller);
        $this->assertStringContainsString("return view('profile', \$this->profileData());", $controller);
        $this->assertStringContainsString("asset('assets/default_user.jpg')", $controller);
        $this->assertStringContainsString('employee:id,user_id,employee_code', $controller);
        $this->assertStringContainsString("select(['id', 'username', 'email', 'phone'])", $controller);
        $this->assertStringContainsString('employee.profile:id,employee_id,name,gender,marital_status,date_of_birth,profile_picture_path', $controller);
        $this->assertStringContainsString('current_office_location_id', $controller);
        $this->assertStringContainsString('employee.deployment.company:id,name', $controller);
        $this->assertStringContainsString('employee.deployment.officeLocation:id,name', $controller);
        $this->assertStringContainsString("'employee.latestAddress' => static function", $controller);
        $this->assertStringContainsString('employee_addresses.employee_id', $controller);
        $this->assertStringContainsString('employee_addresses.address_line', $controller);
        $this->assertStringContainsString('employee.deployment.department:id,name', $controller);
        $this->assertStringContainsString('employee.deployment.positions:id,name', $controller);
        $this->assertStringContainsString('pivot?->is_primary', $controller);
        $this->assertStringContainsString('profilePageAvatarUrl', $profile);
        $this->assertStringContainsString('profilePageDisplayName', $profile);
        $this->assertStringContainsString('profilePagePositionName', $profile);
        $this->assertStringContainsString('profilePageEmployeeCode', $profile);
        $this->assertStringContainsString('profilePageDepartmentName', $profile);
        $this->assertStringContainsString('profilePageJoinDateLabel', $profile);
        foreach ([
            'profileFormName',
            'profileFormGender',
            'profileFormMaritalStatus',
            'profileFormBirth',
            'profileFormBirthValue',
            'profileFormPhone',
            'profileFormEmail',
            'profileFormCurrentPosition',
            'profileFormCompany',
            'profileFormBaseOffice',
            'profileFormAddress',
        ] as $profileFormValue) {
            $this->assertStringContainsString($profileFormValue, $profile);
        }
        $this->assertStringContainsString('.profile-summary-list .list-group-item', $profile);
        $this->assertStringContainsString('grid-template-columns: minmax(7.5rem, 42%) minmax(0, 1fr);', $profile);
        $this->assertStringContainsString('overflow-wrap: anywhere;', $profile);
        $this->assertStringContainsString('list-group list-group-flush profile-summary-list', $profile);
        $this->assertStringContainsString('profile-summary-label', $profile);
        $this->assertStringContainsString('profile-summary-value', $profile);
        $this->assertStringContainsString('Employee ID', $profile);
        $this->assertStringContainsString('Department', $profile);
        $this->assertStringContainsString('Join Date', $profile);
        $this->assertStringNotContainsString('Elijah James', $profile);
        $this->assertStringNotContainsString('assets/images/avatar/middle/avatar2.webp', $profile);
        $this->assertStringContainsString('id="profilePictureInput"', $profile);
        $this->assertStringContainsString("data-upload-url=\"{{ route('profile.photo.update') }}\"", $profile);
        $this->assertStringContainsString('data-csrf-token="{{ csrf_token() }}"', $profile);
        $this->assertStringContainsString("formData.append('profile_picture', file)", $profile);
        $this->assertStringContainsString("document.querySelectorAll('.header-profile img')", $profile);
        $this->assertStringNotContainsString('Models</a>', $profile);
        $this->assertStringNotContainsString('Gallery</a>', $profile);
        $this->assertStringNotContainsString('Lessons</a>', $profile);
        $this->assertStringContainsString('id="profileInformationForm"', $profile);
        $this->assertStringContainsString('action="{{ route(\'profile.update\') }}" method="POST"', $profile);
        $this->assertStringContainsString('@csrf', $profile);
        $this->assertStringContainsString("@method('PATCH')", $profile);
        $this->assertStringContainsString('id="profilePasswordForm"', $profile);
        $this->assertStringContainsString('action="{{ route(\'profile.password.update\') }}" method="POST"', $profile);
        $this->assertSame(2, substr_count($profile, '<form id='));
        $this->assertSame(1, substr_count($profile, '<div class="card m-b30">'));
        $this->assertStringContainsString('<h6 class="card-title">Account Setup</h6>', $profile);
        $this->assertStringContainsString('.profile-account-tabs', $profile);
        $this->assertStringContainsString('background: #f4f6fb;', $profile);
        $this->assertStringContainsString('nav nav-pills profile-account-tabs', $profile);
        $this->assertStringContainsString('id="profileAccountSetupTabs"', $profile);
        $this->assertStringContainsString('data-bs-target="#profile-update-pane"', $profile);
        $this->assertStringContainsString('Update Profile', $profile);
        $this->assertStringContainsString('data-bs-target="#password-update-pane"', $profile);
        $this->assertStringContainsString('Update Password', $profile);
        $this->assertStringContainsString('name="name"', $profile);
        $this->assertStringContainsString('name="gender"', $profile);
        $this->assertStringContainsString("['Male', 'Female']", $profile);
        $this->assertStringContainsString('@selected($profileGenderValue === $gender)', $profile);
        $this->assertStringContainsString('genderLabel', $controller);
        $this->assertStringContainsString("'male' => 'Male'", $controller);
        $this->assertStringContainsString("'female' => 'Female'", $controller);
        $this->assertStringContainsString('name="marital_status"', $profile);
        $this->assertStringContainsString('Marital Status', $profile);
        $this->assertStringContainsString("['Lajang', 'Menikah']", $profile);
        $this->assertStringContainsString('@selected($profileMaritalStatusValue === $status)', $profile);
        $this->assertStringContainsString('maritalStatusLabel', $controller);
        $this->assertStringContainsString("'single', 'lajang' => 'Lajang'", $controller);
        $this->assertStringContainsString("'married', 'menikah' => 'Menikah'", $controller);
        $this->assertStringNotContainsString("'Single', 'Married', 'Divorced', 'Widowed'", $profile);
        $this->assertStringContainsString('name="birth"', $profile);
        $this->assertStringContainsString('type="hidden" name="birth" id="profileBirthValue"', $profile);
        $this->assertStringContainsString('id="profileBirthDisplay"', $profile);
        $this->assertStringContainsString('js-profile-birth-picker', $profile);
        $this->assertStringContainsString('placeholder="DD/MM/YYYY"', $profile);
        $this->assertStringContainsString('data-date-target="#profileBirthValue"', $profile);
        $this->assertStringContainsString('$birthDisplayInput.daterangepicker(pickerOptions)', $profile);
        $this->assertStringNotContainsString('type="date" name="birth"', $profile);
        $this->assertStringContainsString('name="phone"', $profile);
        $this->assertStringContainsString('name="email"', $profile);
        $this->assertStringContainsString('name="current_position"', $profile);
        $this->assertStringContainsString('name="company"', $profile);
        $this->assertStringContainsString('name="base_office"', $profile);
        $this->assertStringContainsString('name="current_position" class="form-control" value="{{ $profileFormCurrentPosition }}" disabled aria-disabled="true" data-disabled-field="true"', $profile);
        $this->assertStringContainsString('name="company" class="form-control" value="{{ $profileFormCompany }}" disabled aria-disabled="true" data-disabled-field="true"', $profile);
        $this->assertStringContainsString('name="base_office" class="form-control" value="{{ $profileFormBaseOffice }}" disabled aria-disabled="true" data-disabled-field="true"', $profile);
        $this->assertStringContainsString('name="address"', $profile);
        $this->assertStringContainsString('readonly data-date-target="#profileBirthValue"', $profile);
        $this->assertStringNotContainsString('name="name" class="form-control" value="{{ $profileNameValue }}" readonly', $profile);
        $this->assertStringNotContainsString('name="phone" class="form-control" value="{{ $profilePhoneValue }}" readonly', $profile);
        $this->assertStringNotContainsString('name="email" class="form-control" value="{{ $profileEmailValue }}" readonly', $profile);
        $this->assertStringContainsString('type="submit" class="btn btn-primary me-1">UPDATE PROFILE</button>', $profile);
        $this->assertStringNotContainsString('First Name', $profile);
        $this->assertStringNotContainsString('Last Name', $profile);
        $this->assertStringNotContainsString('Specialty', $profile);
        $this->assertStringNotContainsString('Skills', $profile);
        $this->assertStringNotContainsString('Country', $profile);
        $this->assertStringNotContainsString('City', $profile);
        $this->assertStringNotContainsString('demo@gmail.com', $profile);
        $this->assertStringNotContainsString('HTML, JavaScript, PHP', $profile);
        $this->assertStringNotContainsString('<h6 class="card-title">Change Password</h6>', $profile);
        $this->assertStringNotContainsString('Change Password', $profile);
        $this->assertStringContainsString('Current Password', $profile);
        $this->assertStringNotContainsString('Old Password', $profile);
        $this->assertStringContainsString('name="current_password"', $profile);
        $this->assertStringContainsString('New Password', $profile);
        $this->assertStringContainsString('name="password"', $profile);
        $this->assertStringContainsString('Confirmation Password', $profile);
        $this->assertStringContainsString('name="password_confirmation"', $profile);
        $this->assertStringContainsString('$errors->passwordUpdate->any()', $profile);
        $this->assertStringContainsString("session('password_status')", $profile);
        $this->assertStringContainsString('UPDATE PASSWORD', $profile);
        $this->assertStringContainsString('type="submit" class="btn btn-primary me-1">UPDATE PASSWORD</button>', $profile);
        $this->assertStringNotContainsString('Forgot your password?', $profile);
        $this->assertStringNotContainsString('pages/forgot-password.html', $profile);
        $this->assertStringNotContainsString('Portfolio</a>', $profile);
        $this->assertStringNotContainsString('dexignzone.com', $profile);
    }

    public function test_profile_update_request_limits_gender_and_profile_fields(): void
    {
        $request = File::get(app_path('Http/Requests/ProfileUpdateRequest.php'));
        $photoRequest = File::get(app_path('Http/Requests/ProfilePhotoUpdateRequest.php'));

        $this->assertStringContainsString('class ProfileUpdateRequest extends FormRequest', $request);
        $this->assertStringContainsString('return true;', $request);
        $this->assertStringContainsString("'gender' => ['nullable', Rule::in(['Male', 'Female'])]", $request);
        $this->assertStringContainsString("'marital_status' => ['nullable', Rule::in(['Lajang', 'Menikah'])]", $request);
        $this->assertStringContainsString("'birth' => ['nullable', 'date_format:Y-m-d']", $request);
        $this->assertStringContainsString("Rule::unique('users', 'email')->ignore(\$this->user()?->id)", $request);
        $this->assertStringContainsString("'profile_picture' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048']", $photoRequest);
    }
}
