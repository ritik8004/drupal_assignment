<?php

namespace Drupal\alshaya_options_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Url;

/**
 * Controller to add options list page.
 */
class AlshayaOptionsPageController extends ControllerBase {

  /**
   * The Facet Manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * Database connection service object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Cache Backend service for alshaya.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * AlshayaOptionsPageController constructor.
   *
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facetManager
   *   The facet manager service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   */
  public function __construct(DefaultFacetManager $facetManager,
                              Connection $connection,
                              LanguageManagerInterface $language_manager,
                              CacheBackendInterface $cache) {
    $this->facetManager = $facetManager;
    $this->connection = $connection;
    $this->languageManager = $language_manager;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('facets.manager'),
      $container->get('database'),
      $container->get('language_manager'),
      $container->get('cache.default')
    );
  }

  /**
   * Returns the build for options page.
   *
   * @return array
   *   Build array.
   */
  public function optionsPage() {
    $options_list = [];
    $config = $this->config('alshaya_options_list.admin_settings');
    $isEnabled = $config->get('alshaya_options_on_off');
    if ($isEnabled) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      $attributeCodes = array_filter($config->get('alshaya_options_attributes'));
      foreach ($attributeCodes as $attributeCode) {
        // Check for cache first.
        $cid = 'alshaya_options_page:' . $attributeCode . ':' . $langcode;
        if ($cache = $this->cache->get($cid)) {
          $data = $cache->data;
          // If cache hit.
          if (!empty($data)) {
            return $data;
          }
        }
        else {
          $options_list[$attributeCode] = $this->fetchAllTermsForAttribute($attributeCode);
          $this->cache->set($cid, $options_list[$attributeCode], Cache::PERMANENT);
        }
      }
    }
    else {
      throw new AccessDeniedHttpException();
    }

    $options_list = [
      '#theme' => 'alshaya_options_page',
      '#options_list' => $options_list,
    ];
    return $options_list;
  }

  /**
   * Returns the build for options page.
   *
   * @return array
   *   All term names array.
   */
  public function fetchAllTermsForAttribute($attributeCode) {
    $return = [];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['name', 'tid']);
    $query->innerJoin('taxonomy_term__field_sku_attribute_code', 'tfa', 'tfd.tid = tfa.entity_id');
    $query->orderBy('tfd.name');
    $query->condition('tfa.field_sku_attribute_code_value', $attributeCode);
    $query->condition('tfd.langcode', $langcode);
    $options = $query->execute()->fetchAllKeyed(1, 0);
    foreach ($options as $option) {
      $list_object['title'] = $option;
      $option = [
        'query' => [
          'f[0]' => $attributeCode . ':' . $option,
          'sort_bef_combine' => 'search_api_relevance DESC',
          'show_on_load' => '12',
        ],
      ];
      $url = Url::fromUri('internal:/search', $option);
      $list_object['url'] = $url;
      $return[] = $list_object;
    }
    return $return;
  }

}
