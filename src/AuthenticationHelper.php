<?php

namespace Ttskch\Pheetsu;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ttskch\GoogleSheetsApi\Authenticator;
use Ttskch\Pheetsu\Exception\RuntimeException;
use Ttskch\Pheetsu\Exception\SessionNotStartedException;

class AuthenticationHelper
{
    const SESSION_KEY = 'pheetsu.token';

    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Authenticator $authenticator
     * @param SessionInterface|null $session
     */
    public function __construct(Authenticator $authenticator, SessionInterface $session = null)
    {
        $this->authenticator = $authenticator;
        $this->session = $session ?: new Session();
    }

    /**
     * @param bool $forceApprovalPrompt
     */
    public function authenticate($forceApprovalPrompt = false)
    {
        $this->startSession();
        $this->authenticateIfCan();
        $this->authorizeIfNeeded($forceApprovalPrompt);
    }

    /**
     * @return bool
     */
    public function startSession()
    {
        return $this->session->start();
    }

    /**
     * @param Request|null $request
     */
    public function authenticateIfCan(Request $request = null)
    {
        if (!$this->session->isStarted()) {
            throw new SessionNotStartedException();
        }

        $request = $request ?: Request::createFromGlobals();

        if ($error = $request->get('error')) {
            throw new RuntimeException(sprintf('Google authentication Error: %s', $error));
        }

        if ($code = $request->get('code')) {
            $token = $this->authenticator->authenticate($code);
            $this->session->set(self::SESSION_KEY, $token);

            // remove url parameters.
            $nonParametersUrl = preg_replace('/\?[^#]+$/', '', $request->getUri());
            $response = new RedirectResponse($nonParametersUrl);
            $response->send();
        }
    }

    /**
     * @param bool $forceApprovalPrompt
     */
    public function authorizeIfNeeded($forceApprovalPrompt = false)
    {
        if (!$this->session->isStarted()) {
            throw new SessionNotStartedException();
        }

        if ($token = $this->session->get(self::SESSION_KEY)) {
            $this->authenticator->setAccessToken($token);

            return;
        }

        $this->authenticator->authorize($forceApprovalPrompt);
    }
}
