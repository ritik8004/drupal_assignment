<?php

namespace Drupal\alshaya_product_list\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\block\BlockInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\alshaya_search_api\AlshayaSearchApiFacetsManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides custom block which renders all facet blocks for attributes List.
 *
 * @Block(
 *  id = "alshaya_productlist_facets_block_all",
 *  admin_label = @Translation("Alshaya all facet block - Product List"),
 * )
 */
class AlshayaProductListFacetsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Facet source.
   */
  public const FACET_SOURCE = 'search_api:views_block__alshaya_product_list__block_3';

  /**
   * Exposed sort block.
   */
  public const PLP_EXPOSED_SORT_BLOCK = 'exposedformalshaya_product_listblock_3';

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

    return [
      '#theme' => 'all_facets_block',
      '#facet_blocks' => $facet_blocks,
      '#show_all' => $show_all,
    ];
  }

}
