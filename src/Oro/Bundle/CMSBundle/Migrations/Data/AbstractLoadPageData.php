<?php

namespace Oro\Bundle\CMSBundle\Migrations\Data;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AttachmentBundle\Manager\FileManager;
use Oro\Bundle\CMSBundle\Entity\Page;
use Oro\Bundle\DigitalAssetBundle\Entity\DigitalAsset;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\RedirectBundle\Cache\FlushableCacheInterface;
use Oro\Bundle\UserBundle\DataFixtures\UserUtilityTrait;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

/**
 * The base class for fixtures that load storefront pages.
 */
abstract class AbstractLoadPageData extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    use ContainerAwareTrait;
    use UserUtilityTrait;

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            LoadAdminUserData::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $organization = $this->getOrganization($manager);

        $slugRedirectGenerator = $this->container->get('oro_redirect.generator.slug_entity');

        $loadedPages = [];
        foreach ((array)$this->getFilePaths() as $filePath) {
            $pages = $this->loadFromFile($manager, $filePath, $organization);
            foreach ($pages as $page) {
                $manager->persist($page);
                $loadedPages[] = $page;
            }
        }
        $manager->flush();

        foreach ($loadedPages as $page) {
            $slugRedirectGenerator->generate($page, true);
        }

        $cache = $this->container->get('oro_redirect.url_cache');
        if ($cache instanceof FlushableCacheInterface) {
            $cache->flushAll();
        }
        $manager->flush();
    }

    protected function getOrganization(ObjectManager $manager): Organization
    {
        return $manager->getRepository(Organization::class)->getFirst();
    }

    /**
     * @param ObjectManager $manager
     * @param string        $filePath
     * @param Organization  $organization
     *
     * @return Page[]
     */
    protected function loadFromFile(ObjectManager $manager, string $filePath, Organization $organization): array
    {
        $rows = Yaml::parse(file_get_contents($filePath));
        $pages = [];
        foreach ($rows as $reference => $row) {
            $page = new Page();
            $page->addTitle((new LocalizedFallbackValue())->setString($row['title']));
            $page->addSlugPrototype((new LocalizedFallbackValue())->setString($row['slug']));
            $page->setContent($row['content']);
            $page->setOrganization($organization);

            $pages[$reference] = $page;
        }

        return $pages;
    }

    abstract protected function getFilePaths(): string;

    protected function getFilePathsFromLocator(string $path): array|string
    {
        return $this->container->get('file_locator')->locate($path);
    }

    protected function createDigitalAsset(
        ObjectManager $manager,
        FileManager $fileManager,
        string $sourcePath,
        string $title
    ): DigitalAsset {
        $user = $this->getFirstUser($manager);

        $digitalAssetTitle = new LocalizedFallbackValue();
        $digitalAssetTitle->setString($title);
        $manager->persist($digitalAssetTitle);

        $imagePath = $this->getFilePathsFromLocator($sourcePath);
        $sourceFile = $fileManager->createFileEntity(\is_array($imagePath) ? current($imagePath) : $imagePath);
        $sourceFile->setOwner($user);
        $manager->persist($sourceFile);

        $digitalAsset = new DigitalAsset();
        $digitalAsset->addTitle($digitalAssetTitle);
        $digitalAsset->setSourceFile($sourceFile);
        $digitalAsset->setOwner($user);
        $digitalAsset->setOrganization($user->getOrganization());
        $manager->persist($digitalAsset);

        return $digitalAsset;
    }
}
