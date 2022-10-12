<?php

namespace App;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait ArvanClient
{
    public function sendArvanRequest($url, $data, $method = 'post')
    {
        try {
            $arvan_token = config('app.arvan_token');
            $response = Http::
            withHeaders([
                'Authorization' => $arvan_token
            ])
                ->retry('3', '400')
                ->accept('application/json')
                ->$method($url, $data);

            Log::info("sss: ".$response);
            if ($response->successful()) {
                return json_decode($response);
            }

            if ($response->clientError()) {
                return false;
            }

            if ($response->failed()) {
                return false;
            }

        } catch (\Exception $e) {
            Log::info($e);
            if ($e->getCode() == 404) {
                return null;
            }

            return false;
        }

    }
}
