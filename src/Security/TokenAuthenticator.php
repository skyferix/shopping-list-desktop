<?php

declare(strict_types=1);

namespace App\Security;

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
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TokenAuthenticator extends AbstractLoginFormAuthenticator
{
    public const LOGIN_ROUTE = 'login';

    private UrlGeneratorInterface $urlGenerator;
    private HttpClientInterface $client;

    public function __construct(UrlGeneratorInterface $urlGenerator, HttpClientInterface $client)
    {
        $this->urlGenerator = $urlGenerator;
        $this->client = $client;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);
        $password = $request->request->get('password', '');

        $response = $this->client->request('POST', 'http://localhost:8003/api/login',[
            'json' => ['email' => $email, 'password' => $password]
        ]);

        $responseStatusCode = $response->getStatusCode();

        if(401 === $responseStatusCode){
            throw new CustomUserMessageAuthenticationException('Invalid credentials');
        }

        if(500 === $responseStatusCode){
            throw new CustomUserMessageAuthenticationException('Something went wrong try again later');
        }

        if(200 !== $responseStatusCode){
            throw new CustomUserMessageAuthenticationException('Something went wrong try again later');
        }

        $apiToken = json_decode($response->getContent())->token;

        return new SelfValidatingPassport(new UserBadge($apiToken), [
            new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
        ]);
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
