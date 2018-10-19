<?php

namespace Oro\Bundle\ProductBundle\Tests\Unit\Formatter;

use Oro\Bundle\ProductBundle\Entity\MeasureUnitInterface;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ProductBundle\Formatter\UnitLabelFormatter;
use Oro\Bundle\ProductBundle\Formatter\UnitLabelFormatterInterface;

class UnitLabelFormatterTest extends UnitLabelFormatterTestCase
{
    const TRANSLATION_PREFIX = 'oro.product_unit';

    /**
     * {@inheritdoc}
     */
    protected function createObject($code): MeasureUnitInterface
    {
        $unit = new ProductUnit();
        $unit->setCode($code);

        return $unit;
    }

    /**
     * {@inheritdoc}
     */
    protected function createFormatter(): UnitLabelFormatterInterface
    {
        $formatter = new UnitLabelFormatter($this->translator);
        $formatter->setTranslationPrefix($this->getTranslationPrefix());

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTranslationPrefix(): string
    {
        return static::TRANSLATION_PREFIX;
    }
}
