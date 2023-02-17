<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\AdblockParser\Parser;
use App\AdblockParser\ParserFactory;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\CacheItem;

return function(ContainerConfigurator $containerConfigurator) {
    // default configuration for services in *this* file
    $services = $containerConfigurator->services()
        ->defaults()
        ->autowire()      // Automatically injects dependencies in your services.
        ->autoconfigure(); // Automatically registers your services as commands, event subscribers, etc.


    // makes classes in src/ available to be used as services
    // this creates a service per class whose id is the fully-qualified class name
    $services->load('App\\', '../src/')
        ->exclude('../src/{DependencyInjection,Entity,Kernel.php}');
    $services->load('App\\AdblockParser\\', '../src/AdblockParser/');

    // order is important in this file because service definitions
    // always *replace* previous ones; add your own service configuration below
    $services->set(CacheItemInterface::class, CacheItem::class)->public();
    $services->set(ParserFactory::class)->public();
    $services->set(Parser::class)->public();
};