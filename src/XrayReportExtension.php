<?php

declare(strict_types=1);

namespace BehatXrayReport;

use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use BehatXrayReport\Exception\XrayReportException;
use BehatXrayReport\Feature\FeatureUpdater;
use BehatXrayReport\Result\ResultUploader;
use BehatXrayReport\Xray\Client;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class XrayReportExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigKey()
    {
        return 'xray_report';
    }

    /**
     * {@inheritDoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
            ->scalarNode('xray_api_url')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('jira_project_key')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('json_report_path')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('browser')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('platform_version')->cannotBeEmpty()->isRequired()->end()
            ->end();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $extensions = $container->getParameter('extensions');

        if (\in_array(\Vanare\BehatCucumberJsonFormatter\Extension::class, $extensions) === false) {
            throw new XrayReportException('This extension need "'.\Vanare\BehatCucumberJsonFormatter\Extension::class.'" extension');
        }

        $envValues = getenv();
        if (isset($envValues['XRAY_DISABLE_UPLOAD'])) {
            return;
        }

        if (!isset($envValues['XRAY_CLIENT_ID']) || !isset($envValues['XRAY_CLIENT_SECRET'])) {
            throw new XrayReportException('Environement variables "XRAY_CLIENT_ID" or "XRAY_CLIENT_SECRET" are not set.');
        }

        $definition = new Definition(Client::class);
        $definition->addArgument($config['xray_api_url']);
        $definition->addArgument((string) $envValues['XRAY_CLIENT_ID']);
        $definition->addArgument((string) $envValues['XRAY_CLIENT_SECRET']);
        $definition->addArgument($config['jira_project_key']);

        $definition->addArgument($config['browser']);
        $definition->addArgument($config['platform_version']);

        $container->setDefinition(Client::class, $definition);

        $definition = new Definition(FeatureUpdater::class);
        $definition->addArgument(new Reference(Client::class));
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);

        $container->setDefinition(FeatureUpdater::class, $definition);

        $definition = new Definition(ResultUploader::class);
        $definition->addArgument(new Reference(Client::class));
        $definition->addArgument($config['json_report_path']);
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);

        $container->setDefinition(ResultUploader::class, $definition);
    }
}
