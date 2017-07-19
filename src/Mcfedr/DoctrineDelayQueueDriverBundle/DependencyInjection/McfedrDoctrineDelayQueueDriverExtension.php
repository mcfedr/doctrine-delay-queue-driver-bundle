<?php

namespace Mcfedr\DoctrineDelayQueueDriverBundle\DependencyInjection;

use Mcfedr\DoctrineDelayQueueDriverBundle\Command\DoctrineDelayRunnerCommand;
use Mcfedr\DoctrineDelayQueueDriverBundle\Manager\DoctrineDelayQueueManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class McfedrDoctrineDelayQueueDriverExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        // get all Bundles
        $bundles = $container->getParameter('kernel.bundles');
        // determine if McfedrQueueManagerBundle is registered
        if (isset($bundles['McfedrQueueManagerBundle'])) {
            $container->prependExtensionConfig('mcfedr_queue_manager', [
                'drivers' => [
                    'doctrine_delay' => [
                        'class' => DoctrineDelayQueueManager::class,
                        'options' => [
                            'entity_manager' => null,
                            'default_manager' => null,
                            'default_manager_options' => [],
                        ],
                        'command_class' => DoctrineDelayRunnerCommand::class,
                    ],
                ],
            ]);
        }
    }
}
