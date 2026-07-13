<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    public function test_profile_update_is_wired_to_validated_persistence(): void
    {
        $controller = File::get(app_path('Http/Controllers/ProfileController.php'));
        $request = File::get(app_path('Http/Requests/ProfileUpdateRequest.php'));
        $passwordRequest = File::get(app_path('Http/Requests/ProfilePasswordUpdateRequest.php'));
        $photoRequest = File::get(app_path('Http/Requests/ProfilePhotoUpdateRequest.php'));
        $profile = File::get(resource_path('views/profile.blade.php'));
        $routes = File::get(base_path('routes/web.php'));

        $this->assertStringContainsString("Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');", $routes);
        $this->assertStringContainsString("Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');", $routes);
        $this->assertStringContainsString("Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');", $routes);
        $this->assertStringContainsString('public function update(ProfileUpdateRequest $request): RedirectResponse', $controller);
        $this->assertStringContainsString('public function updatePassword(ProfilePasswordUpdateRequest $request): RedirectResponse', $controller);
        $this->assertStringContainsString('public function updatePhoto(ProfilePhotoUpdateRequest $request): JsonResponse', $controller);
        $this->assertStringContainsString('$validated = $request->validated();', $controller);
        $this->assertStringContainsString("'gender' => \$this->blankToNull(\$validated['gender'] ?? null)", $controller);
        $this->assertStringContainsString("'date_of_birth' => \$this->blankToNull(\$validated['birth'] ?? null)", $controller);
        $this->assertStringContainsString("'address_line' => \$addressLine", $controller);
        $this->assertStringContainsString("'gender' => ['nullable', Rule::in(['Male', 'Female'])]", $request);
        $this->assertStringContainsString("'birth' => ['nullable', 'date_format:Y-m-d']", $request);
        $this->assertStringContainsString("['Male', 'Female']", $profile);
        $this->assertStringContainsString('type="hidden" name="birth" id="profileBirthValue"', $profile);
        $this->assertStringContainsString('$birthDisplayInput.daterangepicker(pickerOptions)', $profile);
        $this->assertStringNotContainsString('type="date" name="birth"', $profile);
        $this->assertStringContainsString('type="submit" class="btn btn-primary me-1">UPDATE PROFILE</button>', $profile);
        $this->assertStringContainsString("'current_password' => ['required', 'current_password']", $passwordRequest);
        $this->assertStringContainsString("'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password']", $passwordRequest);
        $this->assertStringContainsString("protected \$errorBag = 'passwordUpdate';", $passwordRequest);
        $this->assertStringContainsString("'password' => Hash::make(\$validated['password'])", $controller);
        $this->assertStringContainsString("Auth::guard('web')->logout();", $controller);
        $this->assertStringContainsString('$request->session()->invalidate();', $controller);
        $this->assertStringContainsString('$request->session()->regenerateToken();', $controller);
        $this->assertStringContainsString("->route('login')", $controller);
        $this->assertStringContainsString('action="{{ route(\'profile.password.update\') }}" method="POST"', $profile);
        $this->assertStringContainsString('type="submit" class="btn btn-primary me-1">UPDATE PASSWORD</button>', $profile);
        $this->assertStringContainsString("'profile_picture' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:1024']", $photoRequest);
        $this->assertStringContainsString('storeProfilePictureFile', $controller);
        $this->assertStringContainsString('deleteStoredProfilePicture', $controller);
        $this->assertStringContainsString('accept=".jpg,.jpeg,.png"', $profile);
        $this->assertStringContainsString("var allowedExtensions = ['jpg', 'jpeg', 'png'];", $profile);
        $this->assertStringContainsString('file.size > 1024 * 1024', $profile);
        $this->assertStringContainsString("data-upload-url=\"{{ route('profile.photo.update') }}\"", $profile);
        $this->assertStringContainsString("formData.append('profile_picture', file)", $profile);
    }
}
