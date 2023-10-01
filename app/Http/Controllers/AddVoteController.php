<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrightSpotResource;
use App\Models\BrightSpot;
use App\Models\Vote;
use Illuminate\Http\Request;

class AddVoteController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, BrightSpot $brightSpot)
    {
        $vote = Vote::create(['vote' => $request->input('vote'), 'bright_spot_id' => $brightSpot->id]);
        return new BrightSpotResource(BrightSpot::find($brightSpot->id));
    }
}
