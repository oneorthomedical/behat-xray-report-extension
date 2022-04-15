<?php

declare(strict_types=1);

namespace BehatXrayReport\Feature;

use Behat\Testwork\EventDispatcher\Event as TestworkEvent;
use Behat\Testwork\Specification\SpecificationIterator;
use BehatXrayReport\Xray\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

final class FeatureUpdater implements EventSubscriberInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public static function getSubscribedEvents()
    {
        return [
            TestworkEvent\ExerciseCompleted::BEFORE => ['onBeforeExercise', 2000],
        ];
    }

    public function onBeforeExercise(TestworkEvent\BeforeExerciseCompleted $event): void
    {
        $path = [];
        foreach ($event->getSpecificationIterators() as $element) {
            if ($element instanceof SpecificationIterator) {
                $path = array_merge($path, $element->getSuite()->getSetting('paths'));
            }
        }

        $path = array_unique($path);

        $finder = new Finder();
        $finder->files()->name('*.feature')->in($path);
        foreach ($finder as $file) {
            $this->client->uploadFeatureFile($file->getRealPath());
        }
    }
}
