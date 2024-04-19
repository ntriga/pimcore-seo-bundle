<?php

namespace Ntriga\PimcoreSeoBundle\Manager;


use Ntriga\PimcoreSeoBundle\Model\ElementMetaDataInterface;

interface ElementMetaDataManagerInterface
{
    public function getMetaDataIntegratorConfiguration(): array;

    public function getMetaDataIntegratorBackendConfiguration(mixed $correspondingElement): array;

    /**
     * @return array<int, ElementMetaDataInterface>
     */
    public function getElementData(string $elementType, int $elementId): array;

    public function getElementDataForBackend(string $elementType, int $elementId): array;

//    public function getElementDataForXliffExport(string $elementType, int $elementId, string $locale): array;
//
//    public function saveElementDataFromXliffImport(string $elementType, int $elementId, array $rawData, string $locale): void;

    public function saveElementData(string $elementType, int $elementId, string $integratorName, array $data, bool $merge = false): void;

//    public function generatePreviewDataForElement(string $elementType, int $elementId, string $integratorName, ?string $template, array $data): array;

    public function deleteElementData(string $elementType, int $elementId): void;
}
