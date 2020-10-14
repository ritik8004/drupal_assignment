<?php

namespace Drupal\alshaya_aura_react\Controller;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_aura_react\Helper\AuraStatus;
use Drupal\alshaya_aura_react\Helper\AuraTier;
use Drupal\alshaya_user\AlshayaUserInfo;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_aura_react\Helper\AuraHelper;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for Alshaya Aura routes.
 */
class AlshayaAuraController extends ControllerBase {

  /**
   * Alshaya User Info service object.
   *
   * @var \Drupal\alshaya_user\AlshayaUserInfo
   */
  protected $userInfo;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * API Wrapper service.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Aura Helper service object.
   *
   * @var Drupal\alshaya_aura_react\Helper\AuraHelper
   */
  protected $auraHelper;

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
      $container->get('alshaya_user.info'),
      $container->get('logger.channel.alshaya_aura_react'),
      $container->get('alshaya_api.api'),
      $container->get('alshaya_aura_react.aura_helper'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * UserController constructor.
   *
   * @param \Drupal\alshaya_user\AlshayaUserInfo $user_info
   *   The user info service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   API Wrapper service.
   * @param Drupal\alshaya_aura_react\Helper\AuraHelper $aura_helper
   *   The aura helper service.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   Current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(
    AlshayaUserInfo $user_info,
    LoggerInterface $logger,
    AlshayaApiWrapper $api_wrapper,
    AuraHelper $aura_helper,
    AccountProxy $current_user,
    EntityTypeManagerInterface $entity_type_manager
    ) {
    $this->userInfo = $user_info;
    $this->logger = $logger;
    $this->apiWrapper = $api_wrapper;
    $this->auraHelper = $aura_helper;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Builds the response.
   */
  public function userPoints() {
    $is_authenticated = $this->userInfo->currentUser->isAuthenticated();

    if (!$is_authenticated) {
      return new CacheableJsonResponse([]);
    }

    $user = [];
    // User name and loyalty card linked status always be sent to front end.
    $user['name'] = $this->userInfo->getName();
    $user['is_loyalty_linked'] = $is_loyalty_linked = FALSE;

    $is_customer = alshaya_acm_customer_is_customer($this->userInfo->currentUser);
    if (!$is_customer) {
      $response = new CacheableJsonResponse(['aura_user' => $user]);
      $response->addCacheableDependency($this->userInfo->userObject);
    }

    $loyalty_status = (int) $this->auraHelper->getUserAuraStatus();
    if (AuraStatus::LINKED_STATUSES[$loyalty_status] ?? []) {
      $is_loyalty_linked = TRUE;
    }
    $user['is_loyalty_linked'] = $is_loyalty_linked;

    if ($is_loyalty_linked) {
      $user_tier = AuraTier::ALL_AURA_TIERS[$this->auraHelper->getUserAuraTier()]
      ?? AuraTier::DEFAULT_TIER;
      // The number part of the tier constant will be used in naming the tier
      // class in the HTML.
      $user_tier = substr($user_tier, -1);
      try {
        // Following code will be uncommented once API is available.
        // @codingStandardsIgnoreStart
        // $endpoint = sprintf('/customers/apc-points-balance/%s', $this->userInfo->userObject->get('acq_customer_id')->getString());
        // $response = $this->apiWrapper->invokeApi($endpoint, [], 'GET', TRUE);
        // $result = json_decode($response->getBody()->getContents(), TRUE);
        // if (isset($result['error'])) {
        //   throw new \Exception($result['error_message'] ?? 'Unknown error');
        // }
        // @codingStandardsIgnoreEnd
        // The following line of code should be removed once API is available.
        $result['points'] = rand(-100, 9999);

        $user = array_merge($user, [
          'points' => $result['points'],
          'tier' => $user_tier,
        ]);
      }
      catch (\Exception $e) {
        $this->logger->notice('Could not fetch points for @user because of @message', [
          '@user' => $this->userInfo->currentUser->id(),
          '@message' => $result['error_message'],
        ]);
        $user = array_merge($user, [
          'points' => -1,
        ]);
      }
    }

    $response = new CacheableJsonResponse(['aura_user' => $user]);
    $response->addCacheableDependency($this->userInfo->userObject);

    return $response;
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
