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
        $response = $this->request->request($token, 'GET', '/user/current');

        $statusCode = $response->getStatusCode();
        if (200 !== $statusCode) {
            return new User();
        }

        $content = $response->getContent();
        $userData = $content;

        $user = new User();
        
        $user->setId($userData->id ?? null);
        $user->setEmail($userData->email);
        $user->setRoles($userData->roles);
        $user->setName($userData->name ?? null);
        $user->setSurname($userData->surname ?? null);
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
