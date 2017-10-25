<?php

namespace Oro\Bundle\ShoppingListBundle\Datagrid\Extension\MassAction;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerArgs;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use Oro\Bundle\ShoppingListBundle\Generator\MessageGenerator;
use Oro\Bundle\ShoppingListBundle\Handler\ShoppingListLineItemHandler;

class AddProductsMassActionHandler implements MassActionHandlerInterface
{
    /** @var MessageGenerator */
    protected $messageGenerator;

    /**  @var ShoppingListLineItemHandler */
    protected $shoppingListLineItemHandler;

    /**
     * @param ShoppingListLineItemHandler $shoppingListLineItemHandler
     * @param MessageGenerator $messageGenerator
     */
    public function __construct(
        ShoppingListLineItemHandler $shoppingListLineItemHandler,
        MessageGenerator $messageGenerator
    ) {
        $this->shoppingListLineItemHandler = $shoppingListLineItemHandler;
        $this->messageGenerator = $messageGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MassActionHandlerArgs $args)
    {
        $argsParser = new AddProductsMassActionArgsParser($args);
        $shoppingList = $this->shoppingListLineItemHandler->getShoppingList($argsParser->getShoppingListId());

        // Don't allow to add all products
        if (!$shoppingList || !$argsParser->getProductIds()) {
            return $this->generateResponse($args);
        }

        try {
            $addedCnt = $this->shoppingListLineItemHandler->createForShoppingList(
                $shoppingList,
                $argsParser->getProductIds(),
                $argsParser->getUnitsAndQuantities()
            );

            return $this->generateResponse($args, $addedCnt, $shoppingList->getId());
        } catch (AccessDeniedException $e) {
            return $this->generateResponse($args);
        }
    }

    /**
     * @param MassActionHandlerArgs $args
     * @param int $entitiesCount
     * @param int|null $shoppingListId
     *
     * @return MassActionResponse
     */
    protected function generateResponse(MassActionHandlerArgs $args, $entitiesCount = 0, $shoppingListId = null)
    {
        $transChoiceKey = $args->getMassAction()->getOptions()->offsetGetByPath(
            '[messages][success]',
            'oro.shoppinglist.actions.add_success_message'
        );

        return new MassActionResponse(
            $entitiesCount > 0,
            $this->messageGenerator->getSuccessMessage($shoppingListId, $entitiesCount, $transChoiceKey),
            ['count' => $entitiesCount]
        );
    }
}
