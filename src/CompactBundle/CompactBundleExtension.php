<?php
/**
 * Created by PhpStorm.
 * User: Kogaion
 * Date: 9/15/2017
 * Time: 12:04 PM
 */

namespace CompactBundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Provides an {@link ExtensionInterface} and {@link PrependExtensionInterface} implementation for {@link
 * CompactBundle}s. Defines conventions to be used among bundles.
 */
class CompactBundleExtension implements ExtensionInterface, PrependExtensionInterface
{
    /** @var CompactBundle the associated bundle */
    private $bundle;

    public function __construct(CompactBundle $bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $configDir = new FileLocator($this->bundle->getPath() . '/Resources/config');
        $loader = new YamlFileLoader($container, $configDir);
        $loader->load("services.yml");
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return Container::underscore($this->bundle->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getXsdValidationBasePath()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return 'http://example.org/schema/dic/' . $this->getAlias();
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        //$this->prependDoctrineMappingConfiguration($container);
        //$this->prependJmsSerializerConfiguration($container);
    }

    /**
     * Configures doctrine to add mapping files if doctrine config files are stored within this compact bundle in:
     *  <Bundle>/Resources/config/doctrine
     *
     * @param ContainerBuilder $container
     */
    private function prependDoctrineMappingConfiguration(ContainerBuilder $container)
    {
        $doctrineConfigs = sprintf('%s/Resources/config/doctrine', $this->bundle->getPath());
        if (is_dir($doctrineConfigs)) {
            $ns = $this->bundle->getNamespace();
            $container->prependExtensionConfig('doctrine', [
                'orm' => [
                    'mappings' => [
                        $this->bundle->getName() => [
                            'type' => 'yml',
                            'dir' => 'Resources/config/doctrine',
                            'prefix' => substr($ns, 0, strrpos($ns, '\\')) . '\\Domain',
                        ],
                    ]
                ]
            ]);
        }
    }

    /**
     * Configures JMS serializer to add mapping files if serializer config files are stored within this compact bundle in
     *  <Bundle>/Resources/config/serializer
     *
     * @param ContainerBuilder $container
     */
    private function prependJmsSerializerConfiguration(ContainerBuilder $container)
    {
        $serializerConfigs = sprintf('%s/Resources/config/serializer', $this->bundle->getPath());
        if (is_dir($serializerConfigs)) {
            $ns = $this->bundle->getNamespace();
            $container->prependExtensionConfig('jms_serializer', [
                'metadata' => [
                    'directories' => [
                        $this->bundle->getName() => [
                            'namespace_prefix' => substr($ns, 0, strrpos($ns, '\\')),
                            'path' => sprintf('@%s/Resources/config/serializer', $this->bundle->getName()),
                        ],
                    ]
                ]
            ]);
        }
    }

}