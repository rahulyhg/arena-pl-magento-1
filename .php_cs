<?php

use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;
use Symfony\CS\FixerInterface;

return Config::create()
    ->level(FixerInterface::SYMFONY_LEVEL)
    ->fixers(['-concat_without_spaces', 'concat_with_spaces'])
    ->finder(DefaultFinder::create()->in(__DIR__));
