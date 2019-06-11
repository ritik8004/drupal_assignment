<?php

namespace Drupal\alshaya_options_list\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\alshaya_options_list\AlshayaOptionsListService;

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
   * @var Drupal\alshaya_options_list\AlshayaOptionsListService
   */
  private $alshayaOptionsService;

  /**
   * AlshayaOptionsPageController constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param Drupal\alshaya_options_list\AlshayaOptionsListService $alshaya_options_service
   *   Alshaya options service.
   */
  public function __construct(LanguageManagerInterface $language_manager,
                              CacheBackendInterface $cache,
                              RequestStack $request_stack,
                              AlshayaOptionsListService $alshaya_options_service) {
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
      $container->get('cache.default'),
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
      $option = [];
      // Check for cache first.
      $cid = 'alshaya_options_page:' . $attributeCode . ':' . $langcode;
      if ($cache = $this->cache->get($cid)) {
        $data = $cache->data;
        // If cache hit.
        if (!empty($data)) {
          $option['terms'] = $data;
        }
      }
      else {
        $option['terms'] = $this->alshayaOptionsService->fetchAllTermsForAttribute($attributeCode, $attribute_options[$request]['attribute_details'][$attributeCode]['show-images'], $attribute_options[$request]['attribute_details'][$attributeCode]['group']);
        $this->cache->set($cid, $option['terms'], Cache::PERMANENT, ['alshaya-options-page']);
      }
      if ($attribute_options[$request]['attribute_details'][$attributeCode]['show-search']) {
        $search_form = $this->formBuilder()
          ->getForm('\Drupal\alshaya_options_list\Form\AlshayaOptionsListAutocompleteForm', [
            'page_code' => $request,
            'attribute_code' => $attributeCode,
          ]);
        $options_list[$attributeCode]['search_form'] = $search_form;
      }

      if ($attribute_options[$request]['attribute_details'][$attributeCode]['group']) {
        $option['group'] = TRUE;
        $option['terms'] = $this->alshayaOptionsService->groupAlphabetically($option['terms']);
      }

      $options_list[$attributeCode]['options_markup'] = [
        '#theme' => 'alshaya_options_attribute',
        '#option' => $option,
        '#attribute_code' => $attributeCode,
      ];

      $options_list[$attributeCode]['title'] = $attribute_options[$request]['attribute_details'][$attributeCode]['title'];
      $options_list[$attributeCode]['description'] = $attribute_options[$request]['attribute_details'][$attributeCode]['description'];

      if ($attribute_options[$request]['attribute_details'][$attributeCode]['mobile_title_toggle']) {
        $options_list[$attributeCode]['mobile_title'] = $attribute_options[$request]['attribute_details'][$attributeCode]['mobile_title'];
      }
    }

    $options_list = [
      '#theme' => 'alshaya_options_page',
      '#options_list' => $options_list,
      '#page_title' => $attribute_options[$request]['title'],
      '#description' => $attribute_options[$request]['description'],
      '#attached' => [
        'library' => [
          'alshaya_white_label/optionlist_filter',
        ],
      ],
    ];

    return $options_list;
  }

}
