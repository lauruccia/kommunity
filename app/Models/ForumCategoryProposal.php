<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumCategoryProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forum_category_id',
        'name',
        'description',
        'status',
        'admin_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function forumCategory(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class);
    }
}
