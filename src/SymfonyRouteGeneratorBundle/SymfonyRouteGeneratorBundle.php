<?php declare(strict_types=1);

namespace MichaelCozzolino\SymfonyRouteGeneratorBundle;

use MichaelCozzolino\SymfonyRouteGeneratorBundle\DependencyInjection\MichaelCozzolinoSymfonyRouteGeneratorBundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @psalm-api
 */
class SymfonyRouteGeneratorBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new MichaelCozzolinoSymfonyRouteGeneratorBundleExtension();
    }
}
