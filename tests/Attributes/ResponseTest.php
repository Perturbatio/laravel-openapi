<?php

namespace Vyuldashev\LaravelOpenApi\Tests\Attributes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Vyuldashev\LaravelOpenApi\Attributes\Response;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;
use Vyuldashev\LaravelOpenApi\OpenApiServiceProvider;
use Vyuldashev\LaravelOpenApi\Tests\TestCase;

#[CoversClass(Response::class)]
#[CoversClass(OpenApiServiceProvider::class)]
class ResponseTest extends TestCase
{
    public static function providerResponseStatusCodes()
    {
        return [
            'integer status code' => [200, 200],
            'string status code' => ['200', '200'],
            'null status code' => [null, null],
        ];
    }

    public function test_constructor_throws_exception_when_factory_does_not_exist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Factory class must be instance of ResponseFactory');
        $response = new Response(
            factory: 'FakeResponseFactory',
            statusCode: 200,
            description: 'A successful response'
        );

        $this->assertSame('Vyuldashev\LaravelOpenApi\Responses\FakeResponseFactory', $response->factory);
    }

    public function test_constructor_accepts_a_factory()
    {
        $factory = $this->makeResponseFactory();
        $response = new Response(
            factory: $factory::class,
            statusCode: 200,
            description: 'A successful response'
        );
        $this->assertSame($factory::class, $response->factory);

        $this->assertSame(200, $response->statusCode);
        $this->assertSame('A successful response', $response->description);
    }

    #[DataProvider('providerResponseStatusCodes')]
    public function test_constructor_accepts_different_types_for_status_code(
        int|string|null $inputStatusCode, int|string|null $expectedStatusCode
    ) {
        $factory = $this->makeResponseFactory();
        $response = new Response(
            factory: $factory::class,
            statusCode: $inputStatusCode,
        );
        $this->assertSame($expectedStatusCode, $response->statusCode);
    }

    protected function makeResponseFactory(): ResponseFactory
    {
        return new class extends ResponseFactory
        {
            public function createResponse(): \GoldSpecDigital\ObjectOrientedOAS\Objects\Response
            {
                return \GoldSpecDigital\ObjectOrientedOAS\Objects\Response::create();
            }

            public function build(): \GoldSpecDigital\ObjectOrientedOAS\Objects\Response
            {
                // not used
            }
        };
    }
}
