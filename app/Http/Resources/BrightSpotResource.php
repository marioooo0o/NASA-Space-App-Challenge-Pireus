<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrightSpotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "country_id" => $this->country_id,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            // "bright_ti4" => $this->bright_ti4,
            // "scan" => $this->scan,
            // "track" => $this->track,
            // "acq_date" => $this->acq_date,
            // "acq_time" => $this->acq_time,
            // "satellite" => $this->satellite,
            // "instrument" => $this->instrument,
            "confidence" => $this->confidence,
            "from_nasa" => $this->from_nasa,
            // "version" => $this->version,
            // "bright_ti5" => $this->bright_ti5,
            // "frp" => $this->frp,
            // "daynight" => $this->daynight,
            "votes" => [
                'positive' => $this->votes()->where('vote', 1)->where('created_at', '>=', Carbon::yesterday())->count(),
                'negative' => $this->votes()->where('vote', 0)->where('created_at', '>=', Carbon::yesterday())->count(),
            ]
        ];
    }
}
