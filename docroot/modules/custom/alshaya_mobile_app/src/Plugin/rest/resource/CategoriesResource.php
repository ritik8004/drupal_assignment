<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;

/**
 * Provides a resource to get list of all categories.
 *
 * @RestResource(
 *   id = "categories",
 *   label = @Translation("List all categories"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/category/all"
 *   }
 * )
 */
class CategoriesResource extends ResourceBase {

  /**
   * Array of term urls for dependencies.
   *
   * @var array
   */
  protected $termUrls = [];

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * File storage object.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * AlshayaErrorMessages constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              LanguageManagerInterface $language_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ProductCategoryTree $product_category_tree,
                              Connection $connection,
                              MobileAppUtility $mobile_app_utility) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->productCategoryTree = $product_category_tree;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->connection = $connection;
    $this->mobileAppUtility = $mobile_app_utility;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('database'),
      $container->get('alshaya_mobile_app.utility')
    );
  }

  /**
   * Return the term objects.
   *
   * @param int $langcode
   *   (optional) The language code.
   * @param int $parent
   *   (optional) The parent term id.
   *
   * @return \Drupal\taxonomy\TermInterface[]
   *   The array containing Term objects.
   */
  private function getAllCategories($langcode, $parent = 0) {
    $data = [];
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $terms = $this->productCategoryTree->allChildTerms($langcode, $parent, FALSE);
    foreach ($terms as $term) {
      $term_url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid])->toString(TRUE);
      $this->termUrls[] = $term_url;

      $record = [
        'id' => $term->tid,
        'name' => $term->name,
        'path' => $term_url->getGeneratedUrl(),
        'deeplink' => $this->mobileAppUtility->getDeepLink($term),
        'include_in_menu' => (bool) $term->include_in_menu,
      ];

      if (is_object($file = $this->getPromoBanner($langcode, $term->tid))) {
        $image = $this->fileStorage->load($file->field_promotion_banner_target_id);
        $record['promo_banner'] = [
          'url' => file_create_url($image->getFileUri()),
          'alt' => $file->field_promotion_banner_alt ? $file->field_promotion_banner_alt : '',
        ];
      }

      $record['child'] = $this->getAllCategories($langcode, $term->tid);

      $data[] = $record;
    }
    return $data;
  }

  /**
   * Gets the image from 'field_promotion_banner' field.
   *
   * @param string $langcode
   *   Language code.
   * @param int $tid
   *   Taxonomy term id.
   *
   * @return array
   *   Array of fiel.
   */
  private function getPromoBanner($langcode, $tid) {
    $query = $this->connection->select('taxonomy_term__field_promotion_banner', 'ttbc');
    $query->fields('ttbc', [
      'entity_id',
      'field_promotion_banner_target_id',
      'field_promotion_banner_alt',
    ]);
    $query->condition('ttbc.entity_id', $tid);
    $query->condition('ttbc.langcode', $langcode);
    $query->condition('ttbc.bundle', ProductCategoryTree::VOCABULARY_ID);
    return $query->execute()->fetchObject();
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    $response_data = $this->getAllCategories($this->languageManager->getCurrentLanguage()->getId());
    $response = new ResourceResponse($response_data);
    $this->addCacheableDependency($response);
    return $response;
  }

  /**
   * Adding content dependency to the response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   Response object.
   */
  protected function addCacheableDependency(ResourceResponse $response) {
    if (!empty($this->termUrls)) {
      foreach ($this->termUrls as $urls) {
        $response->addCacheableDependency($urls);
      }
    }

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => [ProductCategoryTree::CACHE_TAG],
      ],
    ]));
  }

}
