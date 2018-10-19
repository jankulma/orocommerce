<?php

namespace Oro\Bundle\RedirectBundle\EventListener;

use Oro\Bundle\RedirectBundle\Entity\Redirect;
use Oro\Bundle\RedirectBundle\Entity\Repository\RedirectRepository;
use Oro\Bundle\RedirectBundle\Routing\MatchedUrlDecisionMaker;
use Oro\Bundle\RedirectBundle\Routing\SluggableUrlGenerator;
use Oro\Bundle\ScopeBundle\Manager\ScopeManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Catches the 404 exceptions from the router and tries to find the correct redirects for them.
 */
class RedirectExceptionListener
{
    /**
     * @var RedirectRepository
     */
    private $repository;

    /**
     * @var ScopeManager
     */
    private $scopeManager;

    /**
     * @var MatchedUrlDecisionMaker
     */
    private $matchedUrlDecisionMaker;

    /**
     * @param RedirectRepository $repository
     * @param ScopeManager $scopeManager
     * @param MatchedUrlDecisionMaker $matchedUrlDecisionMaker
     */
    public function __construct(
        RedirectRepository $repository,
        ScopeManager $scopeManager,
        MatchedUrlDecisionMaker $matchedUrlDecisionMaker
    ) {
        $this->repository = $repository;
        $this->scopeManager = $scopeManager;
        $this->matchedUrlDecisionMaker = $matchedUrlDecisionMaker;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$this->isRedirectRequired($event)) {
            return;
        }

        $request = $event->getRequest();
        $url = $this->getUrlFromRequest($request);
        $redirect = $this->getApplicableRedirect($url);
        if ($redirect) {
            $to = $this->getFullUrl($redirect->getTo(), $request->getBaseUrl());
            $event->setResponse(new RedirectResponse($to, $redirect->getType()));
        }
    }

    /**
     * @param GetResponseForExceptionEvent $event
     * @return bool
     */
    private function isRedirectRequired(GetResponseForExceptionEvent $event)
    {
        return $event->isMasterRequest()
            && !$event->hasResponse()
            && $this->matchedUrlDecisionMaker->matches($event->getRequest()->getPathInfo())
            && $event->getException() instanceof NotFoundHttpException;
    }

    /**
     * @param string $url
     * @param string $baseUrl
     * @return string
     */
    private function getFullUrl($url, $baseUrl)
    {
        $urlParts = [];
        if ($baseUrl && $baseUrl !== '/') {
            $urlParts = [trim($baseUrl, '/')];
        }

        $urlParts[] = ltrim($url, '/');

        return '/' . implode('/', $urlParts);
    }

    /**
     * @param string $url
     * @return null|Redirect
     */
    private function getApplicableRedirect($url)
    {
        $scopeCriteria = $this->scopeManager->getCriteria('web_content');
        $delimiter = sprintf('/%s/', SluggableUrlGenerator::CONTEXT_DELIMITER);

        if (strpos($url, $delimiter) !== false) {
            list($contextUrl, $itemSlugPrototype) = explode($delimiter, $url);

            $contextRedirect = $this->repository->findByUrl($contextUrl, $scopeCriteria);
            $prototypeRedirect = $this->repository->findByPrototype($itemSlugPrototype, $scopeCriteria);

            if ($contextRedirect || $prototypeRedirect) {
                $contextRedirectUrl = $contextRedirect ? $contextRedirect->getTo() : $contextUrl;
                $prototypeUrl = $prototypeRedirect ? $prototypeRedirect->getToPrototype() : $itemSlugPrototype;

                $redirect = new Redirect();
                $redirect->setTo($contextRedirectUrl . $delimiter . $prototypeUrl);
                $redirect->setType(Redirect::MOVED_PERMANENTLY);

                return $redirect;
            }
        }

        return $this->repository->findByUrl($url, $scopeCriteria);
    }

    /**
     * @param Request $request
     * @return string
     */
    private function getUrlFromRequest(Request $request)
    {
        $url = $request->getPathInfo();
        if ($url !== '/') {
            $url = rtrim($url, '/');
        }

        return $url;
    }
}
