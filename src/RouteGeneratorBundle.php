<?php declare(strict_types=1);

namespace MichaelCozzolino\RouteGeneratorBundle;

use MichaelCozzolino\RouteGeneratorBundle\DependencyInjection\MichaelCozzolinoRouteGeneratorBundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class RouteGeneratorBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new MichaelCozzolinoRouteGeneratorBundleExtension();
    }
}
