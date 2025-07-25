<?php

use Illuminate\Support\Facades\Log;
use Jplhomer\Axiom\AxiomLog;

beforeEach(function () {
    AxiomLog::initTraceId('test-trace-id');
    AxiomLog::setServiceName('test-service');
});

it('initializes with a trace ID', function () {
    expect(AxiomLog::getTraceId())->toBe('test-trace-id');
});

it('generates a trace ID if none is provided', function () {
    AxiomLog::initTraceId(null);

    expect(AxiomLog::getTraceId())->not->toBeNull();
});

it('sets and uses the service name', function () {
    $reflection = new ReflectionClass(AxiomLog::class);
    $serviceNameProperty = $reflection->getProperty('serviceName');
    $serviceNameProperty->setAccessible(true);

    expect($serviceNameProperty->getValue())->toBe('test-service');

    AxiomLog::setServiceName('new-service');

    expect($serviceNameProperty->getValue())->toBe('new-service');
});

it('logs messages with the correct context', function () {
    $logChannel = Mockery::mock();
    Log::shouldReceive('channel')->with(null)->andReturn($logChannel);

    $logChannel->shouldReceive('info')->once()->with('Test message', [
        'context' => ['test' => 'value'],
        'serviceName' => 'test-service',
        'X-Astro-Trace-Id' => 'test-trace-id',
    ]);

    AxiomLog::channel()->info('Test message', ['test' => 'value']);
});

it('allows specifying a channel', function () {
    $logChannel = Mockery::mock();
    Log::shouldReceive('channel')->with('test-channel')->andReturn($logChannel);

    $logChannel->shouldReceive('debug')->once();

    AxiomLog::channel('test-channel')->debug('Test message');
});

it('provides methods for all log levels', function () {
    $logChannel = Mockery::mock();
    Log::shouldReceive('channel')->andReturn($logChannel);

    $logChannel->shouldReceive('emergency')->once();
    $logChannel->shouldReceive('alert')->once();
    $logChannel->shouldReceive('critical')->once();
    $logChannel->shouldReceive('error')->once();
    $logChannel->shouldReceive('warning')->once();
    $logChannel->shouldReceive('notice')->once();
    $logChannel->shouldReceive('info')->once();
    $logChannel->shouldReceive('debug')->once();

    $axiomLog = AxiomLog::channel();
    $axiomLog->emergency('Emergency message');
    $axiomLog->alert('Alert message');
    $axiomLog->critical('Critical message');
    $axiomLog->error('Error message');
    $axiomLog->warning('Warning message');
    $axiomLog->notice('Notice message');
    $axiomLog->info('Info message');
    $axiomLog->debug('Debug message');
});
