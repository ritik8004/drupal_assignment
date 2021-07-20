<?php

namespace Drupal\alshaya_master\Event;

use Drupal\acsf\Event\AcsfEventHandler;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Handles Alshaya-specific scrubbing events performed after site duplication.
 */
class AlshayaAcsfDuplicationScrubUsersHandler extends AcsfEventHandler {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * AlshayaSuperCategoryCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   LoggerFactory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger->get('alshaya_master');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * Implements AcsfEventHandler::handle().
   */
  public function handle() {
    $this->logger->info(dt('Entered @class', ['@class' => get_class($this)]));

    $ids = \Drupal::entityQuery('user')
      ->execute();

    foreach ($ids as $id) {
      $user = User::load($id);
      $roles = $user->getRoles();

      $num_roles = count($roles);

      // Only if a user has just a single role of authenticated user,
      // we will delete them.
      if (($num_roles == 1) && ($roles[0] == 'authenticated')) {
        $this->logger->info(dt('Deleting non-administrative user from duplicated site: @id', ['@id' => $id]));
        $this->entityTypeManager->getStorage('user')->load($id)->delete();
      }
    }
  }

}
