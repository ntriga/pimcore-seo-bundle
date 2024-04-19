<?php

namespace Ntriga\PimcoreSeoBundle\Model;

interface ElementMetaDataInterface
{
    public function getId(): ?int;

    public function setElementType(string $elementType): void;

    public function getElementType(): string;

    public function setElementId(int $elementId): void;

    public function getElementId(): int;

    public function setIntegrator(string $integrator): void;

    public function getIntegrator(): string;

    public function setData(array $data): void;

    public function getData(): array;
}
