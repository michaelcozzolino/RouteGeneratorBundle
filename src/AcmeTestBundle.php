<?php

namespace Acme\TestBundle;

use Acme\TestBundle\DependencyInjection\AcmeTestBundleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AcmeTestBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new AcmeTestBundleExtension();
    }
}