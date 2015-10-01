<?php

namespace OroB2B\Bundle\AccountBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

use OroB2B\Bundle\AccountBundle\Entity\AccountUser;
use OroB2B\Bundle\AccountBundle\Entity\AccountUserRole;

class FrontendAccountUserRoleType extends AbstractAccountUserRoleType
{
    const NAME = 'orob2b_account_frontend_account_user_role';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) use ($options) {
                /** @var AccountUserRole $predefinedRole */
                $predefinedRole = $options['predefined_role'];
                if (!$predefinedRole) {
                    return;
                }

                /** @var AccountUserRole $role */
                $role = $event->getData();
                if (!$role || !$role->getAccount()) {
                    return;
                }

                $accountUsers = $predefinedRole->getAccountUsers()->filter(
                    function (AccountUser $accountUser) use ($role) {
                        return $accountUser->getAccount()->getId() === $role->getAccount()->getId();
                    }
                );

                $event->getForm()->get('appendUsers')->setData($accountUsers);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'access_level_route' => 'orob2b_account_frontend_acl_access_levels',
                'predefined_role' => null,
            ]
        );
    }
}
