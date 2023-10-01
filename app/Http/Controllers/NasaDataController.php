<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetActualPosition;
use App\Http\Resources\BrightSpotResource;
use App\Models\BrightSpot;
use App\Models\Device;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NasaDataController extends Controller
{
    public function getActualData(GetActualPosition $request)
    {
        $device = Device::updateOrCreate(
            ['device_id' => $request->input('device_id')],
            [
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude')
            ]
        );
        if ($request->input('country') === 'greece') {
            $input = "country_id,latitude,longitude,bright_ti4,scan,track,acq_date,acq_time,satellite,instrument,confidence,version,bright_ti5,frp,daynight
            GRC,39.20841,22.68266,339.31,0.42,0.37,2023-09-30,1117,N,VIIRS,n,2.0NRT,295.77,2.02,D
            GRC,40.36935,21.79041,346.08,0.43,0.38,2023-09-30,1117,N,VIIRS,n,2.0NRT,297.09,2.94,D
            GRC,41.02813,25.32181,343.67,0.39,0.36,2023-09-30,1117,N,VIIRS,n,2.0NRT,307.41,5.36,D
            GRC,41.0288,25.32637,332.98,0.39,0.36,2023-09-30,1117,N,VIIRS,l,2.0NRT,303.8,5.36,D
            GRC,41.0321,25.32552,338.6,0.39,0.36,2023-09-30,1117,N,VIIRS,n,2.0NRT,306.04,5.36,D";
        }
        if ($request->input('country') === 'poland') {
            $poland = "country_id,latitude,longitude,bright_ti4,scan,track,acq_date,acq_time,satellite,instrument,confidence,version,bright_ti5,frp,daynight
            POL,51.13786,23.54909,301.68,0.33,0.55,2023-09-30,130,N,VIIRS,n,2.0NRT,285.18,0.89,N
            POL,50.07365,20.09696,295.82,0.52,0.5,2023-09-30,132,N,VIIRS,n,2.0NRT,280.06,0.79,N
            POL,51.68845,15.97989,327.02,0.44,0.39,2023-09-30,1119,N,VIIRS,n,2.0NRT,292.04,2.86,D
            POL,51.99016,18.64269,335.59,0.39,0.36,2023-09-30,1119,N,VIIRS,n,2.0NRT,293.24,5.92,D
            POL,51.99944,18.61449,336.96,0.39,0.36,2023-09-30,1119,N,VIIRS,n,2.0NRT,293.64,4.73,D";
        }


        // $baseUrl = "https://firms.modaps.eosdis.nasa.gov";
        // $client = new Client();
        // $res = $client->get("$baseUrl/api/country/csv/13a6dd06041923b04a95929473003ed4/VIIRS_SNPP_NRT/GRC/1");

        // $statusCode = $res->getStatusCode();
        // $body = $res->getBody();
        // $data = $this->parserDoChuja($res->getBody());

        $data = $this->parserDoChuja($input);
        $this->addToDatabase($data);

        $brightSpots = BrightSpot::latest()->get();
        $pointsToNotification = $this->getPointsToNotifications($brightSpots, [
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude')
        ]);
        return response()->json(
            [
                'brightSpots' => BrightSpotResource::collection($brightSpots),
                'pointsToNotification' => BrightSpotResource::collection($pointsToNotification),
            ],
            Response::HTTP_OK
        );
    }

    public function test()
    {
        $input = "country_id,latitude,longitude,bright_ti4,scan,track,acq_date,acq_time,satellite,instrument,confidence,version,bright_ti5,frp,daynight
            GRC,39.20841,22.68266,339.31,0.42,0.37,2023-09-30,1117,N,VIIRS,n,2.0NRT,295.77,2.02,D
            GRC,40.36935,21.79041,346.08,0.43,0.38,2023-09-30,1117,N,VIIRS,n,2.0NRT,297.09,2.94,D
            GRC,41.02813,25.32181,343.67,0.39,0.36,2023-09-30,1117,N,VIIRS,n,2.0NRT,307.41,5.36,D
            GRC,41.0288,25.32637,332.98,0.39,0.36,2023-09-30,1117,N,VIIRS,l,2.0NRT,303.8,5.36,D
            GRC,41.0321,25.32552,338.6,0.39,0.36,2023-09-30,1117,N,VIIRS,n,2.0NRT,306.04,5.36,D";
        $data = $this->parserDoChuja($input);
        $oki = $this->groupCoordinates($data, 15);
    }

    function getPointsToNotifications(Collection $brightSpots, array $actualLocation): array
    {
        $data = [];
        foreach ($brightSpots as $brightSpot) {
            if (($this->getDistanceBetweenPointsNew(
                $actualLocation['latitude'],
                $actualLocation['longitude'],
                $brightSpot->latitude,
                $brightSpot->longitude
            )) <= 15) {
                $data[] = $brightSpot;
            }
        }
        return $data;
    }

    function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'kilometers')
    {
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        switch ($unit) {
            case 'miles':
                break;
            case 'kilometers':
                $distance = $distance * 1.609344;
        }
        return (round($distance, 2));
    }

    public function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLon = $lon2 - $lon1;

        $a = pow(sin(($lat2 - $lat1) / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($dLon / 2), 2);
        $c = 2 * asin(sqrt($a));

        // Średni promień Ziemi w kilometrach (ok. 6371 km)
        $radius = 6371;

        // Oblicz odległość
        $distance = $radius * $c;

        return $distance;
    }

    // Funkcja do obliczania środka geograficznego tylko dla punktów w obrębie określonego promienia
    public function calculateMidpointWithinRadius($points, $radius)
    {
        $pointsWithinRadius = [];

        // Wybierz tylko te punkty, które są w obrębie określonego promienia
        foreach ($points as $point) {
            $distance = $this->haversineDistance($point['latitude'], $point['longitude'], $points[0]['latitude'], $points[0]['longitude']); // Zakładamy pierwszy punkt jako środek
            if ($distance <= $radius) {
                $pointsWithinRadius[] = $point;
            }
        }

        if (empty($pointsWithinRadius)) {
            return null; // Brak punktów w obrębie promienia
        }

        // Oblicz średnie długości i szerokości geograficzne tylko dla punktów w obrębie promienia
        $totalLat = 0;
        $totalLon = 0;
        $numPoints = count($pointsWithinRadius);

        foreach ($pointsWithinRadius as $point) {
            $totalLat += $point['latitude'];
            $totalLon += $point['longitude'];
        }

        $averageLat = $totalLat / $numPoints;
        $averageLon = $totalLon / $numPoints;

        return ['latitude' => $averageLat, 'longitude' => $averageLon];
    }



    public function prepareToDatabase($data)
    {
        $maxDistance = 15; // Maksymalna odległość w kilometrach

        // Grupuj punkty w obrębie określonej odległości
        $groups = $this->groupCoordinates($data, $maxDistance);

        return $groups;
    }
    // Funkcja do grupowania punktów w obrębie określonej odległości
    function groupCoordinates($coordinates, $maxDistance)
    {
        $groups = [];

        foreach ($coordinates as $coordinate) {
            $addedToGroup = false;

            foreach ($groups as &$group) {
                $center = $group['center'];

                // Sprawdź odległość między punktem a środkiem grupy
                $distance = $this->haversineDistance($coordinate['latitude'], $coordinate['longitude'], $center['latitude'], $center['longitude']);

                if ($distance <= $maxDistance) {
                    // Dodaj punkt do grupy i oblicz nowy środek grupy
                    $group['points'][] = $coordinate;
                    $numPoints = count($group['points']);
                    $newLat = ($center['latitude'] * ($numPoints - 1) + $coordinate['latitude']) / $numPoints;
                    $newLon = ($center['longitude'] * ($numPoints - 1) + $coordinate['longitude']) / $numPoints;
                    $group['center']['latitude'] = $newLat;
                    $group['center']['longitude'] = $newLon;
                    $addedToGroup = true;
                    break;
                }
            }

            if (!$addedToGroup) {
                // Twórz nową grupę z tym punktem jako pierwszym elementem
                $groups[] = [
                    'center' => $coordinate,
                    'points' => [$coordinate],
                ];
            }
        }

        return $groups;
    }


    public function addToDatabase($input)
    {
        // $data = $this->prepareToDatabase($input);
        foreach ($input as $item) {
            BrightSpot::updateOrCreate(
                ['latitude' =>  $item['latitude'], 'longitude' => $item["longitude"]],
                [
                    'country_id' =>  $item['country_id'],
                    'bright_ti4' =>  $item['bright_ti4'],
                    'scan' =>  $item['scan'],
                    'track' =>  $item['track'],
                    'acq_date' =>  $item['acq_date'],
                    'acq_time' =>  $item['acq_time'],
                    'satellite' =>  $item['satellite'],
                    'instrument' =>  $item['instrument'],
                    'confidence' =>  $item['confidence'],
                    'confidence' =>  $item['confidence'],
                    'version' =>  $item['version'],
                    'bright_ti5' =>  $item['bright_ti5'],
                    'frp' =>  $item['frp'],
                    'daynight' =>  $item['daynight'],
                    'from_nasa' => 1
                ]
            );
        }
    }

    public function parserDoChuja($input)
    {
        $lines = explode("\n", $input);
        $header = str_getcsv(array_shift($lines)); // Get the header row

        $data = [];
        foreach ($lines as $line) {
            $row = str_getcsv($line);
            $data[] = array_combine($header, $row);
        }
        return $data;
    }
}
