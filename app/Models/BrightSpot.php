<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrightSpot extends Model
{
    use HasFactory;
    protected $fillable = [
        "country_id",
        "latitude",
        "longitude",
        "bright_ti4",
        "scan",
        "track",
        "acq_date",
        "acq_time",
        "satellite",
        "instrument",
        "confidence",
        "version",
        "bright_ti5",
        "frp",
        "daynight",
        "from_nasa"
    ];

    /**
     * Get the comments for the blog post.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
