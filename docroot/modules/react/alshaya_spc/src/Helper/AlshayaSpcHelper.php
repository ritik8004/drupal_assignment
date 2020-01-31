<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AlshayaSpcHelper.
 */
class AlshayaSpcHelper {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaSpcHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

}
