<?php

namespace App\Services\Admin;

use App\Models\OperationManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OperationManagerAuthService
{
    public function login(array $data, string $ipAddress): void
    {
        $manager = OperationManager::where('email', $data['email'])->first();

        if (!$manager) {
            throw ValidationException::withMessages([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ]);
        }

        if (!$manager->isActive()) {
            throw ValidationException::withMessages([
                'email' => 'Tài khoản này đang ngưng hoạt động.',
            ]);
        }

        if ($manager->isLocked()) {
            throw ValidationException::withMessages([
                'email' => 'Tài khoản đang bị khóa tạm thời. Vui lòng thử lại sau.',
            ]);
        }

        if (!Hash::check($data['password'], $manager->password)) {
            $this->handleFailedLogin($manager);

            throw ValidationException::withMessages([
                'password' => 'Email hoặc mật khẩu không đúng.',
            ]);
        }

        $this->handleSuccessfulLogin($manager, $ipAddress);

        Auth::guard('admin')->login(
            $manager,
            $data['remember'] ?? false
        );
    }

    private function handleFailedLogin(OperationManager $manager): void
    {
        $failedAttempts = $manager->failed_login_attempts + 1;

        $manager->failed_login_attempts = $failedAttempts;

        if ($failedAttempts >= 5) {
            $manager->locked_until = now()->addMinutes(15);
        }

        $manager->save();
    }

    private function handleSuccessfulLogin(OperationManager $manager, string $ipAddress): void
    {
        $manager->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);
    }

    public function logout(): void
    {
        Auth::guard('admin')->logout();
    }
}
