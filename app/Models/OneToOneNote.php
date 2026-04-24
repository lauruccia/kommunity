<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OneToOneNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'one_to_one_request_id',
        'user_id',
        'note',
        'type',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(OneToOneRequest::class, 'one_to_one_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
