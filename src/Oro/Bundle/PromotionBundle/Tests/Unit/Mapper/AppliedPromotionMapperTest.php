<?php

namespace Oro\Bundle\PromotionBundle\Tests\Unit\Mapper;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\PromotionBundle\Entity\AppliedCoupon;
use Oro\Bundle\PromotionBundle\Entity\Coupon;
use Oro\Bundle\PromotionBundle\Entity\DiscountConfiguration;
use Oro\Bundle\PromotionBundle\Entity\Promotion;
use Oro\Bundle\PromotionBundle\Normalizer\NormalizerInterface;
use Oro\Bundle\PromotionBundle\Entity\PromotionDataInterface;
use Oro\Bundle\PromotionBundle\Entity\AppliedPromotion as AppliedPromotionEntity;
use Oro\Bundle\PromotionBundle\Mapper\AppliedPromotionMapper;
use Oro\Bundle\PromotionBundle\Model\AppliedPromotionData;
use Oro\Bundle\RuleBundle\Entity\Rule;
use Oro\Component\Testing\Unit\EntityTrait;

class AppliedPromotionMapperTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /**
     * @var int
     */
    const PROMOTION_ID = 123;

    /**
     * @var string
     */
    const PROMOTION_NAME = 'Order Promotion';

    /**
     * @var string
     */
    const COUPON_ID = 71;

    /**
     * @var string
     */
    const COUPON_CODE = 'summer2010';

    /**
     * @var string
     */
    const DISCOUNT_TYPE = 'order';

    /**
     * @var array
     */
    const DISCOUNT_OPTIONS = ['discount_type' => 'amount'];

    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var NormalizerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $normalizer;

    /**
     * @var AppliedPromotionMapper
     */
    private $appliedPromotionMapper;

    protected function setUp()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->appliedPromotionMapper = new AppliedPromotionMapper($this->registry, $this->normalizer);
    }

    public function testMapPromotionDataToAppliedPromotionWhenNotManagedPromotionGiven()
    {
        $discountConfiguration = (new DiscountConfiguration())
            ->setType(self::DISCOUNT_TYPE)
            ->setOptions(self::DISCOUNT_OPTIONS);

        /** @var PromotionDataInterface|\PHPUnit_Framework_MockObject_MockObject $appliedPromotion **/
        $appliedPromotion = (new AppliedPromotionData())
            ->setDiscountConfiguration($discountConfiguration)
            ->setId(self::PROMOTION_ID)
            ->setRule((new Rule())->setName(self::PROMOTION_NAME));

        $normalizedPromotion = ['rule' => ['name' => self::PROMOTION_NAME]];
        $this->normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($appliedPromotion)
            ->willReturn($normalizedPromotion);

        $foundPromotion = (new Promotion())->setRule((new Rule())->setName(self::PROMOTION_NAME));

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Promotion::class, self::PROMOTION_ID)
            ->willReturn($foundPromotion);

        $this->registry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(Promotion::class)
            ->willReturn($entityManager);

        $expectedAppliedPromotionEntity = $this->getEntity(AppliedPromotionEntity::class, [
            'promotion' => $foundPromotion,
            'promotionName' => self::PROMOTION_NAME,
            'sourcePromotionId' => self::PROMOTION_ID,
            'configOptions' => self::DISCOUNT_OPTIONS,
            'type' => self::DISCOUNT_TYPE,
            'promotionData' => $normalizedPromotion
        ]);

        static::assertEquals(
            $expectedAppliedPromotionEntity,
            $this->appliedPromotionMapper->mapPromotionDataToAppliedPromotion($appliedPromotion)
        );
    }

    public function testMapPromotionDataToAppliedPromotion()
    {
        $discountConfiguration = (new DiscountConfiguration())
            ->setType(self::DISCOUNT_TYPE)
            ->setOptions(self::DISCOUNT_OPTIONS);

        /** @var PromotionDataInterface|\PHPUnit_Framework_MockObject_MockObject $promotion **/
        $promotion = $this->getEntity(Promotion::class, [
            'discountConfiguration' => $discountConfiguration,
            'id' => self::PROMOTION_ID,
            'rule' => (new Rule())->setName(self::PROMOTION_NAME)
        ]);

        $normalizedPromotion = ['rule' => ['name' => self::PROMOTION_NAME]];
        $this->normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($promotion)
            ->willReturn($normalizedPromotion);

        $this->registry
            ->expects($this->never())
            ->method('getManagerForClass');

        $expectedAppliedPromotionEntity = $this->getEntity(AppliedPromotionEntity::class, [
            'promotion' => $promotion,
            'promotionName' => self::PROMOTION_NAME,
            'sourcePromotionId' => self::PROMOTION_ID,
            'configOptions' => self::DISCOUNT_OPTIONS,
            'type' => self::DISCOUNT_TYPE,
            'promotionData' => $normalizedPromotion
        ]);

        static::assertEquals(
            $expectedAppliedPromotionEntity,
            $this->appliedPromotionMapper->mapPromotionDataToAppliedPromotion($promotion)
        );
    }

    public function testMapAppliedPromotionToPromotionDataWithoutAppliedCoupon()
    {
        $promotionData = ['rule' => ['name' => self::PROMOTION_NAME]];

        $discountConfiguration = (new DiscountConfiguration())
            ->setType(self::DISCOUNT_TYPE)
            ->setOptions(self::DISCOUNT_OPTIONS);

        /** @var AppliedPromotionEntity|\PHPUnit_Framework_MockObject_MockObject $appliedPromotionEntity **/
        $appliedPromotionEntity = $this->getEntity(AppliedPromotionEntity::class, [
            'promotionData' => $promotionData,
            'type' => self::DISCOUNT_TYPE,
            'configOptions' => self::DISCOUNT_OPTIONS
        ]);

        $appliedPromotion = (new AppliedPromotionData());

        $this->normalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with($promotionData)
            ->willReturn($appliedPromotion);

        $expectedAppliedPromotion = (new AppliedPromotionData())
            ->setDiscountConfiguration($discountConfiguration);

        $this->registry
            ->expects($this->never())
            ->method('getManagerForClass');

        static::assertEquals(
            $expectedAppliedPromotion,
            $this->appliedPromotionMapper->mapAppliedPromotionToPromotionData($appliedPromotionEntity)
        );
    }

    public function testMapAppliedPromotionToPromotionDataWhenCouponExistsAndNotChanged()
    {
        $promotionData = ['rule' => ['name' => self::PROMOTION_NAME]];

        $discountConfiguration = (new DiscountConfiguration())
            ->setType(self::DISCOUNT_TYPE)
            ->setOptions(self::DISCOUNT_OPTIONS);

        $appliedCoupon = (new AppliedCoupon())
            ->setCouponCode(self::COUPON_CODE)
            ->setSourceCouponId(self::COUPON_ID)
            ->setSourcePromotionId(self::PROMOTION_ID);

        /** @var AppliedPromotionEntity|\PHPUnit_Framework_MockObject_MockObject $appliedPromotionEntity **/
        $appliedPromotionEntity = $this->getEntity(AppliedPromotionEntity::class, [
            'promotionData' => $promotionData,
            'type' => self::DISCOUNT_TYPE,
            'configOptions' => self::DISCOUNT_OPTIONS,
            'appliedCoupon' => $appliedCoupon
        ]);

        $appliedPromotion = (new AppliedPromotionData());

        $this->normalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with($promotionData)
            ->willReturn($appliedPromotion);

        /** @var Coupon $coupon */
        $coupon = $this->getEntity(Coupon::class, [
            'id' => self::COUPON_ID,
            'code' => self::COUPON_CODE,
            'promotion' => $this->getEntity(Promotion::class, ['id' => self::PROMOTION_ID])
        ]);

        $couponManager = $this->createMock(EntityManager::class);
        $couponManager
            ->expects($this->once())
            ->method('find')
            ->with(Coupon::class, self::COUPON_ID)
            ->willReturn($coupon);

        $promotionManager = $this->createMock(EntityManager::class);

        $this->registry
            ->expects($this->any())
            ->method('getManagerForClass')
            ->willReturnMap([
                [Promotion::class, $promotionManager],
                [Coupon::class, $couponManager]
            ]);

        $expectedAppliedPromotion = (new AppliedPromotionData())
            ->setDiscountConfiguration($discountConfiguration)
            ->addCoupon($coupon);

        static::assertEquals(
            $expectedAppliedPromotion,
            $this->appliedPromotionMapper->mapAppliedPromotionToPromotionData($appliedPromotionEntity)
        );
    }

    /**
     * @dataProvider couponDataProvider
     * @param Coupon|null $coupon
     */
    public function testMapAppliedPromotionToPromotionDataWhenCouponNotExistsOrChanged($coupon)
    {
        $promotionData = ['rule' => ['name' => self::PROMOTION_NAME]];

        $discountConfiguration = (new DiscountConfiguration())
            ->setType(self::DISCOUNT_TYPE)
            ->setOptions(self::DISCOUNT_OPTIONS);

        $appliedCoupon = (new AppliedCoupon())
            ->setCouponCode(self::COUPON_CODE)
            ->setSourceCouponId(self::COUPON_ID)
            ->setSourcePromotionId(self::PROMOTION_ID);

        /** @var AppliedPromotionEntity|\PHPUnit_Framework_MockObject_MockObject $appliedPromotionEntity **/
        $appliedPromotionEntity = $this->getEntity(AppliedPromotionEntity::class, [
            'promotionData' => $promotionData,
            'type' => self::DISCOUNT_TYPE,
            'configOptions' => self::DISCOUNT_OPTIONS,
            'appliedCoupon' => $appliedCoupon
        ]);

        $appliedPromotion = (new AppliedPromotionData());

        $this->normalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with($promotionData)
            ->willReturn($appliedPromotion);

        $couponManager = $this->createMock(EntityManager::class);
        $couponManager
            ->expects($this->once())
            ->method('find')
            ->with(Coupon::class, self::COUPON_ID)
            ->willReturn($coupon);

        $promotion = new Promotion();
        $promotionManager = $this->createMock(EntityManager::class);
        $promotionManager
            ->expects($this->once())
            ->method('find')
            ->with(Promotion::class, self::PROMOTION_ID)
            ->willReturn($promotion);

        $this->registry
            ->expects($this->any())
            ->method('getManagerForClass')
            ->willReturnMap([
                [Promotion::class, $promotionManager],
                [Coupon::class, $couponManager]
            ]);

        /** @var Coupon $newCoupon */
        $newCoupon = $this->getEntity(Coupon::class, [
            'code' => self::COUPON_CODE,
            'promotion' => $promotion
        ]);

        $expectedAppliedPromotion = (new AppliedPromotionData())
            ->setDiscountConfiguration($discountConfiguration)
            ->addCoupon($newCoupon);

        static::assertEquals(
            $expectedAppliedPromotion,
            $this->appliedPromotionMapper->mapAppliedPromotionToPromotionData($appliedPromotionEntity)
        );
    }

    /**
     * @return array
     */
    public function couponDataProvider()
    {
        /** @var Promotion $promotion */
        $promotion = $this->getEntity(Promotion::class, ['id' => 1]);

        return [
            'no coupon found' => [
                'coupon' => null
            ],
            'coupon code was changed' => [
                'coupon' => (new Coupon())->setCode('some code')
            ],
            'coupon promotion was deleted' => [
                'coupon' => (new Coupon())->setCode(self::COUPON_CODE)->setPromotion(null)
            ],
            'coupon was assigned to other promotion' => [
                'coupon' => (new Coupon())->setCode(self::COUPON_CODE)->setPromotion($promotion)
            ]
        ];
    }

    public function testMapAppliedPromotionToPromotionDataWhenCouponNotExistsAndPromotionNotExists()
    {
        $promotionData = ['rule' => ['name' => self::PROMOTION_NAME]];

        $discountConfiguration = (new DiscountConfiguration())
            ->setType(self::DISCOUNT_TYPE)
            ->setOptions(self::DISCOUNT_OPTIONS);

        $appliedCoupon = (new AppliedCoupon())
            ->setCouponCode(self::COUPON_CODE)
            ->setSourceCouponId(self::COUPON_ID)
            ->setSourcePromotionId(self::PROMOTION_ID);

        /** @var AppliedPromotionEntity|\PHPUnit_Framework_MockObject_MockObject $appliedPromotionEntity **/
        $appliedPromotionEntity = $this->getEntity(AppliedPromotionEntity::class, [
            'promotionData' => $promotionData,
            'type' => self::DISCOUNT_TYPE,
            'configOptions' => self::DISCOUNT_OPTIONS,
            'appliedCoupon' => $appliedCoupon
        ]);

        $this->normalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with($promotionData)
            ->willReturn(new AppliedPromotionData());

        $couponManager = $this->createMock(EntityManager::class);
        $couponManager
            ->expects($this->once())
            ->method('find')
            ->with(Coupon::class, self::COUPON_ID)
            ->willReturn(null);

        $promotionManager = $this->createMock(EntityManager::class);
        $promotionManager
            ->expects($this->once())
            ->method('find')
            ->with(Promotion::class, self::PROMOTION_ID)
            ->willReturn(null);

        $this->registry
            ->expects($this->any())
            ->method('getManagerForClass')
            ->willReturnMap([
                [Promotion::class, $promotionManager],
                [Coupon::class, $couponManager]
            ]);

        $promotion = $this->getEntity(Promotion::class, ['id' => self::PROMOTION_ID]);

        /** @var Coupon $newCoupon */
        $newCoupon = $this->getEntity(Coupon::class, [
            'code' => self::COUPON_CODE,
            'promotion' => $promotion
        ]);

        $expectedAppliedPromotion = (new AppliedPromotionData())
            ->setDiscountConfiguration($discountConfiguration)
            ->addCoupon($newCoupon);

        static::assertEquals(
            $expectedAppliedPromotion,
            $this->appliedPromotionMapper->mapAppliedPromotionToPromotionData($appliedPromotionEntity)
        );
    }
}