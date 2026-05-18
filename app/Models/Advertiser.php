<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advertiser extends Model
{
    protected $fillable = ['name', 'contact_name', 'email', 'phone', 'notes'];

    public function campaigns(): HasMany
    {
        return $this->hasMany(BannerCampaign::class);
    }
}
