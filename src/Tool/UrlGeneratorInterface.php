<?php

namespace Ntriga\PimcoreSeoBundle\Tool;

interface UrlGeneratorInterface
{
    public function generate(mixed $element, array $options = []): ?string;

    public function getCurrentSchemeAndHost(): string;
}
