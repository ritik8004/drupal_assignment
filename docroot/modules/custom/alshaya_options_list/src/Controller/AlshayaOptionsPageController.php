<?php

namespace Drupal\alshaya_options_list\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller to add options list page.
 */
class AlshayaOptionsPageController extends ControllerBase {

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
   * Request stack.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * AlshayaOptionsPageController constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(Connection $connection,
                              LanguageManagerInterface $language_manager,
                              CacheBackendInterface $cache,
                              RequestStack $request_stack) {
    $this->connection = $connection;
    $this->languageManager = $language_manager;
    $this->cache = $cache;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('language_manager'),
      $container->get('cache.default'),
      $container->get('request_stack')
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
    if (!$config->get('alshaya_options_on_off')) {
      throw new NotFoundHttpException();
    }
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // Get current request uri.
    $request = $this->requestStack->getCurrentRequest()->getRequestUri();
    // Remove query parameters.
    $request = explode('?', $request);
    // Remove langcode.
    $request = str_replace('/' . $langcode . '/', '', $request[0]);
    $attribute_options = $config->get('alshaya_options_pages');
    $attributeCodes = array_filter($attribute_options[$request]['attributes']);
    foreach ($attributeCodes as $attributeCode) {
      // Check for cache first.
      $cid = 'alshaya_options_page:' . $attributeCode . ':' . $langcode;
      if ($cache = $this->cache->get($cid)) {
        $data = $cache->data;
        // If cache hit.
        if (!empty($data)) {
          $options_list[$attributeCode]['terms'] = $data;
        }
      }
      else {
        $options_list[$attributeCode]['terms'] = $this->fetchAllTermsForAttribute($attributeCode, $attribute_options[$request]['attribute_details'][$attributeCode]['show-images'], $attribute_options[$request]['attribute_details'][$attributeCode]['group']);
        $this->cache->set($cid, $options_list[$attributeCode]['terms'], Cache::PERMANENT, ['alshaya-options-page']);
      }
      if ($attribute_options[$request]['attribute_details'][$attributeCode]['show-search']) {
        $search_form = $this->formBuilder()
          ->getForm('\Drupal\alshaya_options_list\Form\AlshayaOptionsListAutocompleteForm');
        $options_list[$attributeCode]['search_form'] = $search_form;
      }

      if ($attribute_options[$request]['attribute_details'][$attributeCode]['group']) {
        $options_list[$attributeCode]['group'] = TRUE;
        $options_list[$attributeCode]['terms'] = $this->groupAlphabetically($options_list[$attributeCode]['terms']);
      }

      $options_list[$attributeCode]['title'] = $attribute_options[$request]['attribute_details'][$attributeCode]['title'];
      $options_list[$attributeCode]['description'] = $attribute_options[$request]['attribute_details'][$attributeCode]['description'];
    }

    $options_list = [
      '#theme' => 'alshaya_options_page',
      '#options_list' => $options_list,
      '#page_title' => $attribute_options[$request]['title'],
      '#description' => $attribute_options[$request]['description'],
    ];

    return $options_list;
  }

  /**
   * Returns the build for options page.
   *
   * @param string $attributeCode
   *   Attribute code.
   * @param bool $showImages
   *   Whether images should be shown with the attribute.
   * @param bool $group
   *   Whether the attribute should be grouped alphabetically or not.
   *
   * @return array
   *   All term names array.
   */
  public function fetchAllTermsForAttribute($attributeCode, $showImages = FALSE, $group = FALSE) {
    $return = [];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['name', 'tid']);
    $query->innerJoin('taxonomy_term__field_sku_attribute_code', 'tfa', 'tfd.tid = tfa.entity_id');
    $query->condition('tfa.field_sku_attribute_code_value', $attributeCode);
    $query->condition('tfd.langcode', $langcode);
    if ($showImages) {
      $query->fields('tfs', ['field_attribute_swatch_image_target_id']);
      $query->innerJoin('taxonomy_term__field_attribute_swatch_image', 'tfs', 'tfa.entity_id = tfs.entity_id');
    }
    if ($group) {
      $query->orderBy('tfd.name');
    }
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

  /**
   * {@inheritdoc}
   */
  public function groupAlphabetically($options_array) {
    $return_array = [];
    foreach ($options_array as $option) {
      $char = strtolower($option['title'][0]);
      $return_array[$char][] = $option;
    }
    return $return_array;
  }

  /**
   * {@inheritdoc}
   */
  public function searchAutocomplete($keyword) {
    return new JsonResponse(['key' => 'value']);
  }

}
