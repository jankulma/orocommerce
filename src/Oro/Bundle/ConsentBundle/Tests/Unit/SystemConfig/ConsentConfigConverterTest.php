<?php

namespace Oro\Bundle\ConsentBundle\Tests\Unit\SystemConfig;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\ConsentBundle\Entity\Consent;
use Oro\Bundle\ConsentBundle\SystemConfig\ConsentConfig;
use Oro\Bundle\ConsentBundle\SystemConfig\ConsentConfigConverter;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\MockObject\MockObject;

class ConsentConfigConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected $configs = [
        ['consent' => 1, 'sort_order' => 100],
        ['consent' => 2, 'sort_order' => 200]
    ];

    public function testConvertBeforeSave()
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $converter = new ConsentConfigConverter($registry);
        $testData = $this->createConfigs(2);

        $expected = $this->configs;

        $actual = $converter->convertBeforeSave($testData);
        $this->assertSame($expected, $actual);
    }

    public function testConvertFromSaved()
    {
        $registry = $this->getRegistryMockWithRepository();
        $converter = new ConsentConfigConverter($registry);

        $configs = $this->configs;

        $actual = $converter->convertFromSaved($configs);

        $convertedConfigs = $this->createConfigs(2);
        $expected = [$convertedConfigs[0], $convertedConfigs[1]];

        $this->assertEquals($expected, $actual);
    }

    public function testConvertFromSavedNoConsentIds()
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $converter = new ConsentConfigConverter($registry);

        $this->assertSame([], $converter->convertFromSaved([]));
    }

    /**
     * @param int $count
     * @return ConsentConfig[]
     */
    public function createConfigs($count)
    {
        $result = [];
        for ($i = 1; $i <= $count; $i++) {
            $consent = new Consent();
            ReflectionUtil::setId($consent, $i);
            $nameFallback = new LocalizedFallbackValue();
            $consent->addName($nameFallback->setString('Consent ' . $i));

            $config = new ConsentConfig();
            $config->setConsent($consent)
                ->setSortOrder($i * 100);
            $result[] = $config;
        }

        return $result;
    }

    /**
     * @return MockObject|ManagerRegistry
     */
    protected function getRegistryMockWithRepository()
    {
        $consentConfigs = $this->createConfigs(2);
        $consents = array_map(function ($item) {
            /** @var ConsentConfig $item */
            return $item->getConsent();
        }, $consentConfigs);

        $repository = $this->createMock(ObjectRepository::class);

        $repository->expects($this->once())
            ->method('findBy')
            ->willReturn($consents);

        $manager = $this->createMock(ObjectManager::class);

        $manager->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $registry = $this->createMock(ManagerRegistry::class);

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->willReturn($manager);

        return $registry;
    }
}
