<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LanguageController extends AbstractController
{
    #[Route('/language/{language}', name: 'language')]
    public function languageChange(string $language, SessionInterface $session, Request $request, UrlGeneratorInterface $generator): RedirectResponse
    {
        $session->set('_locale', $language);
        return new RedirectResponse($request->headers->get('referer') ?? $generator->generate('homepage'));
    }
}