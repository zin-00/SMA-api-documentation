<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'reference_id',
        'is_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function reference()
    {
        return $this->morphTo();
    }
    public function message()
    {
        return $this->belongsTo(Message::class, 'reference_id')
                    ->where('type', 'message');
    }
    // Polymorphic relation to notifiable entities
    public function notifiable()
    {
        return $this->morphTo();
    }
}
