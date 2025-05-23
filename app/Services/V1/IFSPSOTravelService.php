<?php

namespace App\Services\V1;

use App\Classes\V1\InputReference;
use App\Models\V2\PSOTravelLog;
use Carbon\CarbonInterval;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JsonException;
use Spatie\Geocoder\Geocoder;

class IFSPSOTravelService extends IFSService
{
    private IFSPSOAssistService $IFSPSOAssistService;


    public function __construct($base_url, $token, $username, $password, $account_id = null, $requires_auth = false, $pso_environment = null)
    {
        parent::__construct($base_url, $token, $username, $password, $account_id, $requires_auth, $pso_environment);
        $this->IFSPSOAssistService = new IFSPSOAssistService($base_url, $token, $username, $password, $account_id, $requires_auth);

    }

    /**
     * @throws JsonException
     */
    private function createTravelLog($payload, $id): PSOTravelLog
    {

        $travellog = new PSOTravelLog();

        $travellog->id = $id;

        $travellog->input_payload = json_encode($payload, JSON_THROW_ON_ERROR);
        $travellog->save();

        return $travellog;

    }


    /**
     * @throws JsonException
     * @throws Exception
     */
    public function analyzetravel(Request $request)
    {

        // 1) receive PSO creds + coords - done
        // 2) send to PSO - done
        // 3) broadcast back to second endpoint
        // 4) stuff gets stored
        // 5) stuff gets returned

        $id = Str::orderedUuid()->getHex()->toString();
        $payload = $this->travelPayload($request, $id);
        $travellog = $this->createTravelLog($payload, $id);


        // reverse geocode
        $start_address = $this->reverseGeocode($request->lat_from, $request->long_from);
        $end_address = $this->reverseGeocode($request->lat_to, $request->long_to);

        $formatted_google = $this->formatGoogle($request);


        $this->IFSPSOAssistService->processPayload($request->send_to_pso, $payload, $this->token, $request->base_url);
        // wait a moment?
        sleep(5);
        // now go back and get the stuff
        $travellog->refresh();

        if ($travellog->pso_response) {
            // why are we diong this? // because after we refresh, we expect a broadcast back to our receiving service which populates pso_response
            $pso_result = json_decode($travellog->pso_response, false, 512, JSON_THROW_ON_ERROR);
            $dateformat = str_contains($pso_result->time, '.') ? 'd.H:i:s' : 'H:i:s';
            $formatted_pso_duration = CarbonInterval::createFromFormat($dateformat, $pso_result->time)->forHumans();

            return [
                'travel_detail_request' => [
                    'id' => $pso_result->travel_detail_request_id,
                    'from_address' => [
                        'address' => $start_address['address'],
                        'accuracy' => $start_address['accuracy']
                    ],
                    'to_address' => [
                        'address' => $end_address['address'],
                        'accuracy' => $end_address['accuracy']
                    ],
                    'google_result' => $formatted_google,
                    'pso_result' => [
                        'distance' => $pso_result->distance,
                        'distance_km' => $pso_result->distance / 1000,
                        'duration' => $formatted_pso_duration
                    ]
                ]
            ];
        }
        return response()->json([
            'status' => 500,
            'description' => 'something looks off, double check everything'

        ], 500, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);

    }

    private function formatGoogle($request)
    {

        // get distance

        $google_values = $this->getGoogleValues($request->lat_from, $request->long_from, $request->lat_to, $request->long_to, $request->google_api_key);

        $distance = $distance_km = $formatted_google_duration = 'unable to google';

        if ($google_values['status'] === "OK") {
            $formatted_google_duration = CarbonInterval::seconds($google_values['duration']['value'])->cascade()->forHumans();

            $distance = $google_values['distance']['value'];
            $distance_km = $distance / 1000;
//            $distance_km = Number::format($distance / 1000);
//            $distance = Number::format($google_values['distance']['value']);
        }

        return [

            'distance' => $distance,
            'distance_km' => $distance_km,
            'duration' => $formatted_google_duration

        ];

    }

//    private function getGoogleValues($start, $end)
    private function getGoogleValues($lat_from, $long_from, $lat_to, $long_to, $api_key)
    {

        if ($api_key === "ish") {
            $api_key = config('pso-services.settings.google_key');
        }

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?destinations=" . $lat_from . "%2C" . $long_from . "&origins=" . $lat_to . "%2C" . $long_to . "&key=" . $api_key;
//        dd($url);
        $response = Http::timeout(5)
            ->connectTimeout(5)
            ->withHeaders(['accept' => 'application/json'])
            ->get($url);

        return $response->collect()['rows'][0]['elements'][0];
//        return $response->collect();


    }

    private function reverseGeocode($lat, $long)
    {

        $client = new Client();

        $geocoder = new Geocoder($client);


        $geocoder->setApiKey(config('geocoder.key'));

        $address_dump = $geocoder->getAddressForCoordinates($lat, $long);
        return [
            'address' => $address_dump['formatted_address'],
            'accuracy' => $address_dump['accuracy']
        ];

    }

    /**
     * @throws JsonException
     */
    public function receivePSOBroadcast(Request $request)
    {


        $detail = $request->Travel_Detail;
        $input_ref = $request->Plan[0]['input_reference_id'];

        $mapped = Arr::mapWithKeys($detail, static function (array $item) {
            return [
                $item['travel_detail_request_id'] => [
                    'distance' => $item['distance'],
                    'plan_id' => $item['plan_id'],
                    'time' => $item['time'],
                    'travel_detail_request_id' => $item['travel_detail_request_id']
                ]
            ];
        });

        foreach ($mapped as $travel_detail) {
            if ($input_ref === $travel_detail['travel_detail_request_id']) {
                PSOTravelLog::updateOrCreate(
                    ['id' => $travel_detail['travel_detail_request_id']],
                    ['pso_response' => json_encode($travel_detail, JSON_THROW_ON_ERROR)]
                );
            }
        }


        return response()->json([
            'status' => 204,
            'description' => 'all good'

        ], 204, ['Content-Type', 'application/json'], JSON_UNESCAPED_SLASHES);

    }


    private function travelPayload(Request $request, $id): array
    {
        $input_ref = (new InputReference(
            'Travel Analysis ' . $this->service_name,
            'CHANGE',
            $request->dataset_id,
            $request->input_datetime
        ))->toJson($id);


        $broadcast_json = $this->IFSPSOAssistService->BroadcastPayload('16', config('pso-services.defaults.travel_broadcast_api'));
        $detail_request = $this->travelDetailRequest($request, $id);

        $payload = collect([
            '@xmlns' => 'http://360Scheduling.com/Schema/dsScheduleData.xsd',
            'Input_Reference' => $input_ref
        ])->merge(['Broadcast' => $broadcast_json['Broadcast']])
            ->merge(['Broadcast_Parameter' => $broadcast_json['Broadcast_Parameter']])
            ->merge(['Travel_Detail_Request' => $detail_request['Travel_Detail_Request']]);


        return ['dsScheduleData' => [$payload]];
    }

    private function travelDetailRequest(Request $request, $id): array
    {
        return [
            'Travel_Detail_Request' => [
                'id' => $id,
                'latitude_from' => $request->lat_from,
                'latitude_to' => $request->lat_to,
                'longitude_from' => $request->long_from,
                'longitude_to' => $request->long_to]
        ];
    }

}
