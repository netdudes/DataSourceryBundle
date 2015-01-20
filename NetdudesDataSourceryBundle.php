<?php

namespace Netdudes\DataSourceryBundle;

use Netdudes\DataSourceryBundle\DependencyInjection\Compiler\DataSourceryBundleExtensionServicePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetdudesDataSourceryBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new DataSourceryBundleExtensionServicePass());
    }
}
