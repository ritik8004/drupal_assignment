<?php

namespace Drupal\alshaya_paragraphs\Commands;

use Drupal\alshaya_paragraphs\Helper\MigrateSymmetricToAsymmetric;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * AlshayaParagraphsCommands class.
 */
class AlshayaParagraphsCommands extends DrushCommands {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Migration Utility.
   *
   * @var \Drupal\alshaya_paragraphs\Helper\MigrateSymmetricToAsymmetric
   */
  private $migrateUtility;

  /**
   * AlshayaParagraphsCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\alshaya_paragraphs\Helper\MigrateSymmetricToAsymmetric $migrate_utility
   *   Migration utility.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              MigrateSymmetricToAsymmetric $migrate_utility) {
    $this->entityTypeManager = $entity_type_manager;
    $this->migrateUtility = $migrate_utility;
  }

  /**
   * Code to be executed only once post install.
   *
   * @command alshaya_paragraphs:migrate-paragraph-translations
   *
   * @validate-module-enabled alshaya_paragraphs
   *
   * @aliases migrate-paragraphs
   */
  public function migrateParagraphs() {
    $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'advanced_page',
    ]);

    foreach ($nodes as $node) {
      $this->migrateUtility->migrateEntity($node);
    }
  }

}
