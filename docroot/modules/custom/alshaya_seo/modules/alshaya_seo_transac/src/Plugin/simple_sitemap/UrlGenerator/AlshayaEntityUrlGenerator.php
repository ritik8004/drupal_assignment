<?php

namespace Drupal\alshaya_seo_transac\Plugin\simple_sitemap\UrlGenerator;

use Drupal\alshaya_acm_product\ProductCategoryHelper;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\node\NodeInterface;
use Drupal\simple_sitemap\EntityHelper;
use Drupal\simple_sitemap\Logger;
use Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\EntityUrlGenerator;
use Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator\UrlGeneratorManager;
use Drupal\simple_sitemap\Simplesitemap;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;

/**
 * Class Alshaya Entity Url Generator.
 *
 * @package Drupal\simple_sitemap\Plugin\simple_sitemap\UrlGenerator
 *
 * @UrlGenerator(
 *   id = "alshaya_entity",
 *   label = @Translation("Alshaya Entity URL generator"),
 *   description = @Translation("Generates URLs for entity bundles and bundle overrides."),
 * )
 */
class AlshayaEntityUrlGenerator extends EntityUrlGenerator {

  /**
   * The product category manager.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategory;

  /**
   * Product Category Helper service object.
   *
   * @var \Drupal\alshaya_acm_product\ProductCategoryHelper
   */
  protected $productCategoryHelper;

  /**
   * The config factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              Simplesitemap $generator,
                              Logger $logger,
                              LanguageManagerInterface $language_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              EntityHelper $entityHelper,
                              UrlGeneratorManager $url_generator_manager,
                              ProductCategoryTree $product_category,
                              ProductCategoryHelper $product_category_helper,
                              ConfigFactoryInterface $config_factory,
                              MemoryCacheInterface $memory_cache) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $generator,
      $logger,
      $language_manager,
      $entity_type_manager,
      $entityHelper,
      $url_generator_manager,
      $memory_cache
    );

    $this->productCategory = $product_category;
    $this->productCategoryHelper = $product_category_helper;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('simple_sitemap.generator'),
      $container->get('simple_sitemap.logger'),
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('simple_sitemap.entity_helper'),
      $container->get('plugin.manager.simple_sitemap.url_generator'),
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('alshaya_acm_product.category_helper'),
      $container->get('config.factory'),
      $container->get('entity.memory_cache')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDataSets() {
    if ($this->sitemapVariant !== 'default') {
      return $this->getVariantDataSets();
    }

    $data_sets = [];
    $sitemap_entity_types = $this->entityHelper->getSupportedEntityTypes();

    foreach ($this->generator->setVariants($this->sitemapVariant)->getBundleSettings() as $entity_type_name => $bundles) {
      // Do it only for taxonomy terms and nodes.
      if (isset($sitemap_entity_types[$entity_type_name])) {

        // Skip this entity type if another plugin is written to override
        // its generation.
        foreach ($this->urlGeneratorManager->getDefinitions() as $plugin) {
          if (isset($plugin['settings']['overrides_entity_type'])
            && $plugin['settings']['overrides_entity_type'] === $entity_type_name) {
            continue 2;
          }
        }

        $entityTypeStorage = $this->entityTypeManager->getStorage($entity_type_name);
        $keys = $sitemap_entity_types[$entity_type_name]->getKeys();

        foreach ($bundles as $bundle_name => $bundle_settings) {
          if (empty($bundle_settings['index'])) {
            continue;
          }

          // Alshaya Custom condition 1.
          // For products it will be handled in separate variants.
          if ($bundle_name === 'acq_product') {
            continue;
          }

          $query = $entityTypeStorage->getQuery();

          // Alshaya Custom condition 2.
          // For category terms, get only the enabled ones.
          if ($entity_type_name === 'taxonomy_term' && $bundle_name === 'acq_product_category') {
            $query->condition('field_commerce_status', 1);
          }

          if (empty($keys['id'])) {
            $query->sort($keys['id'], 'ASC');
          }
          if (!empty($keys['bundle'])) {
            $query->condition($keys['bundle'], $bundle_name);
          }
          if (!empty($keys['status'])) {
            $query->condition($keys['status'], 1);
          }

          foreach ($query->execute() as $entity_id) {
            $data_sets[] = [
              'entity_type' => $entity_type_name,
              'id' => $entity_id,
            ];
          }

          if ($bundle_name === 'advanced_page') {
            // Remove homepage nid as we are getting the homepage node alias in
            // the sitemap which is not required. The homepage url shall be
            // added through hook_simple_sitemap_arbitrary_links_alter().
            $homepage_nid = $this->configFactory->get('alshaya_master.home')->get('entity')['id'];
            $homepage_nid_key = array_search($homepage_nid, array_column($data_sets, 'id'));
            unset($data_sets[$homepage_nid_key]);
          }
        }
      }
    }

    return $data_sets;
  }

  /**
   * Wrapper function to get variant label from name.
   *
   * @return string
   *   Variant Label.
   */
  private function getVariantLabel() {
    static $variants;
    if (empty($variants)) {
      $variants = $this->generator->getSitemapManager()->getSitemapVariants();
    }

    $variant = $variants[$this->sitemapVariant];
    return $variant['label'];
  }

  /**
   * Wrapper function to get data set for specific variant.
   *
   * @return array
   *   Data set for the variant.
   */
  private function getVariantDataSets() {
    $data_sets = [];

    /** @var \Drupal\taxonomy\TermStorage $storage */
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $tree = $storage->loadTree('acq_product_category', $this->getVariantLabel(), NULL, FALSE);
    $children = array_column($tree, 'tid');
    // Including the parent along with the children.
    $all_terms = array_merge([$this->getVariantLabel()], $children);

    if (empty($all_terms)) {
      return $data_sets;
    }

    $entityTypeStorage = $this->entityTypeManager->getStorage('node');
    $query = $entityTypeStorage->getQuery();
    $query->sort('nid', 'ASC');
    $query->condition('type', 'acq_product');
    $query->condition('status', NodeInterface::PUBLISHED);
    $query->condition('field_category', $all_terms, 'IN');

    foreach ($query->execute() as $entity_id) {
      $data_sets[] = [
        'entity_type' => 'node',
        'id' => $entity_id,
      ];
    }

    return $data_sets;
  }

  /**
   * {@inheritdoc}
   */
  protected function processDataSet($data_set) {
    $entity = $this->entityTypeManager->getStorage($data_set['entity_type'])->load($data_set['id']);
    if ($entity instanceof ContentEntityInterface) {
      if ($entity->bundle() === 'acq_product_category' && !($entity->get('field_commerce_status')->getString())) {
        return FALSE;
      }
      elseif ($entity->bundle() === 'acq_product') {
        $terms = $this->productCategoryHelper->getBreadcrumbTermList($entity->get('field_category')->getValue());
        foreach (array_reverse($terms) as $term) {
          if ($term->id() == $this->getVariantLabel()) {
            return $this->processDataSetAsDefault($data_set);
          }
        }

        return FALSE;
      }
    }

    return $this->processDataSetAsDefault($data_set);
  }

  /**
   * Wrapper function to process data set with default context.
   *
   * @param mixed $data_set
   *   Data set to process.
   *
   * @return mixed
   *   Data set or false.
   */
  protected function processDataSetAsDefault($data_set) {
    $variant = $this->sitemapVariant;
    $this->sitemapVariant = 'default';
    $return = parent::processDataSet($data_set);
    $this->sitemapVariant = $variant;
    return $return;
  }

}
