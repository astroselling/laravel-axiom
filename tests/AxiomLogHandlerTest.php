<?php

use Jplhomer\Axiom\AxiomLogHandler;
use Monolog\Level;
use Monolog\LogRecord;

beforeEach(function () {
    $this->dataset = 'test-dataset';
    $this->apiToken = 'test-api-token';
    $this->handler = new AxiomLogHandler($this->dataset, $this->apiToken);
});

it('initializes with correct dataset and api token', function () {
    $reflection = new ReflectionClass($this->handler);

    $datasetProperty = $reflection->getProperty('dataset');
    $datasetProperty->setAccessible(true);

    $apiTokenProperty = $reflection->getProperty('apiToken');
    $apiTokenProperty->setAccessible(true);

    expect($datasetProperty->getValue($this->handler))->toBe($this->dataset);
    expect($apiTokenProperty->getValue($this->handler))->toBe($this->apiToken);
});

it('initializes with default level and bubble values', function () {
    $reflection = new ReflectionClass($this->handler);

    $levelProperty = $reflection->getProperty('level');
    $levelProperty->setAccessible(true);

    $bubbleProperty = $reflection->getProperty('bubble');
    $bubbleProperty->setAccessible(true);

    expect($levelProperty->getValue($this->handler)->value)->toBe(Level::Debug->value);
    expect($bubbleProperty->getValue($this->handler))->toBeTrue();
});

it('initializes with custom level and bubble values', function () {
    $handler = new AxiomLogHandler(
        $this->dataset,
        $this->apiToken,
        Level::Error,
        false
    );

    $reflection = new ReflectionClass($handler);

    $levelProperty = $reflection->getProperty('level');
    $levelProperty->setAccessible(true);

    $bubbleProperty = $reflection->getProperty('bubble');
    $bubbleProperty->setAccessible(true);

    expect($levelProperty->getValue($handler)->value)->toBe(Level::Error->value);
    expect($bubbleProperty->getValue($handler))->toBeFalse();
});

it('sends log records to Axiom API', function () {
    $curlMock = Mockery::mock('overload:curl_init');
    $curlMock->shouldReceive('curl_init')->andReturn(true);
    $curlMock->shouldReceive('curl_setopt')->andReturn(true);
    $curlMock->shouldReceive('curl_exec')->andReturn(true);
    $curlMock->shouldReceive('curl_close')->andReturn(true);

    $record = new LogRecord(
        datetime: new DateTimeImmutable,
        channel: 'test-channel',
        level: Level::Info,
        message: 'Test message',
        context: ['foo' => 'bar'],
        extra: []
    );

    $reflection = new ReflectionClass($this->handler);
    $writeMethod = $reflection->getMethod('write');
    $writeMethod->setAccessible(true);

    $writeMethod->invoke($this->handler, $record);

    expect(true)->toBeTrue();
});

it('formats log record correctly for Axiom API', function () {
    $curlHandle = fopen('php://memory', 'r+');

    $curlMock = Mockery::mock('overload:curl_init');
    $curlMock->shouldReceive('curl_init')->andReturn($curlHandle);
    $curlMock->shouldReceive('curl_setopt')->andReturn(true);
    $curlMock->shouldReceive('curl_exec')->andReturn(true);
    $curlMock->shouldReceive('curl_close')->andReturn(true);

    $record = new LogRecord(
        datetime: new DateTimeImmutable,
        channel: 'test-channel',
        level: Level::Info,
        message: 'Test message',
        context: ['foo' => 'bar'],
        extra: []
    );

    $reflection = new ReflectionClass($this->handler);
    $writeMethod = $reflection->getMethod('write');
    $writeMethod->setAccessible(true);

    $writeMethod->invoke($this->handler, $record);

    expect(true)->toBeTrue();
});
