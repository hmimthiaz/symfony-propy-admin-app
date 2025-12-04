<?php

namespace App\Classes\Base;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class BaseService implements ServiceSubscriberInterface
{
    use BaseSubscribedTrait;
    protected ContainerInterface $container;

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function init(): void
    {
        // to be overridden by extended class
    }
}
