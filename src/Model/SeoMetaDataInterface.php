<?php

namespace Ntriga\PimcoreSeoBundle\Model;

use Ntriga\PimcoreSeoBundle\Middleware\MiddlewareInterface;

interface SeoMetaDataInterface
{
    public function getMiddleware(string $middlewareAdapterName): MiddlewareInterface;

    public function setMetaDescription(string $metaDescription): void;

    public function getMetaDescription(): string;

    public function setOriginalUrl(string $originalUrl): void;

    public function getOriginalUrl(): string;

    public function setTitle(string $title): void;

    public function getTitle(): string;

    public function setCanonicalUrl(string $url): void;

    public function getCanonicalUrl(): string;

    public function setExtraProperties(array|\Traversable $extraProperties): void;

    public function setExtraNames(array|\Traversable $extraNames): void;

    public function setExtraHttp(array|\Traversable $extraHttp): void;

    public function getExtraProperties(): array;

    public function getExtraNames(): array;

    public function getExtraHttp(): array;

    public function addExtraProperty(string $key, string $value);

    public function addExtraName(string $key, string $value);

    public function addExtraHttp(string $key, string $value);

    public function getSchema(): array;

    public function addSchema(array $schemaJsonLd): void;
}
