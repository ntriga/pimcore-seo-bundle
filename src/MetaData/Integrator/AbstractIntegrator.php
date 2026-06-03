<?php

namespace Ntriga\PimcoreSeoBundle\MetaData\Integrator;

use Pimcore\Tool;

abstract class AbstractIntegrator
{
    protected function findLocaleAwareData(
        mixed $data,
        ?string $requestedLocale,
        string $returnType = 'scalar'
    ): mixed {
        $requestedLocale = $this->normalizeLocale($requestedLocale);

        if ($requestedLocale === null) {
            return null;
        }

        $value = $this->findData($data, $requestedLocale, $returnType);

        if ($value !== null) {
            return $value;
        }

        foreach (Tool::getFallbackLanguagesFor($requestedLocale) as $fallBackLocale) {
            $fallBackLocale = $this->normalizeLocale($fallBackLocale);

            if ($fallBackLocale === null) {
                continue;
            }

            if (null !== $fallBackValue = $this->findData($data, $fallBackLocale, $returnType)) {
                return $fallBackValue;
            }
        }

        return null;
    }

    protected function normalizeLocale(mixed $locale): ?string
    {
        if (!is_string($locale)) {
            return null;
        }

        $locale = str_replace('-', '_', trim($locale));

        return $locale !== '' ? $locale : null;
    }

    protected function findData(mixed $data, string $locale, string $returnType = 'scalar'): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        if (count($data) === 0) {
            return null;
        }

        $index = array_search($locale, array_column($data, 'locale'), true);
        if ($index === false) {
            return null;
        }

        $value = $data[$index]['value'];

        if (empty($value)) {
            return null;
        }

        if ($returnType === 'scalar' && !is_scalar($value)) {
            return null;
        }

        if ($returnType === 'array' && !is_array($value)) {
            return null;
        }

        return $value;
    }
}
