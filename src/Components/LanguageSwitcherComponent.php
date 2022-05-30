<?php

declare(strict_types=1);

namespace App\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('language-switcher')]
class LanguageSwitcherComponent
{
    public function type(string $type=null):string {
        return match ($type){
            'up' => 'dropup',
            default  => ''
        };
    }

}