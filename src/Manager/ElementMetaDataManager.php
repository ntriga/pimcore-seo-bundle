<?php

namespace Ntriga\PimcoreSeoBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Ntriga\PimcoreSeoBundle\Model\ElementMetaData;
use Ntriga\PimcoreSeoBundle\Model\ElementMetaDataInterface;
use Ntriga\PimcoreSeoBundle\Registry\MetaDataIntegratorRegistryInterface;
use Ntriga\PimcoreSeoBundle\Repository\ElementMetaDataRepositoryInterface;
use Pimcore\Model\Document;

class ElementMetaDataManager implements ElementMetaDataManagerInterface
{
    public function __construct(
        protected array $integratorConfiguration,
        protected EntityManagerInterface $entityManager,
        protected MetaDataIntegratorRegistryInterface $metaDataIntegratorRegistry,
        protected ElementMetaDataRepositoryInterface $elementMetaDataRepository
    )
    {}

    public function getMetaDataIntegratorConfiguration(): array
    {
        return $this->integratorConfiguration;
    }

    public function getMetaDataIntegratorBackendConfiguration(mixed $correspondingElement): array
    {
        $configuration = [];

        foreach ($this->integratorConfiguration['enabled_integrator'] as $enabledIntegrator){
            $enabledIntegratorName = $enabledIntegrator['integrator_name'];
            $metaDataIntegrator = $this->metaDataIntegratorRegistry->has($enabledIntegratorName) ? $this->metaDataIntegratorRegistry->get($enabledIntegratorName) : null;
            dd($metaDataIntegrator);
            $config = $metaDataIntegrator === null ? [] : $metaDataIntegrator->getBackendConfiguration($correspondingElement);
            $configuration[$enabledIntegratorName] = $config;
        }

        return $configuration;
    }

    public function getElementData(string $elementType, int $elementId): array
    {
        $elementValues = $this->elementMetaDataRepository->findAll($elementType, $elementId);


        return $this->checkForLegacyData($elementValues, $elementType, $elementId);
    }

    /**
     * @throws Exception
     */
    public function getElementDataForBackend(string $elementType, int $elementId): array
    {
        $parsedData = [];
        $data = $this->getElementData($elementType, $elementId);


        foreach ($data as $element){
            $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($element->getIntegrator());
            $parsedData[$element->getIntegrator()] = $metaDataIntegrator->validateBeforeBackend($elementType, $elementId, $element->getData());
        }

        return $this->checkForLegacyBackendData($parsedData, $elementType, $elementId);
    }

    public function saveElementData(string $elementType, int $elementId, string $integratorName, array $data, bool $merge = false): void
    {
        $elementMetaData = $this->elementMetaDataRepository->findByIntegrator($elementType, $elementId, $integratorName);

        if (!$elementMetaData instanceof ElementMetaDataInterface){
            $elementMetaData = new ElementMetaData();
            $elementMetaData->setElementType($elementType);
            $elementMetaData->setElementId($elementId);
            $elementMetaData->setIntegrator($integratorName);
        }

        $metaDataIntegrator = $this->metaDataIntegratorRegistry->get($integratorName);
        $sanitizedData = $metaDataIntegrator->validateBeforePersist($elementType, $elementId, $data, $elementMetaData->getData(), $merge);

        // Remove empty meta data
        if ($sanitizedData === null){
            if ($elementMetaData->getId() > 0){
                $this->entityManager->remove($elementMetaData);
                $this->entityManager->flush();
            }

            return;
        }

        $elementMetaData->setData($sanitizedData);

        $this->entityManager->persist($elementMetaData);
        $this->entityManager->flush();
    }

    public function deleteElementData(string $elementType, int $elementId): void
    {
        $elementData = $this->elementMetaDataRepository->findAll($elementType, $elementId);

        if (count($elementData) === 0){
            return;
        }

        foreach ($elementData as $element){
            $this->entityManager->remove($element);
        }

        $this->entityManager->flush();
    }


    /**
     * @param array $elements
     * @param string $elementType
     * @param int $elementId
     * @return array<int, ElementMetaDataInterface>
     */
    protected function checkForLegacyData(array $elements, string $elementType, int $elementId): array
    {
        // as soon we have configured seo elements,
        // we'll never check the document again. It's all about performance.

        if (count($elements) > 0){

            return $elements;
        }

        if ($elementType !== 'document'){
            return $elements;
        }

        $legacyData = $this->getDocumentLegacyData($elementId);
        if ($legacyData === null){
            return $elements;
        }


        if ($legacyData['hasTitleDescriptionIntegrator'] === true){
            $legacyTitleDescription = new ElementMetaData();
            $legacyTitleDescription->setElementType($elementType);
            $legacyTitleDescription->setElementId($elementId);
            $legacyTitleDescription->setIntegrator('title_description');
            $legacyTitleDescription->setData(['title' => $legacyData['title'], 'description' => $legacyData['description']]);
            $elements[] = $legacyTitleDescription;
        }

        return $elements;
    }

    protected function getDocumentLegacyData(int $documentId): ?array
    {
        $enabledIntegrator = $this->integratorConfiguration['enabled_integrator'];
        if (!is_array($enabledIntegrator) || count($enabledIntegrator) === 0){
            return null;
        }

        $hasTitleDescriptionIntegrator = array_search('title_description', array_column($enabledIntegrator, 'integrator_name'), true);

        if ($hasTitleDescriptionIntegrator === false){
            return null;
        }

        $document = Document::getById($documentId);
        if (!$document instanceof Document\Page){
            return null;
        }

        return [
            'description' => $document->getDescription(),
            'title' => $document->getTitle(),
            'hasTitleDescriptionIntegrator' => $hasTitleDescriptionIntegrator != false
        ];
    }

    protected function checkForLegacyBackendData(array $parsedData, string $elementType, int $elementId): array
    {
        // as soon we have configured seo elements,
        // we'll never check the document again. It's all about performance.
        if (count($parsedData) !== 0){
            return $parsedData;
        }

        if ($elementType !== 'document'){
            return $parsedData;
        }

        $legacyData = $this->getDocumentLegacyData($elementId);
        if ($legacyData === null){
            return $parsedData;
        }

        if ($legacyData['hasTitleDescriptionIntegrator'] === true){
            $legacyTitleDescription = [];
            if (!empty($legacyData['title'])){
                $legacyTitleDescription['title'] = $legacyData['title'];
            }

            if (!empty($legacyData['description'])){
                $legacyTitleDescription['discription'] = $legacyData['description'];
            }

            if (count($legacyTitleDescription) > 0){
                $parsedData['title_description'] = $legacyTitleDescription;
            }
        }

        return $parsedData;
    }
}
