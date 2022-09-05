<?php

namespace Drupal\alshaya_rcs_listing\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a PLP mobile menu block.
 *
 * @Block(
 *   id = "alshaya_rcs_plp_mobile_menu",
 *   admin_label = @Translation("Alshaya RCS PLP Mobile Menu"),
 *   category = @Translation("RCS Placeholders"),
 * )
 */
class AlshayaRcsPlpMobileMenu extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs an AlshayaRcsPlpMobileMenu object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('language_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    return [
      '#attributes' => [
        'class' => [
          'block-views-blockproduct-category-level-3-block-2',
        ],
      ],
      '#theme_wrappers' => [
        'container' => [
          '#attributes' => [
            'id' => 'rcs-ph-plp_mobile_menu',
            'data-param-entity-to-get' => 'category',
          ],
        ],
      ],
      '#attached' => [
        'library' => ['alshaya_white_label/rcs-ph-plp-mobile-menu'],
      ],
    ];
  }

}
