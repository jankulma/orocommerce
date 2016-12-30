<?php

namespace Oro\Bundle\RuleBundle\Tests\Unit\RuleFiltration;

use Oro\Bundle\RuleBundle\Entity\Rule;
use Oro\Bundle\RuleBundle\Entity\RuleOwnerInterface;
use Oro\Bundle\RuleBundle\RuleFiltration\EnabledRuleFiltrationServiceDecorator;
use Oro\Bundle\RuleBundle\RuleFiltration\RuleFiltrationServiceInterface;
use Oro\Bundle\RuleBundle\RuleFiltration\StopProcessingRuleFiltrationServiceDecorator;

class StopProcessingRuleFiltrationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RuleFiltrationServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $service;

    /**
     * @var EnabledRuleFiltrationServiceDecorator
     */
    private $serviceDecorator;

    protected function setUp()
    {
        $this->service = $this->getMockBuilder(RuleFiltrationServiceInterface::class)
            ->setMethods(['getFilteredRuleOwners'])->getMockForAbstractClass();
        $this->serviceDecorator = new StopProcessingRuleFiltrationServiceDecorator($this->service);
    }

    public function testGetFilteredRuleOwnersSortWithStopProcessing()
    {
        $context = [];

        $firstOwnerNonStopProcessingRule = $this->createRuleOwner($this->createRule(1, false));
        $secondOwnerStopProcessingRule = $this->createRuleOwner($this->createRule(2, true));
        $thirdOwnerStopProcessingRule = $this->createRuleOwner($this->createRule(3, true));
        $forthOwnerNonStopProcessingRule = $this->createRuleOwner($this->createRule(4, false));

        $ruleOwners = [
            $forthOwnerNonStopProcessingRule,
            $firstOwnerNonStopProcessingRule,
            $thirdOwnerStopProcessingRule,
            $secondOwnerStopProcessingRule,
        ];

        $expectedRuleOwners = [
            $firstOwnerNonStopProcessingRule,
            $secondOwnerStopProcessingRule
        ];

        $this->service->expects(static::once())
            ->method('getFilteredRuleOwners')
            ->with($expectedRuleOwners, $context)
            ->willReturn($expectedRuleOwners);

        $actualRuleOwners = $this->serviceDecorator->getFilteredRuleOwners($ruleOwners, $context);

        static::assertEquals($expectedRuleOwners, $actualRuleOwners);
    }

    public function testGetFilteredRuleOwnersOneNonStopProcessing()
    {
        $context = [];

        $ownerNonStopProcessingRule = $this->createRuleOwner($this->createRule(1, false));

        $ruleOwners = [$ownerNonStopProcessingRule];

        $this->service->expects(static::once())
            ->method('getFilteredRuleOwners')
            ->with($ruleOwners, $context)
            ->willReturn($ruleOwners);

        static::assertEquals($ruleOwners, $this->serviceDecorator->getFilteredRuleOwners($ruleOwners, $context));
    }

    public function testGetFilteredRuleOwnersOneStopProcessing()
    {
        $context = [];

        $ownerNonStopProcessingRule = $this->createRuleOwner($this->createRule(1, true));

        $ruleOwners = [$ownerNonStopProcessingRule];

        $this->service->expects(static::once())
            ->method('getFilteredRuleOwners')
            ->with($ruleOwners, $context)
            ->willReturn($ruleOwners);

        static::assertEquals($ruleOwners, $this->serviceDecorator->getFilteredRuleOwners($ruleOwners, $context));
    }

    /**
     * @param int $sortOrder
     * @param bool $stopProcessing
     * @return Rule
     */
    private function createRule($sortOrder, $stopProcessing)
    {
        return (new Rule())->setSortOrder($sortOrder)->setStopProcessing($stopProcessing);
    }

    /**
     * @param Rule $rule
     * @return RuleOwnerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRuleOwner(Rule $rule)
    {
        $ruleOwner = $this->createPartialMock(RuleOwnerInterface::class, ['getRule']);
        $ruleOwner->expects(static::any())
            ->method('getRule')
            ->willReturn($rule);

        return $ruleOwner;
    }
}
