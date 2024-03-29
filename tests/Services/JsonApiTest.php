<?php

namespace Humi\JsonApiConnector\Tests\Services;

use Humi\JsonApiConnector\Services\JsonApi;
use Humi\JsonApiConnector\Tests\Fixtures\Resources\TestResource;
use Humi\JsonApiConnector\Tests\Fixtures\Resources\TestResourceCollection;
use Humi\JsonApiConnector\Tests\Traits\JsonFixtures;
use Humi\JsonApiConnector\Tests\Traits\MocksGuzzle;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Orchestra\Testbench\TestCase;

/**
 * @group JsonApi
 */
class JsonApiTest extends TestCase
{
    use MocksGuzzle, JsonFixtures;

    public function setUp(): void
    {
        parent::setup();

        $this->setFixtureRoot(
            __dir__.'/../Services/Fixtures/JsonApi'
        );
    }

    /**
     * @test
     */
    public function itShouldPerformIndexRequests(): void
    {
        $client = $this->mockGuzzleClient(
            200,
            $this->jsonFixture('collection')
        );

        $api = $this->createApi($client);

        $response = $api->index(
            TestResourceCollection::class
        );

        $this->assertTrue($response instanceof ResourceCollection);
    }

    /**
     * @test
     */
    public function itShouldPerformShowRequests(): void
    {
        $client = $this->mockGuzzleClient(
            200,
            $this->jsonFixture('entity')
        );

        $api = $this->createApi($client);

        $response = $api->show(
            TestResource::class,
            $id = 1
        );

        $this->assertTrue($response instanceof JsonResource);
    }

    /**
     * @test
     */
    public function itShouldPerformStoreRequests(): void
    {
        $client = $this->mockGuzzleClient(
            200,
            $this->jsonFixture('entity')
        );

        $api = $this->createApi($client);

        $resource = new TestResource(
            $this->jsonFixtureToArray('entity')
        );

        $response = $api->store(
            $resource
        );

        $this->assertTrue($response instanceof JsonResource);
    }

    /**
     * @test
     */
    public function itShouldPerformUpdateRequests(): void
    {
        $client = $this->mockGuzzleClient(
            200,
            $this->jsonFixture('entity')
        );

        $api = $this->createApi($client);

        $resource = new TestResource(
            $this->jsonFixtureToArray('entity')
        );

        $response = $api->update(
            $resource
        );

        $this->assertTrue($response instanceof JsonResource);
    }

    /**
     * @test
     */
    public function itShouldPerformDestroyRequests(): void
    {
        $client = $this->mockGuzzleClient(
            200,
            $this->jsonFixture('entity')
        );

        $api = $this->createApi($client);

        $response = $api->destroy(
            TestResource::class,
            $id = 1
        );

        $this->assertTrue($response instanceof JsonResource);
    }

    /**
     * @test
     */
    public function itShouldThrowIfResourceNotApiResource(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->mockGuzzleClient(
            200,
            $this->jsonFixture('entity')
        );

        $api = $this->createApi($client);

        $response = $api->show(
            get_class(
                new class()
                {

                }
            ),
            $id = 1
        );
    }

    /**
     * @test
     */
    public function ItShouldThrowIfIndexResourceNotCollectionResource(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->mockGuzzleClient(
            200,
            $this->jsonFixture('entity')
        );

        $api = $this->createApi($client);

        $response = $api->index(
            get_class(
                new class()
                {

                }
            )
        );
    }

    /**
     * @test
     */
    public function ItShouldThrowIfApiResponseNotSuccessful(): void
    {
        $this->expectException(Exception::class);

        $client = $this->mockGuzzleClient(
            500,
            $this->jsonFixture('entity')
        );

        $api = $this->createApi($client);

        $response = $api->show(
            TestResource::class
        );
    }

    /**
     * @test
     */
    public function itShouldThrowIfApiResponseNotJson(): void
    {
        $this->expectException(Exception::class);

        $client = $this->mockGuzzleClient(
            200,
            '---'
        );

        $api = $this->createApi($client);

        $response = $api->show(
            TestResource::class
        );
    }

    /**
     * @test
     */
    public function itShouldThrowIfApiResponseDoesNotConformToJsonApi(): void
    {
        $this->expectException(Exception::class);

        $client = $this->mockGuzzleClient(
            200,
            '{"datum":{ "attributes":{}}}'
        );

        $api = $this->createApi($client);

        $response = $api->show(
            TestResource::class
        );
    }

    /**
     * @test
     */
    public function itShouldAllowSettingMagicParamtersOnTheResource(): void
    {
        $client = $this->mockGuzzleClient(
            200,
            $this->jsonFixture('entity')
        );

        $api = $this->createApi($client);

        $response = $api
            ->setMagicParam1('magic1')
            ->setMagicParam2('magic2')
            ->show(
                TestResource::class
            );

        $this->assertEquals('magic1', $api->getParam('magicParam1'));
        $this->assertEquals('magic2', $api->getParam('magicParam2'));
    }

    /**
     * Extend the abstract JsonApi to create our own
     * anonymous API class
     */
    private function createApi($client)
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->method('info');

        return new class($client, $logger) extends JsonApi
        {

        };
    }
}
