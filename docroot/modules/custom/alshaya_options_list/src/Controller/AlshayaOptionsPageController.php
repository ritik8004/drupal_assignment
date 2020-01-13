<?php

namespace Drupal\alshaya_options_list\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\alshaya_options_list\AlshayaOptionsListHelper;

/**
 * Controller to add options list page.
 */
class AlshayaOptionsPageController extends ControllerBase {

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
   * Alshaya Options List Service.
   *
   * @var Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * AlshayaOptionsPageController constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshaya_options_service
   *   Alshaya options service.
   */
  public function __construct(LanguageManagerInterface $language_manager,
                              CacheBackendInterface $cache,
                              RequestStack $request_stack,
                              AlshayaOptionsListHelper $alshaya_options_service) {
    $this->languageManager = $language_manager;
    $this->cache = $cache;
    $this->requestStack = $request_stack;
    $this->alshayaOptionsService = $alshaya_options_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('cache.data'),
      $container->get('request_stack'),
      $container->get('alshaya_options_list.alshaya_options_service')
    );
  }

  /**
   * Returns the build for options page.
   *
   * @return array
   *   Build array.
   */
  public function optionsPage() {
    if (!$this->alshayaOptionsService->optionsPageEnabled()) {
      throw new NotFoundHttpException();
    }

    $config = $this->config('alshaya_options_list.settings');
    $cache_tags = Cache::mergeTags(
      [AlshayaOptionsListHelper::OPTIONS_PAGE_CACHETAG],
      $config->getCacheTags()
    );

    $options_list = [];
    $libraries = ['alshaya_white_label/optionlist_filter', 'alshaya_options_list/alshaya_options_list_search'];
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // Get current request uri.
    $request = $this->requestStack->getCurrentRequest()->getRequestUri();
    // Remove query parameters.
    $request = explode('?', $request);
    // Remove langcode.
    $request = str_replace('/' . $langcode . '/', '', $request[0]);
    $attribute_options = $config->get('alshaya_options_pages');
    $attributeCodes = array_filter($attribute_options[$request]['attributes']);
    // Check for cache first.
    $cid = 'alshaya_options_page:' . $request . ':' . $langcode;
    if ($cache = $this->cache->get($cid)) {
      $data = $cache->data;
      // If cache hit.
      if (!empty($data)) {
        $options_list = $data;
      }
    }
    else {
      foreach ($attributeCodes as $attributeCode) {
        foreach ($attribute_options[$request]['attribute_details'][$attributeCode] as $key => $attributeOptions) {
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

          $options_list[$attributeCode][$key]['options_markup'] = [
            '#theme' => 'alshaya_options_attribute',
            '#option' => $option,
            '#attribute_code' => $attributeCode,
          ];

          $options_list[$attributeCode][$key]['title'] = $attributeOptions['title'];
          $options_list[$attributeCode][$key]['description'] = $attributeOptions['description'];

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
          foreach ($attribute_detail['options_markup']['#option']['terms'] as $group_key => $grouped_term) {
            foreach ($grouped_term as $group_term_key => $grouped_term_value) {
              if (!in_array($grouped_term_value['title'], $facet_results[$attribute_key])) {
                unset($options_list[$attribute_key][$no]['options_markup']['#option']['terms'][$group_key][$group_term_key]);
              }
            }
          }
          $options_list[$attribute_key][$no]['options_markup']['#option']['terms'] = array_filter($options_list[$attribute_key][$no]['options_markup']['#option']['terms']);
        }
        else {
          foreach ($attribute_detail['options_markup']['#option']['terms'] as $term_key => $term) {
            if (!in_array($term['title'], $facet_results[$attribute_key])) {
              unset($options_list[$attribute_key][$no]['options_markup']['#option']['terms'][$term_key]);
            }
          }
        }
      }
    }

    $options_list = [
      '#theme' => 'alshaya_options_page',
      '#options_list' => $options_list,
      '#page_title' => $attribute_options[$request]['title'],
      '#description' => $attribute_options[$request]['description'],
      '#attached' => [
        'library' => $libraries,
      ],
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];

    return $options_list;
  }

}
