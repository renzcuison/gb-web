<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const AVATAR_PALETTE = [
        '#4f46e5', // Indigo
        '#2563eb', // Blue
        '#0284c7', // Sky
        '#0d9488', // Teal
        '#059669', // Emerald
        '#16a34a', // Green
        '#d97706', // Amber
        '#ea580c', // Orange
        '#e11d48', // Rose
        '#db2777', // Pink
        '#9333ea', // Purple
        '#7c3aed', // Violet
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'provider',
        'provider_id',
        'avatar',
        'avatar_color',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
        'resolved_avatar_color',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->avatar_color)) {
                $user->avatar_color = self::AVATAR_PALETTE[array_rand(self::AVATAR_PALETTE)];
            }
        });
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    public function getResolvedAvatarColorAttribute(): string
    {
        if (! empty($this->avatar_color)) {
            return $this->avatar_color;
        }

        $index = abs(crc32((string) ($this->email ?? $this->id ?? '0'))) % count(self::AVATAR_PALETTE);

        return self::AVATAR_PALETTE[$index];
    }
}
