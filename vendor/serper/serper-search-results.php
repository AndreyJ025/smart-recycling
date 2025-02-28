<?php

class SerperSearchResultsException extends Exception {}

class SerperSearchResults {
    private $api_key;
    private $base_url = "https://google.serper.dev";

    public function __construct($api_key = NULL) {
        if($api_key) {
            $this->api_key = $api_key;
        }
    }

    public function search_images($query) {
        if($this->api_key == NULL) {
            throw new SerperSearchResultsException("api_key must be defined");
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->base_url . "/images",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'q' => $query,
                'gl' => 'us',
                'hl' => 'en'
            ]),
            CURLOPT_HTTPHEADER => [
                "X-API-KEY: " . $this->api_key,
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new SerperSearchResultsException("cURL Error: " . $err);
        }

        return json_decode($response);
    }
}