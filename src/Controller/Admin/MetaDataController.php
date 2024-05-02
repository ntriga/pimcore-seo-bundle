<?php

namespace Ntriga\PimcoreSeoBundle\Controller\Admin;

use Ntriga\PimcoreSeoBundle\Manager\ElementMetaDataManagerInterface;
use Ntriga\PimcoreSeoBundle\Tool\LocaleProviderInterface;
use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MetaDataController extends AdminAbstractController
{
    public function __construct(
        protected ElementMetaDataManagerInterface $elementMetaDataManager,
        protected LocaleProviderInterface $localeProvider
    )
    {}

    public function getMetaDataDefinitionsAction(): JsonResponse
    {
        return $this->json([
            'configuration' => $this->elementMetaDataManager->getMetaDataIntegratorConfiguration()
        ]);
    }


    /**
     * @throws \Exception
     * @param Request $request
     * @return JsonResponse
     */
    public function getElementMetaDataConfigurationAction(Request $request): JsonResponse
    {
        $element = null;
        $availableLocales = null;

        $elementId = (int) $request->query->get('elementId', 0);
        $elementType = $request->query->get('elementType');

        if ($elementType === 'object'){
            $element = DataObject::getById($elementId);
            $availableLocales = $this->localeProvider->getAllowedLocalesForObject($element);
        } elseif ($elementType  === 'document'){
            $element = Document::getById($elementId);
        }

        $configuration = $this->elementMetaDataManager->getMetaDataIntegratorBackendConfiguration($element);
        $data = $this->elementMetaDataManager->getElementDataForBackend($elementType, $elementId);


        return $this->adminJson([
            'success' => true,
            'data' => $data,
            'availableLocales' => $availableLocales,
            'configuration' => $configuration
        ]);
    }

    /**
     * @throws \JsonException
     */
    public function setElementMetaDataConfigurationAction(Request $request): JsonResponse
    {
        $elementId = (int) $request->request->get('elementId', 0);
        $elementType = $request->get('elementType');
        $integratorValues = json_decode($request->request->get('integratorValues'), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($integratorValues)){
            return $this->adminJson(['success' => true]);
        }

        foreach ($integratorValues as $integratorName => $integratorData){
            $sanitizedData = is_array($integratorData) ? $integratorData : [];
            $this->elementMetaDataManager->saveElementData($elementType, $elementId, $integratorName, $sanitizedData);
        }

        return $this->adminJson(['success' => true]);
    }


}
