<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_options_list\AlshayaOptionsListHelper;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to get options values.
 *
 * @RestResource(
 *   id = "options_list",
 *   label = @Translation("Options List"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/options-list/{page_url}"
 *   }
 * )
 */
class OptionsListResource extends ResourceBase {

  /**
   * Alshaya Options List Service.
   *
   * @var Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * ProductResource constructor.
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
   * @param Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshaya_options_service
   *   Alshaya options service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AlshayaOptionsListHelper $alshaya_options_service,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager,
    CacheBackendInterface $cache
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->alshayaOptionsService = $alshaya_options_service;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->cache = $cache;
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
      $container->get('alshaya_options_list.alshaya_options_service'),
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('cache.data')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns response data based on configured attribute code.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing options term data.
   */
  public function get(string $page_url) {
    $response_data = $this->getOptionsList($this->languageManager->getCurrentLanguage()->getId(), $page_url);

    return new ModifiedResourceResponse($response_data);
  }

  /**
   * Get the options list for the configured attribute code.
   *
   * @param string $langcode
   *   Current lagcode.
   * @param string $page_url
   *   URL configured for options page.
   *
   * @return array
   *   build response array.
   */
  public function getOptionsList(string $langcode, string $page_url) {
    if (!$this->alshayaOptionsService->optionsPageEnabled()) {
      throw new NotFoundHttpException();
    }

    $config = $this->configFactory->get('alshaya_options_list.settings');
    $cache_tags = Cache::mergeTags(
      [AlshayaOptionsListHelper::OPTIONS_PAGE_CACHETAG],
      $config->getCacheTags()
    );

    $options_list = [];
    $attribute_options = $config->get('alshaya_options_pages');
    $attributeCodes = array_filter($attribute_options[$page_url]['attributes']);
    // Check for cache first.
    $cid = 'alshaya_options_page:' . $page_url . ':' . $langcode;
    if ($cache = $this->cache->get($cid)) {
      $data = $cache->data;
      // If cache hit.
      if (!empty($data)) {
        $options_list = $data;
      }
    }
    else {
      foreach ($attributeCodes as $attributeCode) {
        foreach ($attribute_options[$page_url]['attribute_details'][$attributeCode] as $key => $attributeOptions) {
          $option = [];
          $option['terms'] = $this->alshayaOptionsService->fetchAllTermsForAttribute($attributeCode, $attributeOptions['show-images'], $attributeOptions['group']);
          if ($attributeOptions['show-search']) {
            $option['search'] = $options_list[$attributeCode][$key]['search'] = TRUE;
            $options_list[$attributeCode][$key]['search_placeholder'] = $attributeOptions['search-placeholder'];
          }

          if ($attributeOptions['group']) {
            $option['group'] = $options_list[$attributeCode][$key]['group'] = TRUE;
            $option['terms'] = $this->alshayaOptionsService->groupAlphabetically($option['terms']);
          }

          $options_list[$attributeCode][$key]['options'] = [
            'option' => $option,
            'attribute_code' => $attributeCode,
          ];

          $options_list[$attributeCode][$key]['title'] = $attributeOptions['title'];
          $options_list[$attributeCode][$key]['description'] = $attributeOptions['description'];
          $options_list[$attributeCode][$key]['show_image'] = FALSE;
          if ($attributeOptions['show-images']) {
            $options_list[$attributeCode][$key]['show_image'] = TRUE;
          }

          if ($attributeOptions['mobile_title_toggle']) {
            $options_list[$attributeCode][$key]['mobile_title'] = $attributeOptions['mobile_title'];
          }
        }
      }
      $this->cache->set($cid, $options_list, Cache::PERMANENT, $cache_tags);
    }

    // Only show those facets that have values.
    $facet_results = $this->alshayaOptionsService->loadFacetsData($attributeCodes);
    foreach ($options_list as $attribute_key => $attribute_details) {
      foreach ($attribute_details as $no => $attribute_detail) {
        if (isset($attribute_detail['group'])) {
          foreach ($attribute_detail['options']['option']['terms'] as $group_key => $grouped_term) {
            foreach ($grouped_term as $group_term_key => $grouped_term_value) {
              if (!in_array($grouped_term_value['title'], $facet_results[$attribute_key])) {
                unset($options_list[$attribute_key][$no]['options']['option']['terms'][$group_key][$group_term_key]);
              }
            }
          }
          $options_list[$attribute_key][$no]['options']['option']['terms'] = array_filter($options_list[$attribute_key][$no]['options']['option']['terms']);
        }
        else {
          foreach ($attribute_detail['options']['option']['terms'] as $term_key => $term) {
            if (!in_array($term['title'], $facet_results[$attribute_key])) {
              unset($options_list[$attribute_key][$no]['options']['option']['terms'][$term_key]);
            }
          }
        }
      }
    }

    return $options_list;
  }

}
