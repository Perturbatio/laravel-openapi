<?php

namespace Vyuldashev\LaravelOpenApi\Tests;

use Examples\Petstore\PetController;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Vyuldashev\LaravelOpenApi\OpenApiServiceProvider::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Generator::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Attributes\Operation::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Attributes\Parameters::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Attributes\Response::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\ComponentsBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Components\Builder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Components\CallbacksBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Components\RequestBodiesBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Components\ResponsesBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Components\SchemasBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Components\SecuritySchemesBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\ExtensionsBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\InfoBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\PathsBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\CallbacksBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Attributes\Callback::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\ParametersBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\RequestBodyBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\ResponsesBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\SecurityBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Paths\OperationsBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\ServersBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\TagsBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\ClassMapGenerator::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\RouteInformation::class)]
/**
 * @see https://github.com/OAI/OpenAPI-Specification/blob/master/examples/v3.0/petstore.yaml
 */
class PetstoreTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('APP_URL=http://petstore.swagger.io/v1');

        parent::setUp();

        Route::get('/pets', [PetController::class, 'index']);
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('openapi.locations.schemas', [
            __DIR__.'/../examples/petstore/OpenApi/Schemas',
        ]);
    }

    public function test_generate(): void
    {
        $spec = $this->generate()->toArray();

        self::assertSame('http://petstore.swagger.io/v1', $spec['servers'][0]['url']);

        self::assertArrayHasKey('/pets', $spec['paths']);
        self::assertArrayHasKey('get', $spec['paths']['/pets']);

        self::assertSame([
            'summary' => 'List all pets.',
            'operationId' => 'listPets',
            'parameters' => [
                [
                    'name' => 'limit',
                    'in' => 'query',
                    'description' => 'How many items to return at one time (max 100)',
                    'required' => false,
                    'schema' => [
                        'format' => 'int32',
                        'type' => 'integer',
                    ],
                ],
            ],
            'responses' => [
                422 => [
                    '$ref' => '#/components/responses/ErrorValidation',
                ],
            ],
        ], $spec['paths']['/pets']['get']);

        self::assertArrayHasKey('components', $spec);
        self::assertArrayHasKey('schemas', $spec['components']);
        self::assertArrayHasKey('Pet', $spec['components']['schemas']);

        self::assertSame([
            'type' => 'object',
            'required' => [
                'id',
                'name',
            ],
            'properties' => [
                'id' => [
                    'format' => 'int64',
                    'type' => 'integer',
                ],
                'name' => [
                    'type' => 'string',
                ],
                'tag' => [
                    'type' => 'string',
                ],
            ],
        ], $spec['components']['schemas']['Pet']);
    }
}
