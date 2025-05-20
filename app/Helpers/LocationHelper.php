<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Spatie\Geocoder\Geocoder;

class LocationHelper
{

    public static function formatAddress($latitude, $longitude): array
    {

        $geocoder = (new Geocoder(new Client()))
            ->setApiKey(config('geocoder.key'));


        $result = $geocoder->getAddressForCoordinates($latitude, $longitude);
        $arrayResult = json_decode(json_encode($result, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        $components = collect($arrayResult['address_components'] ?? []);

        $countryComponent = $components->first(static fn($c) => Arr::exists($c, 'types') && in_array('country', $c['types'], true));
        $country = Arr::exists($countryComponent ?? [], 'long_name') ? $countryComponent['long_name'] : '';

        $postalKey = ($country === 'United States' || $country === 'US') ? 'zip_code' : 'postal_code';
        $region_key = ($country === 'United States' || $country === 'US') ? 'state' : 'province';

        $streetNumberComponent = $components->first(static fn($c) => Arr::exists($c, 'types') && in_array('street_number', $c['types'], true));
        $streetNumber = Arr::exists($streetNumberComponent ?? [], 'long_name') ? $streetNumberComponent['long_name'] : '';

        $streetNameComponent = $components->first(static fn($c) => Arr::exists($c, 'types') && in_array('route', $c['types'], true));
        $streetName = Arr::exists($streetNameComponent ?? [], 'long_name') ? $streetNameComponent['long_name'] : '';

        $cityComponent = $components->first(static fn($c) => Arr::exists($c, 'types') && in_array('locality', $c['types'], true));
        $city = Arr::exists($cityComponent ?? [], 'long_name') ? $cityComponent['long_name'] : '';

        $stateComponent = $components->first(static fn($c) => Arr::exists($c, 'types') && in_array('administrative_area_level_1', $c['types'], true));
        $state = Arr::exists($stateComponent ?? [], 'long_name') ? $stateComponent['long_name'] : '';

        $postalComponent = $components->first(static fn($c) => Arr::exists($c, 'types') && in_array('postal_code', $c['types'], true));
        $postalCode = Arr::exists($postalComponent ?? [], 'long_name') ? $postalComponent['long_name'] : '';

        return [
            'street_number' => $streetNumber,
            'street_name' => $streetName,
            'city' => $city,
            $region_key => $state,
            $postalKey => $postalCode,
            'country' => $country,
        ];


    }

    public static function findLocationById(mixed $resource, string $locationId): object|null
    {
        $locations = data_get($resource, 'dsScheduleData.Location');

        if ($locations === null) {
            return null;
        }

        // If it's a single object, wrap it in an array for uniform handling
        if (is_object($locations)) {
            $locations = [$locations];
        } elseif (is_array($locations)) {
            // Sometimes it could be associative array or list, let's normalize
            // If it's associative (not numeric keys), wrap in array
            if (array_keys($locations) !== range(0, count($locations) - 1)) {
                $locations = [$locations];
            }
            // Else, it's already a list of locations
        } else {
            // Something else unexpected
            return null;
        }

        foreach ($locations as $loc) {
            // Cast arrays to objects to keep consistent
            if (is_array($loc)) {
                $loc = (object)$loc;
            }
            if (isset($loc->id) && (string)$loc->id === $locationId) {
                return $loc;
            }
        }

        return null; // Not found
    }

    public static function formatPsoAddress($location): array
    {

        $country = data_get($location, 'country', 'CA');

        $postalKey = ($country === 'United States' || $country === 'US') ? 'zip_code' : 'postal_code';
        $region_key = ($country === 'United States' || $country === 'US') ? 'state' : 'province';

        return [
            'id' => data_get($location, 'id'),
            'name' => data_get($location, 'name'),
            'latitude' => data_get($location, 'latitude'),
            'longitude' => data_get($location, 'longitude'),
            'address_line1' => data_get($location, 'address_line1'),
            'city' => data_get($location, 'city'),
            $region_key => data_get($location, 'state'),
            $postalKey => data_get($location, 'post_code_zip'),
        ];

    }

}
