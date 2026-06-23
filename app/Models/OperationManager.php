<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class OperationManager extends Authenticatable
{
    use Notifiable;

    protected $table = 'operation_managers';

    protected $fillable = [
        'name',
        'email',
        'password',
        'account_type',
        'status',
        'phone',
        'remember_token',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'locked_until',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'password_changed_at' => 'datetime',
        'failed_login_attempts' => 'integer',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null
            && now()->lessThan($this->locked_until);
    }

    public function canLogin(): bool
    {
        return $this->isActive() && !$this->isLocked();
    }

    public function isAdmin(): bool
    {
        return $this->account_type === 'admin';
    }

    public function isOperationManager(): bool
    {
        return $this->account_type === 'operation_manager';
    }
}
