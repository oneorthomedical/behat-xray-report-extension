<?php

declare(strict_types=1);

namespace BehatXrayReport\Result;

use Behat\Testwork\EventDispatcher\Event as TestworkEvent;
use BehatXrayReport\Xray\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

final class ResultUploader implements EventSubscriberInterface
{
    private Client $client;
    private string $resultPath;

    public function __construct(Client $client, string $resultPath)
    {
        $this->client = $client;
        $this->resultPath = $resultPath;
    }

    public static function getSubscribedEvents()
    {
        return [
            TestworkEvent\ExerciseCompleted::AFTER => ['onAfterExercise', -2000],
        ];
    }

    public function onAfterExercise(TestworkEvent\ExerciseCompleted $event)
    {
        echo __METHOD__."\n";
        $finder = new Finder();
        $finder->files()->name('*.json')->in($this->resultPath);
        foreach ($finder as $file) {
            $response = $this->client->uploadResultJson($file->getRealPath());
            $this->client->editExecutionTestResult(json_decode($response));
        }
    }
}
