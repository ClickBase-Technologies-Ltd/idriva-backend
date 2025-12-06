<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
// Add this import
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'otherNames',
        'email',
        'password',
        'role',
        'phoneNumber',
        'otp_code',
        'otp_expires_at',
        'email_verified_at',
        'profileSlug',
        'avatar',
        'profileImage',
        'coverImage',
        'status',
        'location',
        'bio',
        'last_seen', // Add this
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'otp_code',
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'last_seen' => 'datetime', // Add this
            'password' => 'hashed',
        ];
    }

    /**
     * JWT Identifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT Custom Claims
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * User role relationship
     */
    public function user_role()
    {
        return $this->belongsTo(Role::class, 'role', 'roleId');
    }

    /**
     * Courses the user is enrolled in
     */
    public function enrolledCourses()
    {
        return $this->hasMany(Enrollment::class, 'user_id');
    }

    /**
     * Messages sent by the user
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'senderId', 'id');
    }

    /**
     * Messages received by the user
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiverId', 'id');
    }

    /**
     * Check if user is online
     */
    public function isOnline(): bool
    {
        if (!$this->last_seen) {
            return false;
        }
        return $this->last_seen->diffInMinutes(now()) < 5;
    }

    /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        $name = trim($this->firstName . ' ' . $this->lastName);
        if (!empty($this->otherNames)) {
            $name .= ' ' . $this->otherNames;
        }
        return $name;
    }

    /**
     * Get conversations with other users
     */
    public function conversations()
    {
        $userId = $this->id;
        
        return User::where('id', '!=', $userId)
            ->whereHas('sentMessages', function($query) use ($userId) {
                $query->where('receiverId', $userId);
            })
            ->orWhereHas('receivedMessages', function($query) use ($userId) {
                $query->where('senderId', $userId);
            })
            ->with(['latestMessage' => function($query) use ($userId) {
                $query->where(function($q) use ($userId) {
                    $q->where('senderId', $userId)
                      ->orWhere('receiverId', $userId);
                });
            }])
            ->get();
    }

    /**
     * Get latest message in conversation with this user
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'senderId', 'id')
            ->orWhere('receiverId', $this->id)
            ->orderBy('created_at', 'desc')
            ->withDefault();
    }



    /**
 * Check if this user follows another user
 */
public function isFollowing(User $user): bool
{
    if (!$this->relationLoaded('following')) {
        return $this->following()
            ->where('followingId', $user->id)
            ->exists();
    }
    
    return $this->following->contains('followingId', $user->id);
}

/**
 * Check if this user is followed by another user
 */
public function isFollowedBy(User $user): bool
{
    if (!$this->relationLoaded('followers')) {
        return $this->followers()
            ->where('followerId', $user->id)
            ->exists();
    }
    
    return $this->followers->contains('followerId', $user->id);
}

/**
 * Follow a user
 */
public function follow(User $user): bool
{
    if ($this->id === $user->id) {
        throw new \Exception('Cannot follow yourself');
    }
    
    if ($this->isFollowing($user)) {
        return false;
    }
    
    $follow = Follow::create([
        'followerId' => $this->id,
        'followingId' => $user->id,
        'followedAt' => now()
    ]);
    
    // Update counters
    $this->increment('followingCount');
    $user->increment('followersCount');
    
    return (bool) $follow;
}

/**
 * Unfollow a user
 */
public function unfollow(User $user): bool
{
    $deleted = Follow::where('followerId', $this->id)
        ->where('followingId', $user->id)
        ->delete();
    
    if ($deleted) {
        // Update counters
        $this->decrement('followingCount');
        $user->decrement('followersCount');
    }
    
    return $deleted > 0;
}

/**
 * Remove a follower
 */
public function removeFollower(User $user): bool
{
    $deleted = Follow::where('followerId', $user->id)
        ->where('followingId', $this->id)
        ->delete();
    
    if ($deleted) {
        // Update counters
        $this->decrement('followersCount');
        $user->decrement('followingCount');
    }
    
    return $deleted > 0;
}


 public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'followingId', 'followerId')
            ->withTimestamps();
    }
    
    // Relationship for followings (people this user follows)
    public function followings()
    {
        return $this->belongsToMany(User::class, 'follows', 'followerId', 'followingId')
            ->withTimestamps();
    }
    
    // Profile relationship
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
    
    // Helper method to check if following another user
    // public function isFollowing(User $user): bool
    // {
    //     return $this->followings()->where('following_id', $user->id)->exists();
    // }
    
    // // Full name accessor
    // public function getFullNameAttribute(): string
    // {
    //     return trim($this->first_name . ' ' . $this->last_name . ' ' . ($this->other_names ?? ''));
    // }

/**
 * Check if user is online
 */
// public function isOnline(): bool
// {
//     if (!$this->lastSeen) {
//         return false;
//     }
    
//     try {
//         $lastSeen = \Carbon\Carbon::parse($this->lastSeen);
//         return $lastSeen->diffInMinutes(now()) < 5;
//     } catch (\Exception $e) {
//         return false;
//     }
// }
}