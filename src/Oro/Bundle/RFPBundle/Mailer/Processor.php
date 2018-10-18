<?php

namespace Oro\Bundle\RFPBundle\Mailer;

use Oro\Bundle\RFPBundle\Entity\Request;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserInterface;
use Oro\Bundle\UserBundle\Mailer\UserTemplateEmailSender;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Sends quote related email notifications.
 */
class Processor implements LoggerAwareInterface
{
    const CREATE_REQUEST_TEMPLATE_NAME = 'request_create_notification';
    const CONFIRM_REQUEST_TEMPLATE_NAME = 'request_create_confirmation';

    /**
     * @var UserTemplateEmailSender
     */
    private $userTemplateEmailSender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function __construct(UserTemplateEmailSender $userTemplateEmailSender)
    {
        $this->userTemplateEmailSender = $userTemplateEmailSender;
        $this->logger = new NullLogger();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param User    $user
     *
     * @return int
     */
    public function sendRFPNotification(Request $request, User $user): int
    {
        return $this->send($request, $user, self::CREATE_REQUEST_TEMPLATE_NAME);
    }

    /**
     * @param Request $request
     * @param UserInterface $user
     *
     * @return int
     */
    public function sendConfirmation(Request $request, UserInterface $user): int
    {
        return $this->send($request, $user, self::CONFIRM_REQUEST_TEMPLATE_NAME);
    }

    /**
     * @param Request $request
     * @param UserInterface $user
     * @param string $template
     *
     * @return int
     */
    private function send(Request $request, UserInterface $user, $template): int
    {
        try {
            return $this->userTemplateEmailSender->sendUserTemplateEmail($user, $template, ['entity' => $request]);
        } catch (\Swift_SwiftException $exception) {
            $this->logger->error('Unable to send email', [
                'template' => $template,
                'username' => $user->getUsername(),
                'request' => (string) $request,
                'exception' => $exception,
            ]);
        }

        return 0;
    }
}
