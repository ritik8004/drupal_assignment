<?php

namespace Drupal\alshaya_search_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\block\BlockInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\alshaya_search_api\AlshayaSearchApiFacetsManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom block which contains/renders all facet blocks for PLP.
 *
 * @Block(
 *  id = "alshaya_plp_facets_block_all",
 *  admin_label = @Translation("Alshaya all facet block - PLP"),
 * )
 */
class AlshayaPlpFacetsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Facet source.
   */
  const FACET_SOURCE = 'search_api:views_block__alshaya_product_list__block_1';

  /**
   * Exposed sort block.
   */
  const PLP_EXPOSED_SORT_BLOCK = 'exposedformalshaya_product_listblock_1';

  /**
   * Alshaya facet manager.
   *
   * @var \Drupal\alshaya_search_api\AlshayaSearchApiFacetsManager
   */
  protected $alshayaFacetManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaPlpFacetsBlock constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\alshaya_search_api\AlshayaSearchApiFacetsManager $alshaya_facet_manager
   *   Facet manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaSearchApiFacetsManager $alshaya_facet_manager,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->alshayaFacetManager = $alshaya_facet_manager;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_search_api.facets_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get all sorted facet blocks.
    $facet_blocks = $this->alshayaFacetManager->getBlocksForFacets(self::FACET_SOURCE);
    $block = $this->entityTypeManager->getStorage('block')->load(self::PLP_EXPOSED_SORT_BLOCK);
    if ($block instanceof BlockInterface) {
      $block_view = $this->entityTypeManager->getViewBuilder('block')->view($block);
      array_unshift($facet_blocks, $block_view);
    }

    $show_all = TRUE;
    if (count($facet_blocks) <= 4) {
      $show_all = FALSE;
    }

    // This is to determine whether category facet will render on the page.
    $category_facet_block = $this->entityTypeManager->getStorage('block')->load('categoryfacetplp');

    // We have cases where we disable access to category facet.
    // For instance panty-guide page.
    // @see alshaya_acm_product_category_block_access().
    $category_facet_block_content = $category_facet_block->access('view') ? $category_facet_block->getPlugin()->build() : NULL;
    $class = '';
    if (empty($category_facet_block_content)) {
      $class = 'empty-category';
    }

    return [
      '#theme' => 'all_facets_block',
      '#facet_blocks' => $facet_blocks,
      '#show_all' => $show_all,
      '#class' => $class,
    ];
  }

}
