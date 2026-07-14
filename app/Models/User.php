<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Take all user data row then count
     */
    public static function getTotalUser()
    {
        return self::count();
    }

    /**
     * Take all user data
     */
    public static function getAllUser()
    {
        return self::all();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function teamMember()
    {
        return $this->hasOne(TeamMember::class, 'user_id');
    }

    public function wbsItems()
    {
        return $this->belongsToMany(WbsItem::class,'task_user')->withPivot([
            'project_id',
            'role',
            'workload_percentage',
        ])->withTimestamps();
    }

    public function createdMeetings(): HasMany
    {
        return $this->hasMany(MeetingSchedule::class, 'created_by');
    }
}
