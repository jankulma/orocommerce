<?php

namespace Oro\Bundle\ValidationBundle\Validator\Constraints;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Validates that one of fields is required.
 */
class NotBlankOneOfValidator extends ConstraintValidator
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     * @param NotBlankOneOf $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        foreach ($constraint->fields as $fieldGroup) {
            $this->processFieldGroup($value, $fieldGroup, $constraint);
        }
    }

    /**
     * @param object|array $value
     * @param array $fieldGroup
     * @param NotBlankOneOf $constraint
     */
    protected function processFieldGroup($value, array $fieldGroup, NotBlankOneOf $constraint)
    {
        $fields = array_keys($fieldGroup);
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($fields as $field) {
            $fieldValue = $accessor->getValue($value, $field);

            if (true === $fieldValue || !empty($fieldValue) || '0' == $fieldValue) {
                return;
            }
        }

        foreach ($fields as $field) {
            /** @var ExecutionContextInterface $context */
            $context = $this->context;
            $context->buildViolation(
                $constraint->message,
                [
                    "%fields%" => implode(', ', array_map(function ($value) {
                        return $this->translator->trans((string) $value);
                    }, $fieldGroup))
                ]
            )
                ->atPath($field)
                ->addViolation();
        }
    }
}
