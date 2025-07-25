<?php

namespace Jplhomer\Axiom\Tests\Feature;

use Illuminate\Support\Facades\Log;
use Jplhomer\Axiom\AxiomLogHandler;
use Mockery;

it('demonstrates how to configure Axiom logging in a Laravel application', function () {
    $config = [
        'channels' => [
            'stack' => [
                'driver' => 'stack',
                'channels' => ['single', 'axiom'],
                'ignore_exceptions' => false,
            ],
            'axiom' => [
                'driver' => 'monolog',
                'handler' => AxiomLogHandler::class,
                'handler_with' => [
                    'dataset' => env('AXIOM_DATASET', 'your-dataset-name'),
                    'apiToken' => env('AXIOM_API_TOKEN', 'your-api-token'),
                ],
                'level' => env('LOG_LEVEL', 'debug'),
            ],
        ],
    ];

    expect($config)->toBeArray();
    expect($config['channels']['axiom']['handler'])->toBe(AxiomLogHandler::class);
});

it('demonstrates how to log messages to Axiom', function () {
    config()->set('logging.channels.axiom', [
        'driver' => 'monolog',
        'handler' => AxiomLogHandler::class,
        'handler_with' => [
            'dataset' => 'test-dataset',
            'apiToken' => 'test-api-token',
        ],
    ]);

    $mockHandler = Mockery::mock(AxiomLogHandler::class);
    $mockHandler->shouldReceive('handle')->once();
    $mockHandler->shouldReceive('isHandling')->andReturn(true);

    $logger = Log::channel('axiom');
    $monolog = $logger->getLogger();
    $monolog->setHandlers([$mockHandler]);

    $logger->info('User logged in', [
        'user_id' => 123,
        'email' => 'user@example.com',
        'ip_address' => '192.168.1.1',
    ]);

    expect(true)->toBeTrue();
});

it('demonstrates how to log exceptions to Axiom', function () {
    config()->set('logging.channels.axiom', [
        'driver' => 'monolog',
        'handler' => AxiomLogHandler::class,
        'handler_with' => [
            'dataset' => 'test-dataset',
            'apiToken' => 'test-api-token',
        ],
    ]);

    $mockHandler = Mockery::mock(AxiomLogHandler::class);
    $mockHandler->shouldReceive('handle')->once();
    $mockHandler->shouldReceive('isHandling')->andReturn(true);

    $logger = Log::channel('axiom');
    $monolog = $logger->getLogger();
    $monolog->setHandlers([$mockHandler]);

    try {
        throw new \Exception('Something went wrong');
    } catch (\Exception $e) {
        $logger->error('An error occurred', [
            'exception' => $e,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    expect(true)->toBeTrue();
});
