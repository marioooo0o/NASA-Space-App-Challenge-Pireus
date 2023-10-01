<?php

namespace App\Http\Controllers;

use App\Http\Resources\BrightSpotResource;
use App\Models\BrightSpot;
use App\Services\CoordinatesService;
use Illuminate\Http\Request;

class AddNewBrightSpotController extends Controller
{
    public function __construct(protected CoordinatesService $service)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $isExists = false;
        $brightSpots = BrightSpot::all();
        foreach ($brightSpots as $brightSpot) {
            if ($this->service->getDistanceBetweenPointsNew(
                $request->input("latitude"),
                $request->input("longitude"),
                $brightSpot->latitude,
                $brightSpot->longitude
            ) <= 15) {
                $brightSpot->votes()->create(['vote' => true]);
                $isExists = true;
            }
        }
        if (!$isExists) {
            BrightSpot::create([
                'latitude' => $request->input("latitude"),
                'longitude' => $request->input("longitude"),
                'from_nasa' => false,
            ]);
        }

        $brightSpots = BrightSpot::latest()->get();
        return BrightSpotResource::collection($brightSpots);
        // return new BrightSpotResource(BrightSpot::create([
        //     'latitude' => $request->input("latitude"),
        //     'longitude' => $request->input("longitude"),
        //     'from_nasa' => false,
        // ]));
    }
}
