<?php

namespace Ntriga\PimcoreSeoBundle\Tool;

use Doctrine\DBAL\Exception;
use Pimcore\Db;
use Pimcore\Extension\Bundle\Installer\Exception\InstallationException;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Security\User\TokenStorageUserResolver;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Pimcore\Model\User\Permission;

class Install extends SettingsStoreAwareInstaller
{
    public const REQUIRED_PERMISSION = [
        'seo_bundle_remove_property',
        'seo_bundle_add_property'
    ];

    protected TokenStorageUserResolver $resolver;
    protected DecoderInterface $serializer;

    public function setTokenStorageUserResolver(TokenStorageUserResolver $resolver): void
    {
        $this->resolver = $resolver;
    }

    /**
     * @param DecoderInterface $serializer
     */
    public function setSerializer(DecoderInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    /**
     * @throws Exception
     */
    public function install(): void
    {
        $this->installDbStructure();
        $this->installPermissions();

        parent::install();
    }

    /**
     * @throws Exception
     */
    protected function installDbStructure(): void
    {
        $db = Db::get();
        $db->executeQuery(file_get_contents($this->getInstallSourcesPath() . '/sql/install.sql'));
    }

    protected function installPermissions(): void
    {
        foreach (self::REQUIRED_PERMISSION as $permission){
            $definition = Permission\Definition::getByKey($permission);

            if ($definition){
                continue;
            }

            try {
                Permission\Definition::create($permission);
            } catch (\Throwable $e){
                throw new InstallationException(sprintf('Failed to create permission "%s": %s', $permission, $e->getMessage()));
            }
        }
    }

    protected function getInstallSourcesPath(): string
    {
        return __dir__ . '/../../config/install';
    }

}
