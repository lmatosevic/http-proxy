<?php

namespace lm\proxy;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Application {

    private $httpClient;

    function __construct() {
        $stack = HandlerStack::create();
        $logger = new Logger('client');
        $logger->pushHandler(new StreamHandler(constant("LOG_PATH"), Logger::INFO));
        $stack->push(
            Middleware::log(
                $logger,
                new MessageFormatter(MessageFormatter::SHORT)
            )
        );
        $this->httpClient = new Client(['handler' => $stack]);
    }

    public function run() {
        $method = $_SERVER["REQUEST_METHOD"];
        $uri = constant("REDIRECT_SCHEMA") . '://' . constant("REDIRECT_HOST") . ':' . constant("REDIRECT_PORT") . $_SERVER["REQUEST_URI"];
        $headers = getallheaders();
        $body = $_POST;

        $response = $this->doHttpRequest($method, $uri, $headers, $body);

        header("Content-type: " . $response->getHeader("Content-type")[0]);

        echo $response->getBody();
    }

    private function doHttpRequest($method, $uri, $headers, $body) {
        $request = new Request($method, $uri, $headers);

        $response = $this->httpClient->send($request);

        return $response;
    }
}