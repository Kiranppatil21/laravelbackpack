<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int|null $tenant_id
 */
class User extends Authenticatable
{
    use CrudTrait;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted(): void
    {
        static::retrieved(function (self $user): void {
            if ($user->password && ! Str::startsWith($user->password, '$2y$')) {
                $user->forceFill(['password' => Hash::make($user->password)])->save();
            }
        });
    }

    /**
     * Ensure password is only set when provided and hashed.
     */
    public function setPasswordAttribute($value)
    {
        if (! is_null($value) && $value !== '') {
            // Let Laravel's 'hashed' cast handle hashing if available, but ensure raw values are hashed
            $this->attributes['password'] = Str::startsWith($value, '$2y$')
                ? $value
                : bcrypt($value);
        }
    }

    /**
     * Return an avatar URL for this user. During testing we avoid external
     * HTTP calls and return an empty string. In normal environments, prefer
     * Gravatar for the user's email if available.
     */
    public function avatar()
    {
        if (app()->environment('testing')) {
            return '';
        }

        try {
            if (class_exists('Creativeorange\\Gravatar\\Facades\\Gravatar')) {
                return \Creativeorange\Gravatar\Facades\Gravatar::get($this->email, ['size' => 80]);
            }
        } catch (\Throwable $e) {
            // fallback to empty string on any failure
        }

        return '';
    }
}
