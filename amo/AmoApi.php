<?php

namespace amo;

const LEADS_METHOD = '/api/v4/leads';
const LEADS_PARAMS = '?limit=10';

class AmoApi
{
    private string $link;
    private string $access_token;

    public function __construct($subdomain = null, $access_token = null)
    {
        $this->link = "https://$subdomain.amocrm.ru";
        $this->access_token = $access_token;
    }


    public function getLeads(): bool|string|null
    {
        if(!$this->access_token)
            return null;

        $request_uri = $this->link . LEADS_METHOD . LEADS_PARAMS;
        $request_options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    "Authorization: Bearer " . $this->access_token,
                    "Content-Type: application/json"
                ]
            ],
        ];

        $context = stream_context_create($request_options);

        $response = file_get_contents($request_uri, false, $context);

        return json_encode(json_decode($response)->_embedded->leads, JSON_PRETTY_PRINT);
    }
}