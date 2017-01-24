<?php

namespace lm\proxy;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Application {

    private $httpClient;

    private $logger;

    function __construct() {
        $stack = HandlerStack::create();
        $this->logger = new Logger('proxy');
        $this->logger->pushHandler(new StreamHandler(constant("LOG_INFO_PATH"), Logger::INFO));
        $this->logger->pushHandler(new StreamHandler(constant("LOG_ERROR_PATH"), Logger::ERROR));
        $this->logger->pushHandler(new StreamHandler(constant("LOG_DEBUG_PATH"), Logger::DEBUG));
        $stack->push(
            Middleware::log(
                $this->logger,
                new MessageFormatter(MessageFormatter::SHORT)
            )
        );
        $this->httpClient = new Client(['handler' => $stack]);
        // $this->logger->debug("Proxy configured");
    }

    public function run() {
        $method = $_SERVER["REQUEST_METHOD"];
        $uri = constant("REDIRECT_SCHEMA") . '://' . constant("REDIRECT_HOST") . ':' . constant("REDIRECT_PORT") . $_SERVER["REQUEST_URI"];
        $headers = getallheaders();
        $body = file_get_contents('php://input');

        foreach ($headers as $name => $value) {
            if ($name == "Host" || $name == "Referer" || $name == "Origin") { //TODO: PROXY to HOST mapping move to new function 
                $headers[$name] = str_replace(constant("PROXY_HOST") . ":" . constant("PROXY_PORT"),
                    constant("REDIRECT_HOST") . ":" . constant("REDIRECT_PORT"), $value);
            }
        }

        $response = $this->doHttpRequest($method, $uri, $headers, $body);

        foreach ($response->getHeaders() as $name => $value) {
            if ($name == "Transfer-Encoding") {
                continue;
            }
            $override = true;
            if ($name == "Set-Cookie") {
                $override = false;
            }
            if ($name == "Host" || $name == "Referer" || $name == "Origin") {
                $header = str_replace(constant("REDIRECT_HOST") . ":" . constant("REDIRECT_PORT"),
                    constant("PROXY_HOST") . ":" . constant("PROXY_PORT"), $response->getHeaderLine($name));
                header($name . ": " . $header, $override);
            } else {
                header($name . ": " . $response->getHeaderLine($name), $override);
            }
        }

        if ($response->getStatusCode() > 300 && $response->getStatusCode() < 400) {
            $location = str_replace(constant("REDIRECT_HOST") . ":" . constant("REDIRECT_PORT"),
                constant("PROXY_HOST") . ":" . constant("PROXY_PORT"), $response->getHeaderLine("Location"));
            if ($location) {
                header("Location: " . $location, true, $response->getStatusCode());
            }
        }

        echo $response->getBody();
    }

    private function doHttpRequest($method, $uri, $headers = [], $body = null) {
        $request = new Request($method, $uri, $headers, $body);

        $response = $this->httpClient->send($request, ['allow_redirects' => false]);

        return $response;
    }
}