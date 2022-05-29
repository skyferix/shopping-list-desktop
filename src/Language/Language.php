<?php

declare(strict_types=1);

namespace App\Language;

use Symfony\Contracts\Translation\TranslatorInterface;

class Language
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function flag(): string
    {
        $locale = $this->translator->getLocale();

        return match ($locale){
            'lt' => 'build/images/lt.png',
            'pl' => 'build/images/pl.png',
            default => 'build/images/en.png',
        };
    }

    public function trans(): string
    {
        $locale = $this->translator->getLocale();
        return $this->translator->trans('lang.' . $locale );
    }
}