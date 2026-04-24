<?php

namespace App\Models;

use App\Support\ResolvesPublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberGalleryImage extends Model
{
    use HasFactory;
    use ResolvesPublicMedia;

    protected $fillable = [
        'user_id',
        'image_path',
        'caption',
        'sort_order',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imageUrl(): ?string
    {
        return $this->resolvePublicMediaUrl($this->image_path);
    }
}
