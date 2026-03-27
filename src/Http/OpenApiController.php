<?php

namespace Vyuldashev\LaravelOpenApi\Http;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;
use Illuminate\Support\Arr;
use Str;
use Vyuldashev\LaravelOpenApi\Generator;

class OpenApiController
{
    /**
     * @throws InvalidArgumentException
     */
    public function show(Generator $generator): OpenApi
    {
        $path = Str::of(request()->path())->start('/');
        $collection = 'default';
        // resolve the collection from the route
        foreach (config('openapi.collections', []) as $name => $config) {
            $uri = Str::of(Arr::get($config, 'route.uri'))->start('/');

            if (! $uri->is($path)) {
                continue;
            }
            $collection = $name;
        }

        return $generator->generate($collection);
    }
}
