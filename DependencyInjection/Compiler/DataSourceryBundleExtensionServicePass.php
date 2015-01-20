<?php

namespace Netdudes\DataSourceryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DataSourceryBundleExtensionServicePass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('netdudes_data_query.extension_container')) {
            return;
        }

        $extensionContainer = $container->getDefinition('netdudes_data_query.extension_container');

        foreach ($container->findTaggedServiceIds('netdudes_data_query.extension') as $id => $attributes) {
            $extensionContainer->addMethodCall('addExtension', [new Reference($id)]);
        }
    }
}
