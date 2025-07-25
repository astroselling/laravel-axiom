<?php

use Jplhomer\Axiom\AxiomServiceProvider;
use Spatie\LaravelPackageTools\Package;

it('registers the package with the correct name', function () {
    $provider = new AxiomServiceProvider(app());

    $packageMock = Mockery::mock(Package::class);
    $packageMock->shouldReceive('name')
        ->once()
        ->with('laravel-axiom')
        ->andReturnSelf();

    $provider->configurePackage($packageMock);

    expect(true)->toBeTrue();
});

it('can be resolved from the service container', function () {
    $provider = app()->resolveProvider(AxiomServiceProvider::class);

    expect($provider)->toBeInstanceOf(AxiomServiceProvider::class);
});

it('is registered in the application', function () {
    $providers = app()->getLoadedProviders();

    expect($providers)->toHaveKey(AxiomServiceProvider::class);
});
