<?php

namespace Jplhomer\Axiom;

use Illuminate\Support\ServiceProvider;

class AxiomLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Initialize trace ID for web requests.
        if ($this->app->runningInConsole() === false) {
            $request = $this->app->make('request');
            $traceId = $request->header('X-Astro-Trace-Id');
            AxiomLog::initTraceId($traceId);
            AxiomLog::setServiceName(config('app.name', 'app'));
        }
    }
}
