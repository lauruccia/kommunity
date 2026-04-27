<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_description',
        'show_in_nav',
        'show_in_footer',
        'nav_order',
        'footer_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'show_in_nav'    => 'boolean',
            'show_in_footer' => 'boolean',
            'is_published'   => 'boolean',
        ];
    }

    /** Genera slug automatico dal titolo se non fornito */
    protected static function booted(): void
    {
        static::creating(function (self $page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    /** Pagine pubblicate visibili nel menu nav, ordinate */
    public static function forNav(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_published', true)
            ->where('show_in_nav', true)
            ->orderBy('nav_order')
            ->orderBy('title')
            ->get();
    }

    /** Pagine pubblicate visibili nel footer, ordinate */
    public static function forFooter(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_published', true)
            ->where('show_in_footer', true)
            ->orderBy('footer_order')
            ->orderBy('title')
            ->get();
    }
}
