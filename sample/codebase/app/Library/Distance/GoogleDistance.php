<?php

namespace App\Library\Distance;

class GoogleDistance implements DistanceInterface
{

    /**
     * @param string $source
     * @param string $destination
     *
     * @return int|string
     */
    public function getDistance($source, $destination)
    {
        $googleApiKey = env('GOOGLE_API_KEY');
        $googleAPIURL = env('GOOGLE_API_URL');

        if (empty($googleApiKey) || empty($googleAPIURL)) {
            return 'GOOGLE_API_CONFIG_ERROR';
        }

        $googleAPIURL = $googleAPIURL.'?units=imperial&origins='
            .$source.'&destinations='.$destination.'&key='.$googleApiKey;

        try {

            $cURL         = curl_init();
            curl_setopt($cURL, CURLOPT_URL, $googleAPIURL);
            curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE);
            $cResponse    = trim(curl_exec($cURL));
            curl_close($cURL);
            $responseData = json_decode($cResponse);
            if (empty($responseData) || $responseData->status != 'OK') {
                return (isset($responseData->status)) ? $responseData->status : 'GOOGLE_API_NULL_RESPONSE';
            }

            $dataElements = $responseData->rows[0]->elements[0];
            return (int) $dataElements->distance->value;
        } catch (\Exception $e) {
            return 'GOOGLE_API_NULL_RESPONSE';
        }
    }
}