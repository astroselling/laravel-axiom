<?php

use Illuminate\Support\Facades\Log;
use Jplhomer\Axiom\AxiomLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

it('can be configured as a Laravel log channel', function () {
    config()->set('logging.channels.axiom', [
        'driver' => 'monolog',
        'handler' => AxiomLogHandler::class,
        'handler_with' => [
            'dataset' => 'test-dataset',
            'apiToken' => 'test-api-token',
        ],
    ]);

    $logger = Log::channel('axiom');

    expect($logger)->toBeObject();

    $handlers = $logger->getHandlers();
    expect($handlers)->toHaveCount(1);
    expect($handlers[0])->toBeInstanceOf(AxiomLogHandler::class);
});

it('can log messages through the Laravel log facade', function () {
    config()->set('logging.channels.axiom', [
        'driver' => 'monolog',
        'handler' => AxiomLogHandler::class,
        'handler_with' => [
            'dataset' => 'test-dataset',
            'apiToken' => 'test-api-token',
        ],
    ]);

    $handlerMock = Mockery::mock(HandlerInterface::class);
    $handlerMock->shouldReceive('isHandling')->andReturn(true);
    $handlerMock->shouldReceive('handle')->once();

    $logger = Log::channel('axiom');
    $monolog = $logger->getLogger();
    $monolog->setHandlers([$handlerMock]);

    $logger->info('Test message', ['foo' => 'bar']);

    expect(true)->toBeTrue();
});

it('respects log level configuration', function () {
    config()->set('logging.channels.axiom', [
        'driver' => 'monolog',
        'handler' => AxiomLogHandler::class,
        'handler_with' => [
            'dataset' => 'test-dataset',
            'apiToken' => 'test-api-token',
            'level' => 'error',
        ],
        'level' => 'error',
    ]);

    $logger = Log::channel('axiom');

    $handlers = $logger->getHandlers();
    $reflection = new ReflectionClass($handlers[0]);

    $levelProperty = $reflection->getProperty('level');
    $levelProperty->setAccessible(true);

    expect($levelProperty->getValue($handlers[0])->value)->toBe(Monolog\Level::Error->value);
});
