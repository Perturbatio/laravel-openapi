<?php

namespace Vyuldashev\LaravelOpenApi\Tests\Builders;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;
use PHPUnit\Framework\Attributes\CoversClass;
use Vyuldashev\LaravelOpenApi\Attributes\Extension;
use Vyuldashev\LaravelOpenApi\Builders\ExtensionsBuilder;
use Vyuldashev\LaravelOpenApi\Factories\ExtensionFactory;
use Vyuldashev\LaravelOpenApi\Tests\TestCase;
#[CoversClass(Operation::class)]
#[CoversClass(ExtensionsBuilder::class)]
#[CoversClass(Extension::class)]
#[CoversClass(ExtensionFactory::class)]
#[CoversClass(Schema::class)]
#[CoversClass(PathItem::class)]
#[CoversClass(OpenApi::class)]
#[CoversClass(\Vyuldashev\LaravelOpenApi\OpenApiServiceProvider::class)]
class ExtensionsBuilderTest extends TestCase
{
    public function testBuildUsingFactory(): void
    {
        $operation = Operation::create()->action('get');

        $openApi = OpenApi::create()
            ->paths(
                PathItem::create()
                    ->route('/foo')
                    ->operations($operation)
            );

        /** @var ExtensionsBuilder $builder */
        $builder = resolve(ExtensionsBuilder::class);
        $builder->build($operation, collect([
            new Extension(factory: FakeExtension::class),
        ]));

        self::assertSame([
            'paths' => [
                '/foo' => [
                    'get' => [
                        'x-uuid' => ['format' => 'uuid', 'type' => 'string'],
                    ],
                ],
            ],
        ], $openApi->toArray());
    }

    public function testBuildUsingKeyValue(): void
    {
        $operation = Operation::create()->action('get');

        $openApi = OpenApi::create()
            ->paths(
                PathItem::create()
                    ->route('/foo')
                    ->operations($operation)
            );

        /** @var ExtensionsBuilder $builder */
        $builder = resolve(ExtensionsBuilder::class);
        $builder->build($operation, collect([
            new Extension(key: 'foo', value: 'bar'),
            new Extension(key: 'x-key', value: '1'),
        ]));

        self::assertSame([
            'paths' => [
                '/foo' => [
                    'get' => [
                        'x-foo' => 'bar',
                        'x-key' => '1',
                    ],
                ],
            ],
        ], $openApi->toArray());
    }
}

class FakeExtension extends ExtensionFactory
{
    public function key(): string
    {
        return 'uuid';
    }

    /**
     * @return string|null|array
     */
    public function value()
    {
        return Schema::string()->format(Schema::FORMAT_UUID);
    }
}
