<?php

namespace Drupal\alshaya_aura_react\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for Alshaya Aura routes.
 */
class AlshayaAuraController extends ControllerBase {

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * AlshayaAuraController constructor.
   *
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   Current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(
    AccountProxy $current_user,
    EntityTypeManagerInterface $entity_type_manager
    ) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Update user's aura info.
   */
  public function updateUserAuraInfo(Request $request) {
    $saved = FALSE;
    $request_uid = $request->request->get('uid');
    $aura_status = $request->request->get('apcLinkStatus');
    $aura_tier = $request->request->get('tier');
    $current_uid = $this->currentUser->id();

    // Update user's aura status only when uid in request
    // matches the current user's uid.
    if (($aura_status || $aura_tier) && $request_uid === $current_uid) {
      $user = $this->entityTypeManager->getStorage('user')->load($current_uid);

      if ($aura_status) {
        $user->set('field_aura_loyalty_status', $aura_status);
      }
      if ($aura_tier) {
        $user->set('field_aura_tier', $aura_tier);
      }

      $saved = $user->save() ? TRUE : $saved;
    }

    return new JsonResponse($saved);
  }

}
