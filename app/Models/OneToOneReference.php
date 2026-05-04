<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OneToOneReference extends Model
{
    protected $fillable = [
        'one_to_one_request_id',
        'author_id',
        'recipient_id',
        'content',
        'rating',
        'tags',
        'is_recommended',
    ];

    protected function casts(): array
    {
        return [
            'tags'           => 'array',
            'is_recommended' => 'boolean',
            'rating'         => 'integer',
        ];
    }

    /** Tag competenze selezionabili */
    public static function availableTags(): array
    {
        return [
            'Preparato', 'Puntuale', 'Professionale',
            'Comunicativo', 'Competente', 'Disponibile',
            'Organizzato', 'Affidabile',
        ];
    }

    public function oneToOneRequest(): BelongsTo
    {
        return $this->belongsTo(OneToOneRequest::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
