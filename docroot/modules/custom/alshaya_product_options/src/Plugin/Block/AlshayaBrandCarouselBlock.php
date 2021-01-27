<?php

namespace Drupal\alshaya_product_options\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_product_options\Brand\AlshayaBrandListHelper;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_options_list\AlshayaOptionsListHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
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
   * @var \Drupal\alshaya_product_options\Brand\AlshayaBrandListHelper
   */
  protected $brandList;

  /**
   * Brand Carousel config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Alshaya Options List Service.
   *
   * @var \Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * The Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaBrandListHelper $brand_list,
                              ConfigFactoryInterface $config_factory,
                              AlshayaOptionsListHelper $alshaya_options_service,
                              LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->brandList = $brand_list;
    $this->config = $config_factory->get('alshaya_brand_carousel.settings');
    $this->alshayaOptionsService = $alshaya_options_service;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_product_options.brand_list_helper'),
      $container->get('config.factory'),
      $container->get('alshaya_options_list.alshaya_options_service'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get current language code.
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    // Get product brand details.
    $terms = $this->brandList->getBrandTerms($langcode);
    $brand_images = [];
    $brand_carousel_settings = $this->config->get('brand_carousel_items_settings');
    $attributeName = AlshayaBrandListHelper::getBrandAttribute();
    if (!empty($terms) && $attributeName) {
      $facet_results = $this->alshayaOptionsService->loadFacetsData([$attributeName]);
      $attribute_key = key($facet_results);
      $link = $this->alshayaOptionsService->getAttributeUrl($attribute_key);
      // If there are facet results.
      if (!empty($facet_results) && isset($facet_results[$attribute_key])) {
        $facet_results_lowercase = array_map('strtolower', $facet_results[$attribute_key]);
        foreach ($terms as $term) {
          if (in_array(strtolower($term->name), $facet_results_lowercase)) {
            $brand_images[$term->tid] = [
              'image' => $term->uri,
              'title' => $term->name,
              'link' => $link . rawurlencode($term->name),
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
      [AlshayaBrandListHelper::BRAND_CACHETAG], $this->config->getCacheTags());
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf(!empty(AlshayaBrandListHelper::getLogoAttribute()));
  }

}
