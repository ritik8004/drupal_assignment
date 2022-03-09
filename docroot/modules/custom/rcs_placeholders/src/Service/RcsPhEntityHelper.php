<?php

namespace Drupal\rcs_placeholders\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Service provides helper functions for the rcs_placeholders.
 */
class RcsPhEntityHelper {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new RcsPhEntityHelper instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Drupal Renderer.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
  }

  /**
   * Get the placeholder term data from rcs_category.
   *
   * @return array
   *   Placeholder term's data.
   */
  protected function getRcsPhEntity(string $type) {
    $entity_id_key = $type === 'node'
      ? 'product.placeholder_nid'
      : 'category.placeholder_tid';

    $config = $this->configFactory->get('rcs_placeholders.settings');
    $entity_id = $config->get($entity_id_key);

    // Get placeholder term data from Id.
    if ($entity_id) {
      return $this->entityTypeManager->getStorage($type)->load($entity_id);
    }

    return NULL;
  }

  /**
   * Get the placeholder term entity.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   Placeholder term if available.
   */
  public function getRcsPhCategory() {
    return $this->getRcsPhEntity('taxonomy_term');
  }

  /**
   * Get the placeholder term entity.
   *
   * @return \Drupal\node\NodeInterface|null
   *   Placeholder term if available.
   */
  public function getRcsPhProduct() {
    return $this->getRcsPhEntity('node');
  }

  /**
   * Wrapper function to get template for view mode of specific entity type.
   *
   * @param string $entity_type
   *   Entity Type.
   * @param string $view_mode
   *   View Mode.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   Rendered markup.
   */
  public function getRcsPhEntityView(string $entity_type, string $view_mode) {
    $entity_view = $this->entityTypeManager
      ->getViewBuilder($entity_type)
      ->view($this->getRcsPhEntity($entity_type), $view_mode);

    return $this->renderer->render($entity_view);
  }

}
