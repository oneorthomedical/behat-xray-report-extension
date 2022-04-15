<?php

declare(strict_types=1);

namespace BehatXrayReport\Feature;

use Behat\Testwork\EventDispatcher\Event as TestworkEvent;
use BehatXrayReport\Xray\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

final class FeatureUpdater implements EventSubscriberInterface
{
    private Client $client;
    private string $featurePath;

    public function __construct(Client $client, string $featurePath)
    {
        $this->client = $client;
        $this->featurePath = $featurePath;
    }

    public static function getSubscribedEvents()
    {
        return [
            TestworkEvent\ExerciseCompleted::BEFORE => ['onBeforeExercise', 2000],
        ];
    }

    public function onBeforeExercise(TestworkEvent\BeforeExerciseCompleted $event): void
    {
        $finder = new Finder();
        $finder->files()->name('*.feature')->in($this->featurePath);
        foreach ($finder as $file) {
            $this->client->uploadFeatureFile($file->getRealPath());
        }
    }
}
