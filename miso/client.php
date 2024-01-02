<?php

namespace Miso;

class Client {

    protected $helpers;
    public $products;

    public function __construct($args) {
        $helpers = $this->helpers = new Helpers($args);
        $this->products = new Products($helpers);
    }

}

class Helpers {

    protected $args;
    protected $http;

    public function __construct($args = []) {
        if (!isset($args['api_key'])) {
            throw new \Exception('api_key is required');
        }
        $this->args = $args;
        $this->http = new \GuzzleHttp\Client([
            'headers' => [
                'X-API-KEY' => $args['api_key'],
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
        $maxRetry = $this->args['max_retry'] ?? 3;
        for ($i = 0; $i < $maxRetry; $i++) {
            try {
                return $this->requestOnce($method, $path, $body);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                // don't retry on 4xx
                if ($e->getResponse()->getStatusCode() === 422) {
                    $body = json_decode($e->getResponse()->getBody()->getContents(), true);
                    throw new DataFormatException($body['message'], $body['data']);
                } else {
                    throw $e;
                }
            } catch (\Exception $e) {
                if ($i === $maxRetry - 1) {
                    throw $e;
                }
            }
        }
        throw new \Exception('Unknown error');
    }

    protected function requestOnce($method, $path, $body = null) {
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

    public function ids($args = []) {
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

class DataFormatException extends \Exception {

    protected $message;
    protected $data;

    public function __construct(string $message, array $data) {
        $this->message = $message;
        $this->data = $data;
        parent::__construct('Data format error: ' . $message . implode(' ', $data));
    }

    public function getData() {
        return $this->data;
    }

    public function __toString() {
        return __CLASS__ . ': ' . $this->message . implode(' ', $this->data);
    }

}
