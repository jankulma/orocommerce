<?php

namespace Oro\Bundle\CheckoutBundle\Tests\Unit\Datagrid;

use Oro\Bundle\CheckoutBundle\Datagrid\CheckoutGridCustomerVisitorAclListener;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CheckoutGridCustomerVisitorAclListenerTest extends TestCase
{
    /** @var CheckoutGridCustomerVisitorAclListener */
    private $listener;

    /** @var TokenStorage */
    private $tokenStorage;

    protected function setUp()
    {
        $this->tokenStorage = new TokenStorage();
        $this->listener = new CheckoutGridCustomerVisitorAclListener($this->tokenStorage);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage Anonymous users are not allowed.
     */
    public function testOnBuildBeforeWithAnonymousCustomerUserTokenShouldThrowException()
    {
        $this->tokenStorage->setToken(new AnonymousCustomerUserToken('test'));
        $this->listener->onBuildBefore($this->createMock(BuildBefore::class));
    }

    public function testOnBuildBeforeWithRegularTokenShouldNotThrowException()
    {
        $this->tokenStorage->setToken(new UsernamePasswordToken('test', 'none', 'main'));
        $this->listener->onBuildBefore($this->createMock(BuildBefore::class));
    }
}
