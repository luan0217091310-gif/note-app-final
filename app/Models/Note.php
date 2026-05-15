<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Note extends Model
{
    protected $fillable = ['user_id', 'title', 'content', 'is_pinned', 'pinned_at', 'lock_password'];

    protected $hidden = ['lock_password'];

    protected $casts = [
        'is_pinned'  => 'boolean',
        'pinned_at'  => 'datetime',
    ];

    // ======================== RELATIONSHIPS ========================
    public function user()      { return $this->belongsTo(User::class); }
    public function images()    { return $this->hasMany(NoteImage::class); }
    public function labels()    { return $this->belongsToMany(Label::class, 'note_labels'); }
    public function shares()    { return $this->hasMany(NoteShare::class); }

    // ======================== HELPERS ========================
    public function isLocked(): bool
    {
        return !empty($this->getRawOriginal('lock_password'));
    }

    public function verifyLock(string $password): bool
    {
        $hash = $this->getRawOriginal('lock_password');
        if (!$hash) return true;
        return Hash::check($password, $hash);
    }

    public function setLockPasswordAttribute($value)
    {
        $this->attributes['lock_password'] = $value ? Hash::make($value) : null;
    }

    public function checkAccess(int $userId): string|false
    {
        if ($this->user_id === $userId) return 'owner';
        $share = $this->shares()->where('shared_with_id', $userId)->first();
        return $share ? $share->permission : false;
    }
}
