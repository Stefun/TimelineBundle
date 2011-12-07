<?php

namespace Highco\TimelineBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\Definition\Processor;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HighcoTimelineExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->mergeConfigs($configs, $container->getParameter('kernel.debug'));

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/services'));
        $loader->load('deployer.xml');
        $loader->load('filter.xml');
        $loader->load('manager.xml');
        $loader->load('orm.xml');
        $loader->load('provider.xml');
        $loader->load('spreads.xml');

        $filters = $config['filters'];
        $definition = $container->getDefinition('highco.timeline.local.puller');
        foreach($filters as $filter)
        {
            $definition->addMethodCall('addFilter', array(new Reference($filter)));
        }

        $spread  = isset($config['spread']) ? $config['spread'] : array();

        $definition = $container->getDefinition('highco.timeline.spread.manager');
        $definition->addArgument(array(
            'on_me' => isset($spread['on_me']) ? $spread['on_me'] : true,
            'on_global_context' => isset($spread['on_global_context']) ? $spread['on_global_context'] : true,
        ));
    }

    /**
     * mergeConfigs
     *
     * @param array $configs
     * @param mixed $debug
     * @access private
     * @return void
     */
    private function mergeConfigs(array $configs, $debug)
    {
        $processor = new Processor();
        $config = new Configuration($debug);

        return $processor->process($config->getConfigTreeBuilder()->buildTree(), $configs);
    }
}