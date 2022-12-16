<?php

namespace App;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait ArvanClient
{
    public function sendArvanRequest($url, $data, $method = 'post')
    {
        $arvan_token = config('app.arvan_token');

        Log::info('arvan send url: ' . json_encode($url));
        Log::info('arvan send toekn: ' . json_encode($arvan_token));
        Log::info('arvan send data: ' . json_encode($data));
        try {

            $response = Http::
            withHeaders([
                'Authorization' => $arvan_token
            ])
                ->retry('3', '400')
                ->timeout('100')
                ->accept('application/json')
                ->$method($url, $data);

            Log::info("arvan responce: " . $response);
            if ($response->successful()) {
                Log::info('arvan send data resut: ' . json_encode($response));
                return json_decode($response);
            }

            if ($response->clientError()) {
                Log::info("arvan error12 : " . json_encode($response));
                return false;
            }

            if ($response->failed()) {
                Log::info("arvan error16 : " . json_encode($response));
                return false;
            }

        } catch (\Exception $e) {
            Log::info("arvan error34 : " . $e);
            if ($e->getCode() == 404) {
                return null;
            }

            return false;
        }

        return false;
    }
}
