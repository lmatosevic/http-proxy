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

        $response = $this->doHttpRequest($method, $uri, $headers, $body);

        if ($response->getStatusCode() > 300) {
//            $code = $response->getStatusCode();
//            $resp = new Response(404, ["Location" => $response->getHeader("Location")[0], "Cookie" => $response->getHeader("Cookie")], "<h1>Page not found</h1>");
//            $counter = 1;
//            while ($code > 300) {
//                $this->logger->debug("Received redirect response " . $counter);
//                $uri_array = parse_url($resp->getHeader("Location")[0]);
//                $redirect_uri = constant("REDIRECT_SCHEMA") . '://' . constant("REDIRECT_HOST") . ':' . constant("REDIRECT_PORT") . $uri_array["path"];
//                if (isset($uri_array["query"])) {
//                    $redirect_uri .= "?" . $uri_array["query"];
//                }
//                if (isset($uri_array["fragment"])) {
//                    $redirect_uri .= "#" . $uri_array["fragment"];
//                }
//                $this->logger->debug("Creating new request to " . $redirect_uri);
//                $resp = $this->doHttpRequest("GET", $redirect_uri, $resp->getHeader("Cookie"));
//                $this->logger->debug("Received new response " . $resp->getStatusCode() . " " . print_r($resp->getHeaders(), true));
//                $code = $resp->getStatusCode();
//                $counter++;
//            }
//            $response = $resp;

//            $this->logger->debug("REDIRECTED | RESPONSE:  " . print_r($response->getHeaders(), true));
//
//            header("Location: " . $response->getHeader("Location")[0]);
//            header("Set-Cookie: " . $response->getHeader("Set-Cookie")[0]);
//            header("Expires:" . $response->getHeader("Expires")[0]);
//            header("Keep-Alive:" . $response->getHeader("Keep-Alive")[0]);
//            header("Connection: " . $response->getHeader("Connection")[0]);
        }

        header("Content-type: " . $response->getHeader("Content-type")[0]);

        echo $response->getBody();
    }

    private function doHttpRequest($method, $uri, $headers = [], $body = null) {
        $request = new Request($method, $uri, $headers, $body);

        $response = $this->httpClient->send($request, ['allow_redirects' => false]);

        return $response;
    }
}