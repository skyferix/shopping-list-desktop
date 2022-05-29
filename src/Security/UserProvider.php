<?php

declare(strict_types=1);

namespace App\Security;

use App\Request\ApiRequest;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private ApiRequest $request;

    public function __construct(ApiRequest $request)
    {
        $this->request = $request;
    }

    public function loadUserByIdentifier($token): UserInterface
    {
        $response = $this->request->request('GET','/user/current',[
            'auth_bearer' => $token
        ]);

        $statusCode = $response->getStatusCode();
        if(200 !== $statusCode){
            return new User();
        }

        $content = $response->getContent();
        $userData = json_decode($content);

        $user = new User();

        $user->setEmail($userData->username);
        $user->setRoles($userData->roles);
        $user->setToken($token);

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
    }
}
