<?php

namespace Oro\Bundle\WebsiteSearchBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Oro\Bundle\WebsiteSearchBundle\DependencyInjection\OroWebsiteSearchExtension;

class OroWebsiteSearchExtensionTest extends ExtensionTestCase
{
    /** @var OroWebsiteSearchExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->extension = new OroWebsiteSearchExtension();
    }

    public function testLoad()
    {
        $this->loadExtension($this->extension);

        $expectedParameters = [
            'oro_website_search.engine_dsn'
        ];
        $this->assertParametersLoaded($expectedParameters);

        $expectedDefinitions = [];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
