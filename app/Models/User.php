<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'avatar', 'is_activated', 'activation_token',
        'reset_token', 'reset_expires',
        'font_size', 'note_color', 'theme',
    ];

    protected $hidden = ['password', 'remember_token', 'activation_token', 'reset_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'reset_expires'     => 'datetime',
        'is_activated'      => 'boolean',
        'password'          => 'hashed',
    ];

    // Accessor: dùng 'name' thay cho 'display_name'
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function labels()
    {
        return $this->hasMany(Label::class);
    }
}
