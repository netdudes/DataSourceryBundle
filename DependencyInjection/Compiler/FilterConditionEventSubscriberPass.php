<?php

namespace Netdudes\DataSourceryBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FilterConditionEventSubscriberPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('netdudes_data_sourcery.query.filter_condition_factory')) {
            return;
        }

        $filterConditionFactory = $container->getDefinition('netdudes_data_sourcery.query.filter_condition_factory');

        foreach ($container->findTaggedServiceIds('u2.data_sourcery.filter_condition.event_subscriber') as $id => $attributes) {
            $filterConditionFactory->addMethodCall('registerEventSubscriber', [new Reference($id)]);
        }
    }
}
