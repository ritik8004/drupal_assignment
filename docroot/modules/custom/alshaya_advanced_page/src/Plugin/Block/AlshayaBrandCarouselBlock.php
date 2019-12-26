<?php

namespace Drupal\alshaya_advanced_page\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_advanced_page\Brand\AlshayaBrandListHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Language\LanguageManagerInterface;

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
   * @var \Drupal\alshaya_advanced_page\Brand\AlshayaBrandListHelper
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
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AlshayaBrandListHelper $brand_list, ModuleHandlerInterface $module_handler, LanguageManagerInterface $languageManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->brandList = $brand_list;
    $this->moduleHandler = $module_handler;
    $this->languageManager = $languageManager;
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
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get product brand details.
    $terms = $this->brandList->getBrandTerms();
    $brand_images = [];

    if (!empty($terms)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      $link = '/' . $langcode . '/search?f[0]=product_brand:';
      // Incase of algolia search we have different links for brands.
      // Change links if algolia search is active.
      if ($this->moduleHandler->moduleExists('alshaya_search_algolia')) {
        $link = '/' . $langcode . '/#query= &refinementList[attr_product_brand][0]=';
      }
      foreach ($terms as $term) {
        $brand_images[$term->tid] = [
          'image' => $term->uri,
          'title' => $term->name,
          'link' => $link . $term->name,
        ];
      }
    }

    return [
      '#theme' => 'alshaya_brand_carousel',
      '#brand_details' => $brand_images,
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
