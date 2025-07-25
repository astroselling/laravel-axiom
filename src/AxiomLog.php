<?php

namespace Jplhomer\Axiom;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AxiomLog
{
    protected static ?string $traceId = null;

    protected static string $serviceName;

    protected function __construct(protected ?string $channel = null)
    {
        if (! isset(static::$serviceName)) {
            static::$serviceName = config('app.name');
        }

        if (static::$traceId === null) {
            static::initTraceId();
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function log(string $level, string $message, array $context = []): void
    {
        Log::channel($this->channel)->$level($message, [
            'context' => $context,
            'serviceName' => static::$serviceName,
            'X-Astro-Trace-Id' => static::getTraceId(),
        ]);
    }

    public static function channel(?string $channel = null): self
    {
        return new self($channel);
    }

    public static function initTraceId(?string $traceId = null): void
    {
        if ($traceId === null) {
            $timestamp = gmdate('YmdHis');
            $uuid = Str::uuid()->toString();
            static::$traceId = $timestamp.'-'.$uuid;
        } else {
            static::$traceId = $traceId;
        }
    }

    public static function getTraceId(): ?string
    {
        if (static::$traceId === null) {
            static::initTraceId();
        }

        return static::$traceId;
    }

    public static function setServiceName(string $serviceName): void
    {
        static::$serviceName = $serviceName;
    }
}
