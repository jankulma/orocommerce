<?php

namespace Oro\Bundle\CMSBundle\DependencyInjection;

use Oro\Bundle\CMSBundle\ContentWidget\ContentWidgetTypeInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroCMSExtension extends Extension
{
    const CONTENT_RESTRICTIONS_MODE = 'mode';
    const LAX_CONTENT_RESTRICTIONS = 'lax_restrictions';

    private array $contentRestrictionModes = ['secure', 'selective', 'unsecure'];

    public function addContentRestrictionMode(string $value): void
    {
        $this->contentRestrictionModes[] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration($this->contentRestrictionModes), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services_api.yml');
        $loader->load('form_types.yml');
        $loader->load('block_types.yml');
        $loader->load('content_widget_types.yml');
        $loader->load('controllers.yml');
        $loader->load('image_placeholder.yml');

        if ('test' === $container->getParameter('kernel.environment')) {
            $loader->load('services_test.yml');
        }

        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));

        $container->setParameter(
            sprintf(
                '%s.%s.%s',
                $this->getAlias(),
                Configuration::DIRECT_EDITING,
                Configuration::LOGIN_PAGE_CSS_FIELD_OPTION
            ),
            $config[Configuration::DIRECT_EDITING][Configuration::LOGIN_PAGE_CSS_FIELD_OPTION]
        );

        $contentRestrictions = $config['content_restrictions'] ?? [];
        $contentRestrictionsMode = 'default';
        $laxContentRestrictions = [];
        if (array_key_exists(self::CONTENT_RESTRICTIONS_MODE, $contentRestrictions)) {
            $contentRestrictionsMode = $contentRestrictions[self::CONTENT_RESTRICTIONS_MODE];
        }
        if (array_key_exists(self::LAX_CONTENT_RESTRICTIONS, $contentRestrictions)) {
            $laxContentRestrictions = $contentRestrictions[self::LAX_CONTENT_RESTRICTIONS];
        }

        $container->setParameter('oro_cms.content_restrictions_mode', $contentRestrictionsMode);
        $container->setParameter('oro_cms.lax_content_restrictions', $laxContentRestrictions);

        $container->registerForAutoconfiguration(ContentWidgetTypeInterface::class)
            ->addTag('oro_cms.content_widget.type');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        $container->addResource(new FileResource((new \ReflectionClass(Configuration::class))->getFileName()));

        return new Configuration($this->contentRestrictionModes);
    }
}
