<?php

namespace Drupal\alshaya_advanced_page\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_advanced_page\Brand\AlshayaBrandListHelper;

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
   * Brand List.
   *
   * @var \Drupal\alshaya_advanced_page\Brand\AlshayaBrandListHelper
   */
  protected $brandList;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AlshayaBrandListHelper $brand_list) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->brandList = $brand_list;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya.brand_list_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get product brand details.
    $brand_img = $this->brandList->getBrandImages();

    return [
      '#theme' => 'alshaya_brand_carousel',
      '#brand_details' => $brand_img,
    ];
  }

}
