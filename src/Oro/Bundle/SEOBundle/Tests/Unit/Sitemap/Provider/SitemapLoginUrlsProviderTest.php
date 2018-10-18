<?php

namespace Oro\Bundle\SEOBundle\Tests\Unit\Sitemap\Provider;

use Oro\Bundle\RedirectBundle\Generator\CanonicalUrlGenerator;
use Oro\Bundle\SEOBundle\Model\DTO\UrlItem;
use Oro\Bundle\SEOBundle\Sitemap\Provider\RouterSitemapUrlsProvider;
use Oro\Component\Website\WebsiteInterface;
use Symfony\Component\Routing\Router;

class SitemapLoginUrlsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Router|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $router;

    /**
     * @var CanonicalUrlGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $canonicalUrlGenerator;

    /**
     * @var array
     */
    protected $routes = [
        "oro_customer_customer_user_security_login",
        "oro_customer_frontend_customer_user_reset_request",
        "oro_customer_frontend_customer_user_register"
    ];

    /**
     * @var RouterSitemapUrlsProvider
     */
    private $sitemapLoginUrlsProvider;

    protected function setUp()
    {
        $this->router = $this->createMock(Router::class);
        $this->canonicalUrlGenerator = $this->createMock(CanonicalUrlGenerator::class);

        $this->sitemapLoginUrlsProvider = new RouterSitemapUrlsProvider(
            $this->router,
            $this->canonicalUrlGenerator,
            $this->routes
        );
    }

    public function testGetUrlItems()
    {
        /** @var WebsiteInterface|\PHPUnit\Framework\MockObject\MockObject $website */
        $website = $this->createMock(WebsiteInterface::class);
        $version = '1';
        $url = '/sitemaps/1/actual/test.xml';
        $absoluteUrl = 'http://test.com/sitemaps/1/actual/test.xml';
        $this->router->expects(static::any())
            ->method('generate')
            ->willReturn($url);
        $this->canonicalUrlGenerator->expects(static::any())
            ->method('getAbsoluteUrl')
            ->with($url, $website)
            ->willReturn($absoluteUrl);

        $actual = iterator_to_array($this->sitemapLoginUrlsProvider->getUrlItems($website, $version));
        $this->assertCount(3, $actual);
        /** @var UrlItem $urlItem */
        $urlItem = reset($actual);
        $this->assertInstanceOf(UrlItem::class, $urlItem);
        $this->assertEquals($absoluteUrl, $urlItem->getLocation());
        $this->assertEmpty($urlItem->getPriority());
        $this->assertEmpty($urlItem->getChangeFrequency());
    }
}
