<?php

namespace Http;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use Vyuldashev\LaravelOpenApi\Builders\Components\Builder;
use Vyuldashev\LaravelOpenApi\Builders\Components\CallbacksBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\RequestBodiesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\ResponsesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\SchemasBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\SecuritySchemesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\ComponentsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\InfoBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\OperationsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\PathsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\ServersBuilder;
use Vyuldashev\LaravelOpenApi\Builders\TagsBuilder;
use Vyuldashev\LaravelOpenApi\Generator;
use Vyuldashev\LaravelOpenApi\Http\OpenApiController;
use Vyuldashev\LaravelOpenApi\OpenApiServiceProvider;
use Vyuldashev\LaravelOpenApi\RouteInformation;
use Vyuldashev\LaravelOpenApi\Tests\TestCase;

#[CoversClass(OpenApiController::class)]
#[CoversClass(OpenApiServiceProvider::class)]
#[CoversClass(ComponentsBuilder::class)]
#[CoversClass(Builder::class)]
#[CoversClass(CallbacksBuilder::class)]
#[CoversClass(RequestBodiesBuilder::class)]
#[CoversClass(ResponsesBuilder::class)]
#[CoversClass(SchemasBuilder::class)]
#[CoversClass(SecuritySchemesBuilder::class)]
#[CoversClass(InfoBuilder::class)]
#[CoversClass(PathsBuilder::class)]
#[CoversClass(OperationsBuilder::class)]
#[CoversClass(ServersBuilder::class)]
#[CoversClass(TagsBuilder::class)]
#[CoversClass(Generator::class)]
#[CoversClass(RouteInformation::class)]
class OpenApiControllerTest extends TestCase
{
    public function test_show()
    {
        config()->set('openapi.collections.default.info.title', 'Test API');
        config()->set('openapi.collections.default.info.description', 'This is a test API');

        $response = $this->get('http://localhost/openapi');

        $this->assertSame(200, $response->getStatusCode());

        $this->assertEqualsCanonicalizing([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'Test API',
                'description' => 'This is a test API',
                'version' => '1.0.0',
            ],
            'servers' => [[]],
        ], $response->json());
    }

    public function test_show_finds_a_custom_collection()
    {
        $this->makeFakeCollections();

        \Route::get('custom-openapi', [OpenApiController::class, 'show'])
            ->name('custom'.'.specification')
            ->middleware([]);
        //        \Route::middleware('api')->namespace()
        $response = $this->get('http://localhost/custom-openapi');

        $this->assertSame(200, $response->getStatusCode());

        $this->assertEqualsCanonicalizing([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'Custom API',
                'description' => 'Custom API description',
                'version' => '1.0.0',
            ],
            'servers' => [[
                'url' => 'https://example.com/api/v1',
            ]],
        ], $response->json());
    }

    protected function makeFakeCollections(): void
    {
        config()->set('openapi.collections.custom', [
            'info' => [
                'title' => 'Custom API',
                'description' => 'Custom API description',
                'version' => '1.0.0',
                'contact' => [],
            ],
            'servers' => [
                [
                    'url' => 'https://example.com/api/v1',
                    'description' => null,
                    'variables' => [],
                ],
            ],
            'route' => [
                'uri' => '/custom-openapi',
                'middleware' => [],
            ],
        ]);

        Http::fake([
            'http://localhost/custom-openapi' => Http::response([
                'openapi' => '3.0.0',
                'info' => [
                    'title' => 'Custom API',
                    'version' => '1.0.0',
                ],
                'paths' => [],
            ], 200, ['Content-Type' => 'application/json']),
        ]);
    }
}
