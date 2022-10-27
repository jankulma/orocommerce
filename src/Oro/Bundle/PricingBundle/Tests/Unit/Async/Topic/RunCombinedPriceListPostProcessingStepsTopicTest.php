<?php

namespace Oro\Bundle\PricingBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\PricingBundle\Async\Topic\RunCombinedPriceListPostProcessingStepsTopic;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class RunCombinedPriceListPostProcessingStepsTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new RunCombinedPriceListPostProcessingStepsTopic();
    }

    public function validBodyDataProvider(): array
    {
        return [
            [
                'rawBody' => ['relatedJobId' => 1],
                'expectedMessage' => ['relatedJobId' => 1]
            ]
        ];
    }

    public function invalidBodyDataProvider(): array
    {
        return [
            [
                'body' => [],
                'exceptionClass' => MissingOptionsException::class,
                'exceptionMessage' => '/The required option "relatedJobId" is missing./',
            ]
        ];
    }
}
