<?php

namespace Ntriga\PimcoreSeoBundle\Model;

use Ntriga\PimcoreSeoBundle\Middleware\MiddlewareDispatcherInterface;
use Ntriga\PimcoreSeoBundle\Middleware\MiddlewareInterface;

class SeoMetaData implements SeoMetaDataInterface
{
    private MiddlewareDispatcherInterface $middlewareDispatcher;

    private int $id;
    private string $originalUrl;
    private bool $indexPage = true;
    private string $metaDescription = '';
    private string $title = '';
    private string $canonicalUrl = '';
    private array $extraProperties = [];
    private array $extraNames = [];
    private array $extraHttp = [];
    private array $schema = [];

    /**
     * @deprecated
     */
    private array $raw = [];

    public function __construct(MiddlewareDispatcherInterface $middlewareDispatcher)
    {
        $this->middlewareDispatcher = $middlewareDispatcher;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMiddleware(string $middlewareAdapterName): MiddlewareInterface
    {
        return $this->middlewareDispatcher->buildMiddleware($middlewareAdapterName, $this);
    }

    public function setMetaDescription($metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setOriginalUrl(string $originalUrl): void
    {
        $this->originalUrl = $originalUrl;
    }

    public function getOriginalUrl(): string
    {
        return $this->originalUrl;
    }

    /**
     * @param bool $indexPage
     */
    public function setIndexPage(bool $indexPage): void
    {
        $this->indexPage = $indexPage;
    }

    public function getIndexPage(): bool
    {
        return $this->indexPage;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getCanonicalUrl(): string
    {
        return $this->canonicalUrl;
    }

    /**
     * @param string $canonicalUrl
     */
    public function setCanonicalUrl(string $url): void
    {
        $this->canonicalUrl = $url;
    }

    public function setExtraProperties(array|\Traversable $extraProperties): void
    {
        $this->extraProperties = $this->toArray($extraProperties);
    }

    public function getExtraProperties(): array
    {
        return $this->extraProperties;
    }

    public function addExtraProperty($key, $value): void
    {
        $this->extraProperties[$key] = (string) $value;
    }

    public function removeExtraProperty($key): void
    {
        if (array_key_exists($key, $this->extraProperties)) {
            unset($this->extraProperties[$key]);
        }
    }

    public function setExtraNames(array|\Traversable $extraNames): void
    {
        $this->extraNames = $this->toArray($extraNames);
    }

    public function getExtraNames(): array
    {
        return $this->extraNames;
    }

    public function addExtraName(string $key, string $value): void
    {
        $this->extraNames[$key] = $value;
    }

    public function removeExtraName(string $key): void
    {
        if (array_key_exists($key, $this->extraNames)) {
            unset($this->extraNames[$key]);
        }
    }

    public function setExtraHttp(array|\Traversable $extraHttp): void
    {
        $this->extraHttp = $this->toArray($extraHttp);
    }

    public function getExtraHttp(): array
    {
        return $this->extraHttp;
    }

    public function addExtraHttp(string $key, string $value): void
    {
        $this->extraHttp[$key] = (string) $value;
    }

    public function removeExtraHttp(string $key): void
    {
        if (array_key_exists($key, $this->extraHttp)) {
            unset($this->extraHttp[$key]);
        }
    }

    public function getSchema(): array
    {
        return $this->schema;
    }

    public function addSchema(array $schemaJsonLd): void
    {
        $this->schema[] = $schemaJsonLd;
    }

    public function getRaw(): array
    {
        return $this->raw;
    }

    public function addRaw(string $value): void
    {
        $this->raw[] = $value;
    }

    private function toArray(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof \Traversable) {
            return iterator_to_array($data);
        }

        throw new \InvalidArgumentException(
            sprintf('Expected array or Traversable, got "%s"', is_object($data) ? get_class($data) : gettype($data))
        );
    }
}
