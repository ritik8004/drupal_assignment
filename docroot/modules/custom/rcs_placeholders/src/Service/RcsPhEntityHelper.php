<?php

namespace Drupal\rcs_placeholders\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service provides helper functions for the rcs_placeholders.
 */
class RcsPhEntityHelper implements RcsPhEntityHelperInterface {

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new RcsPhEntityHelper instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->configFactory = $config_factory;
  }

  /**
   * Get the placeholder term data from rcs_category.
   *
   * @return array
   *   Placeholder term's data.
   */
  public function getRcsPhCategoryTermData() {
    // Get the placeholder term Id.
    $config = $this->configFactory->get('rcs_placeholders.settings');
    $ph_term_id = $config->get('category.placeholder_tid');

    $term_data = [];
    // Get placeholder term data from Id.
    if ($ph_term_id) {
      $term_data = $this->termStorage->load($ph_term_id);
    }

    return $term_data;
  }

}
