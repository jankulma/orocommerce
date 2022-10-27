<?php

namespace Oro\Bundle\ProductBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroProductExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('form_types.yml');
        $loader->load('importexport.yml');
        $loader->load('block_types.yml');
        $loader->load('expression_services.yml');
        $loader->load('services_api.yml');
        $loader->load('system_configuration_services.yml');
        $loader->load('related_items.yml');
        $loader->load('commands.yml');
        $loader->load('image_placeholder.yml');
        $loader->load('controllers.yml');
        $loader->load('controllers_api.yml');
        $loader->load('mq_topics.yml');

        if ('test' === $container->getParameter('kernel.environment')) {
            $loader->load('services_test.yml');
        }

        $container->prependExtensionConfig($this->getAlias(), $config);
    }
}
