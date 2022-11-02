<?php

namespace Oro\Bundle\CatalogBundle\Tests\Unit\Layout\DataProvider;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Oro\Bundle\CatalogBundle\Handler\RequestProductHandler;
use Oro\Bundle\CatalogBundle\Layout\DataProvider\CategoryProvider;
use Oro\Bundle\CatalogBundle\Layout\DataProvider\DTO\Category as CategoryDTO;
use Oro\Bundle\CatalogBundle\Provider\CategoryTreeProvider;
use Oro\Bundle\CatalogBundle\Provider\MasterCatalogRootProviderInterface;
use Oro\Bundle\CatalogBundle\Tests\Unit\Stub\CategoryStub;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Tests\Unit\Stub\CustomerUserStub;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CategoryProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    private RequestProductHandler|\PHPUnit\Framework\MockObject\MockObject $requestProductHandler;

    private CategoryRepository|\PHPUnit\Framework\MockObject\MockObject $categoryRepository;

    private CategoryTreeProvider|\PHPUnit\Framework\MockObject\MockObject $categoryTreeProvider;

    private LocalizationHelper|\PHPUnit\Framework\MockObject\MockObject $localizationHelper;

    private TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject $tokenAccessor;

    private MasterCatalogRootProviderInterface|\PHPUnit\Framework\MockObject\MockObject $masterCatalogProvider;

    private AbstractAdapter|\PHPUnit\Framework\MockObject\MockObject $cache;

    private $categoryProvider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->requestProductHandler = $this->createMock(RequestProductHandler::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->categoryTreeProvider = $this->createMock(CategoryTreeProvider::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->localizationHelper = $this->createMock(LocalizationHelper::class);
        $this->masterCatalogProvider = $this->createMock(MasterCatalogRootProviderInterface::class);
        $this->cache = $this->createMock(AbstractAdapter::class);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::any())
            ->method('getRepository')
            ->with(Category::class)
            ->willReturn($this->categoryRepository);

        /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $registry */
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::any())
            ->method('getManagerForClass')
            ->with(Category::class)
            ->willReturn($manager);

        $this->categoryProvider = new CategoryProvider(
            $this->requestProductHandler,
            $registry,
            $this->categoryTreeProvider,
            $this->tokenAccessor,
            $this->localizationHelper,
            $this->masterCatalogProvider
        );
        $this->categoryProvider->setCache($this->cache, 3600);
    }

    public function testGetCurrentCategoryUsingMasterCatalogRoot(): void
    {
        $category = new Category();

        $this->requestProductHandler
            ->expects(self::once())
            ->method('getCategoryId')
            ->willReturn(0);

        $this->masterCatalogProvider
            ->expects(self::once())
            ->method('getMasterCatalogRoot')
            ->willReturn($category);

        $result = $this->categoryProvider->getCurrentCategory();
        self::assertSame($category, $result);
    }

    public function testGetCurrentCategoryUsingFind(): void
    {
        $category = new Category();
        $categoryId = 1;

        $this->requestProductHandler
            ->expects(self::once())
            ->method('getCategoryId')
            ->willReturn($categoryId);

        $this->categoryRepository
            ->expects(self::once())
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $result = $this->categoryProvider->getCurrentCategory();
        self::assertSame($category, $result);
    }

    public function testGetRootCategory(): void
    {
        $category = new Category();

        $this->masterCatalogProvider
            ->expects(self::once())
            ->method('getMasterCatalogRoot')
            ->willReturn($category);

        $result = $this->categoryProvider->getRootCategory();
        self::assertSame($category, $result);
    }

    public function testGetCategoryTree(): void
    {
        $filteredChildCategory = new CategoryStub();
        $filteredChildCategory->setId(4);
        $filteredChildCategory->setLevel(2);
        $filteredChildCategory->setMaterializedPath('1_2_4');

        $childCategory = new CategoryStub();
        $childCategory->setId(3);
        $childCategory->setLevel(2);
        $childCategory->setMaterializedPath('1_2_3');

        $childBarCategory = new CategoryStub();
        $childBarCategory->setId(6);
        $childBarCategory->setLevel(2);
        $childBarCategory->setMaterializedPath('1_5_6');

        $mainBarCategory = new CategoryStub();
        $mainBarCategory->setId(5);
        $mainBarCategory->setLevel(1);
        $mainBarCategory->setMaterializedPath('1_5');
        $mainBarCategory->addChildCategory($childBarCategory);

        $mainCategory = new CategoryStub();
        $mainCategory->setId(2);
        $mainCategory->setLevel(1);
        $mainCategory->setMaterializedPath('1_2');
        $mainCategory->addChildCategory($childCategory);
        $mainCategory->addChildCategory($filteredChildCategory);
        $mainCategory->addChildCategory($mainBarCategory);

        $rootCategory = new CategoryStub();
        $rootCategory->setId(1);
        $rootCategory->setLevel(0);
        $rootCategory->setMaterializedPath('1');
        $rootCategory->addChildCategory($mainCategory);

        $user = new CustomerUser();

        $this->masterCatalogProvider
            ->expects(self::once())
            ->method('getMasterCatalogRoot')
            ->willReturn($rootCategory);

        $this->categoryTreeProvider->expects(self::once())
            ->method('getCategories')
            ->with($user, $rootCategory, null)
            ->willReturn([$mainCategory, $childCategory, $childBarCategory]);

        $actual = $this->categoryProvider->getCategoryTree($user);

        $expectedDTO = new CategoryDTO($mainCategory);
        $expectedDTO->addChildCategory(new CategoryDTO($childCategory));
        $expectedDTO->addChildCategory(new CategoryDTO($childBarCategory));

        self::assertEquals(new ArrayCollection([$expectedDTO]), $actual);
    }

    public function testGetCategoryTreeArray(): void
    {
        $data = [
            [
                'id' => '',
                'title' => 'category_1_2',
                'hasSublist' => 1,
                'childCategories' => [
                [
                        'id' => '',
                        'title' => 'category_1_2_3',
                        'hasSublist' => 0,
                        'childCategories' => []
                    ]
                ]
            ]
        ];

        $organization = new Organization();
        $this->tokenAccessor
            ->expects(self::once())
            ->method('getOrganization')
            ->willReturn($organization);

        $user = new CustomerUser();
        $this->cache->expects(self::once())
            ->method('get')
            ->with('category__0_0_0_')
            ->willReturn($data);

        $actual = $this->categoryProvider->getCategoryTreeArray($user);
        self::assertEquals($data, $actual);
    }

    public function testGetCategoryTreeArrayCached(): void
    {
        $data = [
            [
                'id' => '',
                'title' => 'category_1_2',
                'hasSublist' => 1,
                'childCategories' => []
            ]
        ];
        $user = $this->getEntity(CustomerUser::class, ['id' => 1]);
        $customer = $this->getEntity(Customer::class, ['id' => 2]);
        $user->setCustomer($customer);
        $customerGroup = $this->getEntity(CustomerGroup::class, ['id' => 3]);
        $customer->setGroup($customerGroup);
        $organization = $this->getEntity(CustomerUser::class, ['id' => 4]);
        $localization = $this->getEntity(Localization::class, ['id' => 5]);

        $this->tokenAccessor->expects(self::once())
            ->method('getOrganization')
            ->willReturn($organization);
        $this->localizationHelper->expects(self::any())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        $this->cache->expects(self::once())
            ->method('get')
            ->with('category_1_5_2_3_4')
            ->willReturn($data);

        $actual = $this->categoryProvider->getCategoryTreeArray($user);
        self::assertEquals($data, $actual);
    }

    public function testGetIncludeSubcategoriesChoice(): void
    {
        $this->requestProductHandler
            ->method('getIncludeSubcategoriesChoice')
            ->willReturnOnConsecutiveCalls(true, false);
        self::assertEquals(true, $this->categoryProvider->getIncludeSubcategoriesChoice());
        self::assertEquals(false, $this->categoryProvider->getIncludeSubcategoriesChoice());
    }

    /**
     * @dataProvider getUserDataProvider
     */
    public function testGetCategoryPath(?UserInterface $userFromToken, ?UserInterface $expectedUser): void
    {
        $this->mockTokenAccessor($userFromToken);

        $categoryAId = 1;
        $categoryA = new CategoryStub();
        $categoryA->setId($categoryAId);

        $categoryB = new CategoryStub();
        $categoryB->setId(2);

        $this->requestProductHandler
            ->expects(self::once())
            ->method('getCategoryId')
            ->willReturn($categoryAId);

        $this->categoryRepository
            ->expects(self::once())
            ->method('find')
            ->with($categoryAId)
            ->willReturn($categoryA);

        $parentCategories = [
            $categoryA,
            $categoryB,
        ];
        $this->categoryTreeProvider->expects(self::once())
            ->method('getParentCategories')
            ->with($expectedUser, $categoryA)
            ->willReturn($parentCategories);

        self::assertSame(
            $parentCategories,
            $this->categoryProvider->getCategoryPath()
        );
    }

    public function getUserDataProvider(): array
    {
        $customerUser = new CustomerUserStub(1);

        return [
            'null' => [
                'userFromToken' => null,
                'expectedUser' => null,
            ],
            'not customer user' => [
                'userFromToken' => $this->createMock(UserInterface::class),
                'expectedUser' => null,
            ],
            'customer user' => [
                'userFromToken' => $customerUser,
                'expectedUser' => $customerUser,
            ],
        ];
    }

    private function mockTokenAccessor(?UserInterface $user): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::any())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenAccessor->expects(self::once())
            ->method('getToken')
            ->willReturn($token);
    }
}
