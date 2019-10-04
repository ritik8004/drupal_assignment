<?php

namespace Drupal\alshaya_acm_product_category\Plugin\Block;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block for navigation links for department pages.
 *
 * @Block(
 *   id = "alshaya_dp_navigation_link",
 *   admin_label = @Translation("Alshaya Department Page Navigation Links"),
 * )
 */
class AlshayaDpNavigationLinks extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaDpNavigationLinks constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              LanguageManagerInterface $language_manager) {
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
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = [];

    // Get if advanced page node.
    $node = _alshaya_advanced_page_get_department_node();
    // If department page, only then process further.
    if ($node instanceof NodeInterface) {
      $tid = $node->get('field_product_category')->first()->getString();
      if ($tid) {
        $l2_terms = _alshaya_acm_product_category_get_navigation_link_terms([$tid], 2, $this->languageManager->getCurrentLanguage()->getId());
        // If L2 terms are available, we fetch L3 terms for these L2 terms.
        if (!empty($l2_terms)) {
          $data['l2'] = $l2_terms;
          $l3_terms = _alshaya_acm_product_category_get_navigation_link_terms(array_keys($l2_terms), 3, $this->languageManager->getCurrentLanguage()->getId());
          if (!empty($l3_terms)) {
            $data['l3'] = $l3_terms;
          }
        }
      }
    }

    return [
      '#theme' => 'alshaya_app_navigation_links',
      '#data' => $data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Discard cache for the block once a term gets updated.
    return Cache::mergeTags(parent::getCacheTags(), [ProductCategoryTree::CACHE_TAG]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // As each department page has different url.
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }

}
