<?php

namespace Drupal\alshaya_product_options\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_product_options\Brand\AlshayaBrandListHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_options_list\AlshayaOptionsListHelper;

/**
 * Provides alshaya brand carousel block.
 *
 * @Block(
 *   id = "alshaya_brand_carousel",
 *   admin_label = @Translation("Alshaya Brand Carousel")
 * )
 */
class AlshayaBrandCarouselBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Brand list helper.
   *
   * @var \Drupal\alshaya_product_options\Brand\AlshayaBrandListHelper
   */
  protected $brandList;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * THe language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Alshaya Options List Service.
   *
   * @var Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AlshayaBrandListHelper $brand_list, ModuleHandlerInterface $module_handler, LanguageManagerInterface $languageManager, AlshayaOptionsListHelper $alshaya_options_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->brandList = $brand_list;
    $this->moduleHandler = $module_handler;
    $this->languageManager = $languageManager;
    $this->alshayaOptionsService = $alshaya_options_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya.brand_list_helper'),
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('alshaya_options_list.alshaya_options_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get product brand details.
    $terms = $this->brandList->getBrandTerms();
    $brand_images = [];
    $brand_carousel_settings = \Drupal::config('alshaya_brand_carousel.settings')->get('brand_carousel_items_settings');

    if (!empty($terms)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      $attributeName = 'product_brand';
      $link = '/' . $langcode . '/search?f[0]=' . $attributeName . ':';
      // Incase of algolia we don't have search page.
      // So adding a algolia suitable link.
      if ($this->moduleHandler->moduleExists('alshaya_search_algolia')) {
        $attributeName = 'attr_product_brand';
        $link = '/' . $langcode . '/#query= &refinementList[' . $attributeName . '][0]=';
      }
      $facet_results = $this->alshayaOptionsService->loadFacetsData([$attributeName]);
      if (!empty($facet_results)) {
        foreach ($terms as $term) {
          if (in_array($term->name, $facet_results[$attributeName])) {
            $brand_images[$term->tid] = [
              'image' => $term->uri,
              'title' => $term->name,
              'link' => $link . $term->name,
            ];
          }
        }
      }
    }

    return [
      '#theme' => 'alshaya_brand_carousel',
      '#brand_details' => $brand_images,
      '#attached' => [
        'drupalSettings' => [
          'brand_carousel_items_settings' => [
            'brand_carousel_slidesToShow' => $brand_carousel_settings['brand_carousel_slidesToShow'],
            'brand_carousel_slidesToScroll' => $brand_carousel_settings['brand_carousel_slidesToScroll'],
          ]
        ]
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Discard cache for the block once a brand term gets updated/deleted.
    return Cache::mergeTags(
      parent::getCacheTags(),
      [AlshayaBrandListHelper::BRAND_CACHETAG]);
  }

}
