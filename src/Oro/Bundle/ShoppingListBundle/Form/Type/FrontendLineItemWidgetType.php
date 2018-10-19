<?php

namespace Oro\Bundle\ShoppingListBundle\Form\Type;

use Oro\Bundle\ProductBundle\Form\Type\FrontendLineItemType;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Oro\Bundle\ShoppingListBundle\Entity\Repository\ShoppingListRepository;
use Oro\Bundle\ShoppingListBundle\Manager\ShoppingListManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Form type for line item widget.
 */
class FrontendLineItemWidgetType extends AbstractType
{
    const NAME = 'oro_shopping_list_frontend_line_item_widget';

    /**
     * @var ShoppingListManager
     */
    protected $shoppingListManager;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $shoppingListClass;
    
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $translator
     * @param ShoppingListManager $shoppingListManager
     */
    public function __construct(
        ManagerRegistry $registry,
        TranslatorInterface $translator,
        ShoppingListManager $shoppingListManager
    ) {
        $this->registry = $registry;
        $this->translator = $translator;
        $this->shoppingListManager = $shoppingListManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'shoppingList',
                EntityType::class,
                [
                    'mapped' => false,
                    'required' => false,
                    'label' => 'oro.shoppinglist.lineitem.shopping_list.label',
                    'class' => $this->shoppingListClass,
                    'query_builder' => function (ShoppingListRepository $repository) {
                        $qb = $repository->createQueryBuilder('shoppingList');

                        return $qb;
                    },
                    'placeholder' => 'oro.shoppinglist.lineitem.create_new_shopping_list',
                    'acl_options'  => ['permission' => 'EDIT']
                ]
            )
            ->add(
                'shoppingListLabel',
                TextType::class,
                [
                    'mapped' => false,
                    'required' => true,
                    'label' => 'oro.shoppinglist.lineitem.new_shopping_list_label'
                ]
            )
            ->addEventListener(FormEvents::POST_SET_DATA, [$this, 'postSetData'])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        /* @var $lineItem LineItem */
        $lineItem = $event->getData();

        $event->getForm()->get('shoppingList')->setData($lineItem->getShoppingList());
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        if (!$form->get('shoppingList')->getData() && !$form->get('shoppingListLabel')->getData()) {
            $form->get('shoppingListLabel')->addError(
                new FormError($this->translator->trans('oro.shoppinglist.not_empty', [], 'validators'))
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $currentShoppingList = $this->shoppingListManager->getCurrent();
        $view->children['shoppingList']->vars['currentShoppingList'] = $currentShoppingList;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FrontendLineItemType::class;
    }

    /**
     * @param string $shoppingListClass
     */
    public function setShoppingListClass($shoppingListClass)
    {
        $this->shoppingListClass = $shoppingListClass;
    }
}
