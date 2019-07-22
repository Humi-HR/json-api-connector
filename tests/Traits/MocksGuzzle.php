<?php

namespace Humi\JsonApiConnector\Tests\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

trait MocksGuzzle
{
    use MocksServices;

    private $mockApiResponseValue;

    public function mockGuzzle(
        $statusCode = 200,
        $responseJson = '{}'
    ): void {
        $mock = new MockHandler(
            [
                new Response(
                    $statusCode,
                    ['Content-Type' => 'application/json'],
                    $responseJson
                ),
            ]
        );

        $this->mockApiResponseValue = $responseJson;

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $this->bindService(
            Client::class,
            $client
        );
    }

    public function mockGuzzleClient(
        $statusCode = 200,
        $responseJson = '{}'
    ) {
        $mock = new MockHandler(
            [
                new Response(
                    $statusCode,
                    ['Content-Type' => 'application/json'],
                    $responseJson
                ),
            ]
        );

        $this->mockApiResponseValue = $responseJson;

        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return $client;
    }

    public function getMockGuzzleResponse(): string
    {
        return $this->mockApiResponseValue;
    }
}
