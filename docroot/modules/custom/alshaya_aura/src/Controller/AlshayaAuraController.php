<?php

namespace Drupal\alshaya_aura\Controller;

use Drupal\alshaya_aura\Helper\AuraStatus;
use Drupal\alshaya_aura\Helper\AuraTier;
use Drupal\alshaya_user\AlshayaUserInfo;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Alshaya Aura routes.
 */
class AlshayaAuraController extends ControllerBase {

  /**
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The entity type manager service.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('http_client'),
      $container->get('alshaya_user.info'),
      $container->get('logger.channel.alshaya_aura'),
    );
  }

  /**
   * UserController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client service.
   * @param \Drupal\alshaya_user\AlshayaUserInfo $user_info
   *   The user info service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   */
  public function __construct(
    Request $current_request,
    ClientInterface $http_client,
    AlshayaUserInfo $user_info,
    LoggerInterface $logger
  ) {
    $this->currentRequest = $current_request;
    $this->httpClient = $http_client;
    $this->userInfo = $user_info;
    $this->logger = $logger;
  }

  /**
   * Builds the response.
   */
  public function userPoints() {
    $is_authenticated = $this->userInfo->currentUser->isAuthenticated();

    if (!$is_authenticated) {
      return new CacheableJsonResponse([]);
    }

    $loyalty_status = (int) $this->userInfo->userObject->get('field_aura_loyalty_status')->getString();
    if (in_array($loyalty_status, AuraStatus::getLinkedLoyaltyStatuses())) {
      $is_loyalty_linked = TRUE;
    }

    $user['name'] = $this->userInfo->getName();
    $user['is_loyalty_linked'] = $is_loyalty_linked ?? FALSE;

    if ($is_loyalty_linked) {
      $user_tier = AuraTier::getAllAuraTiers()[$this->userInfo->userObject->get('field_aura_tier')->getString()]
      ?? AuraTier::getDefaultAuraTier();
      try {
        $endpoint = $this->currentRequest->getSchemeAndHttpHost()
        . _alshaya_spc_get_middleware_url()
        . '/get/loyalty-club/get-customer-points';
        $response = $this->httpClient->request('GET', $endpoint, ['verify' => FALSE]);
        $result = json_decode($response->getBody()->getContents(), TRUE);
        if (isset($result['error'])) {
          throw new \Exception($result['error_message'] ?? 'Unknown error');
        }

        $user_tier = AuraTier::getAllAuraTiers()[$this->userInfo->userObject->get('field_aura_tier')->getString()]
        ?? AuraTier::getDefaultAuraTier();

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

}
