<?php

use Jplhomer\Axiom\AxiomLog;
use Jplhomer\Axiom\AxiomLogServiceProvider;

it('can be resolved from the service container', function () {
    $provider = app()->resolveProvider(AxiomLogServiceProvider::class);

    expect($provider)->toBeInstanceOf(AxiomLogServiceProvider::class);
});

it('initializes trace ID for web requests but not for console commands', function () {
    // Mock the application to simulate a web request.
    $app = Mockery::mock(app());
    $app->shouldReceive('runningInConsole')->andReturn(false);

    // Mock request with no X-Astro-Trace-Id header
    $request = Mockery::mock();
    $request->shouldReceive('header')->with('X-Astro-Trace-Id')->andReturnNull();
    $app->shouldReceive('make')->with('request')->andReturn($request);

    // Reset trace ID to ensure it's null.
    $reflection = new ReflectionClass(AxiomLog::class);
    $traceIdProperty = $reflection->getProperty('traceId');
    $traceIdProperty->setAccessible(true);
    $traceIdProperty->setValue(null);

    // Create and boot the provider.
    // @phpstan-ignore-next-line: Mockery does not support type hinting.
    $provider = new AxiomLogServiceProvider($app);
    $provider->boot();

    // Verify trace ID was initialized.
    expect(AxiomLog::getTraceId())->not->toBeNull();

    // Reset trace ID again.
    $traceIdProperty->setValue(null);

    // Mock the application to simulate a console command.
    $app = Mockery::mock(app());
    $app->shouldReceive('runningInConsole')->andReturn(true);

    // Create and boot the provider.
    // @phpstan-ignore-next-line: Mockery does not support type hinting.
    $provider = new AxiomLogServiceProvider($app);
    $provider->boot();

    // Verify trace ID was not initialized (still null).
    expect($traceIdProperty->getValue())->toBeNull();
});

it('sets the service name from config', function () {
    $app = Mockery::mock(app());
    $app->shouldReceive('runningInConsole')->andReturn(false);

    config()->set('app.name', 'test-app-name');

    // @phpstan-ignore-next-line: Mockery does not support type hinting.
    $provider = new AxiomLogServiceProvider($app);
    $provider->boot();

    $reflection = new ReflectionClass(AxiomLog::class);
    $serviceNameProperty = $reflection->getProperty('serviceName');
    $serviceNameProperty->setAccessible(true);

    expect($serviceNameProperty->getValue())->toBe('test-app-name');
});

it('sets the service name from config with default fallback', function () {
    AxiomLog::setServiceName('initial-value');

    $app = Mockery::mock(app());
    $app->shouldReceive('runningInConsole')->andReturn(false);

    // @phpstan-ignore-next-line: Mockery does not support type hinting.
    $provider = new AxiomLogServiceProvider($app);
    $provider->boot();

    $reflection = new ReflectionClass(AxiomLog::class);
    $serviceNameProperty = $reflection->getProperty('serviceName');
    $serviceNameProperty->setAccessible(true);
    $currentValue = $serviceNameProperty->getValue();

    expect($currentValue)->not->toBe('initial-value');
});

it('uses X-Astro-Trace-Id header value as trace ID when it exists in the request', function () {
    // Mock the application to simulate a web request
    $app = Mockery::mock(app());
    $app->shouldReceive('runningInConsole')->andReturn(false);

    // Define a custom trace ID that should be used
    $customTraceId = 'custom-trace-id-from-header';

    // Mock request with X-Astro-Trace-Id header
    $request = Mockery::mock();
    $request->shouldReceive('header')->with('X-Astro-Trace-Id')->andReturn($customTraceId);
    $app->shouldReceive('make')->with('request')->andReturn($request);

    // Reset trace ID to ensure it's null
    $reflection = new ReflectionClass(AxiomLog::class);
    $traceIdProperty = $reflection->getProperty('traceId');
    $traceIdProperty->setAccessible(true);
    $traceIdProperty->setValue(null);

    // Create and boot the provider
    // @phpstan-ignore-next-line: Mockery does not support type hinting.
    $provider = new AxiomLogServiceProvider($app);
    $provider->boot();

    // Verify that the trace ID matches the custom trace ID from the header
    expect(AxiomLog::getTraceId())->toBe($customTraceId);
});
