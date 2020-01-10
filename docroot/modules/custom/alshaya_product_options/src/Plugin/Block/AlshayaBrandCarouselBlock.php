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
use Drupal\Core\Config\ConfigFactoryInterface;

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
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Brand Carousel config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Alshaya Options List Service.
   *
   * @var Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AlshayaBrandListHelper $brand_list, ModuleHandlerInterface $module_handler, LanguageManagerInterface $languageManager, ConfigFactoryInterface $config_factory, AlshayaOptionsListHelper $alshaya_options_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->brandList = $brand_list;
    $this->moduleHandler = $module_handler;
    $this->languageManager = $languageManager;
    $this->config = $config_factory->get('alshaya_brand_carousel.settings');
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
      $container->get('config.factory'),
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
    $brand_carousel_settings = $this->config->get('brand_carousel_items_settings');

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
      // Allow other modules to alter link.
      $this->moduleHandler->invokeAll('brand_carousel_link_alter', [&$link]);
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
          ],
        ],
      ],
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
