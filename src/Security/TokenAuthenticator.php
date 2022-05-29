<?php

declare(strict_types=1);

namespace App\Security;

use App\Request\ApiRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractLoginFormAuthenticator
{
    public const LOGIN_ROUTE = 'login';

    private UrlGeneratorInterface $urlGenerator;
    private ApiRequest $api;

    public function __construct(UrlGeneratorInterface $urlGenerator, ApiRequest $api)
    {
        $this->urlGenerator = $urlGenerator;
        $this->api = $api;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);
        $password = $request->request->get('password', '');

        $response = $this->api->login('/login', [
            'json' => ['email' => $email, 'password' => $password]
        ]);

        if($token = $response->getToken()){
            return new SelfValidatingPassport(new UserBadge($token), [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]);
        }

        $statusCode = $response->getStatusCode();

        $error = $response->generateErrorBasedOnStatusCode($statusCode);
        throw new CustomUserMessageAuthenticationException($error,[],$statusCode);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse('/');
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
