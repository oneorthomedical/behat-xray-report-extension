<?php
declare(strict_types=1);

namespace BehatXrayReport;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class XrayReportExtension implements Extension
{

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        //var_dump($container->getServiceIds());
        // TODO: Implement process() method.
    }

    /**
     * @inheritDoc
     */
    public function getConfigKey()
    {
        return 'xray_report';
    }

    /**
     * @inheritDoc
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * @inheritDoc
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
            ->scalarNode('xray_url')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('features_path')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('json_report_path')->cannotBeEmpty()->isRequired()->end()
            ->end();
    }

    /**
     * @inheritDoc
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $extensions = $container->getParameter('extensions');

        if (in_array(\Vanare\BehatCucumberJsonFormatter\Extension::class, $extensions) === false) {
            throw new \Exception('This extension need "' . \Vanare\BehatCucumberJsonFormatter\Extension::class . '" extension');
        }
    }
}
