<?php

namespace App\Models;

use App\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPublicUuid, Notifiable;

    protected $fillable = [
        'google_id',
        'name',
        'email',
        'password',
        'avatar',
    ];

    protected $hidden = [
        'id',
        'password',
        'remember_token',
    ];

    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAvatarUrlAttribute(): string
    {
        $initial = Str::upper(Str::substr(trim($this->name), 0, 1)) ?: 'U';

        $palette = [
            '#EF4444', '#F97316', '#F59E0B', '#10B981',
            '#06B6D4', '#3B82F6', '#6366F1', '#8B5CF6',
            '#EC4899', '#14B8A6',
        ];
        $bg = $palette[crc32($this->name) % count($palette)];

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">
            <circle cx="64" cy="64" r="64" fill="{$bg}"/>
            <text x="50%" y="50%" dy=".1em" fill="#ffffff" font-family="Arial, sans-serif"
                  font-size="56" font-weight="600" text-anchor="middle" dominant-baseline="middle">{$initial}</text>
        </svg>
        SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
