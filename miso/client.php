<?php

namespace Miso;

class Client {

    protected $helpers;
    public $products;

    public function __construct($apiKey) {
        $helpers = $this->helpers = new Helpers($apiKey);
        $this->products = new Products($helpers);
    }

}

class Helpers {

    protected $http;

    public function __construct($apiKey) {
        $this->http = new \GuzzleHttp\Client([
            'headers' => [
                'X-API-KEY' => $apiKey,
            ],
            'base_uri' => 'https://api.askmiso.com/v1/',
        ]);
    }

    public function get($path) {
        return $this->request('GET', $path);
    }

    public function post($path, $body) {
        return $this->request('POST', $path, $body);
    }

    protected function request($method, $path, $body = null) {
        // TODO: retry mechanism
        // TODO: error handling
        $options = [];
        if ($body) {
            $options['json'] = $body;
        }
        $response = $this->http->request($method, $path, $options);
        $body = json_decode($response->getBody()->getContents(), true);
        return $body['data'];
    }

}

class Products {

    protected $helpers;

    public function __construct(Helpers $helpers) {
        $this->helpers = $helpers;
    }

    public function ids() {
        // TODO: catch 404
        return $this->helpers->get('products/_ids')['ids'];
    }

    public function upload($records) {
        return $this->helpers->post('products', ['data' => $records]);
    }

    public function delete($ids) {
        // TODO: take both string and array
        return $this->helpers->post('products/_delete', [
            'data' => [
                'product_ids' => $ids,
            ],
        ]);
    }

}
