<?php

namespace Vyuldashev\LaravelOpenApi\Tests;

use Examples\Petstore\PetController;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\CoversClass;
use Vyuldashev\LaravelOpenApi\Attributes\Callback;
use Vyuldashev\LaravelOpenApi\Attributes\Operation;
use Vyuldashev\LaravelOpenApi\Attributes\Parameters;
use Vyuldashev\LaravelOpenApi\Attributes\Response;
use Vyuldashev\LaravelOpenApi\Builders\Components\Builder;
use Vyuldashev\LaravelOpenApi\Builders\Components\CallbacksBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\RequestBodiesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\ResponsesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\SchemasBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Components\SecuritySchemesBuilder;
use Vyuldashev\LaravelOpenApi\Builders\ComponentsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\ExtensionsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\InfoBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\ParametersBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\RequestBodyBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\SecurityBuilder;
use Vyuldashev\LaravelOpenApi\Builders\Paths\OperationsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\PathsBuilder;
use Vyuldashev\LaravelOpenApi\Builders\ServersBuilder;
use Vyuldashev\LaravelOpenApi\Builders\TagsBuilder;
use Vyuldashev\LaravelOpenApi\ClassMapGenerator;
use Vyuldashev\LaravelOpenApi\Generator;
use Vyuldashev\LaravelOpenApi\OpenApiServiceProvider;
use Vyuldashev\LaravelOpenApi\RouteInformation;

#[CoversClass(OpenApiServiceProvider::class)]
#[CoversClass(Generator::class)]
#[CoversClass(Operation::class)]
#[CoversClass(Parameters::class)]
#[CoversClass(Response::class)]
#[CoversClass(ComponentsBuilder::class)]
#[CoversClass(Builder::class)]
#[CoversClass(CallbacksBuilder::class)]
#[CoversClass(RequestBodiesBuilder::class)]
#[CoversClass(ResponsesBuilder::class)]
#[CoversClass(SchemasBuilder::class)]
#[CoversClass(SecuritySchemesBuilder::class)]
#[CoversClass(ExtensionsBuilder::class)]
#[CoversClass(InfoBuilder::class)]
#[CoversClass(PathsBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\CallbacksBuilder::class)]
#[CoversClass(Callback::class)]
#[CoversClass(ParametersBuilder::class)]
#[CoversClass(RequestBodyBuilder::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\Builders\Paths\Operation\ResponsesBuilder::class)]
#[CoversClass(SecurityBuilder::class)]
#[CoversClass(OperationsBuilder::class)]
#[CoversClass(ServersBuilder::class)]
#[CoversClass(TagsBuilder::class)]
#[CoversClass(ClassMapGenerator::class)]
#[CoversClass(RouteInformation::class)]
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
