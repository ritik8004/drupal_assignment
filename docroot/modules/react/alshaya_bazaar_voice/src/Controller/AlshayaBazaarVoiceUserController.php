<?php

namespace Drupal\alshaya_bazaar_voice\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice;

/**
 * This controller contains methods for user account reviews.
 */
class AlshayaBazaarVoiceUserController extends ControllerBase {

  /**
   * The current account object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Alshaya bazaar voice service object.
   *
   * @var \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice
   */
  protected $alshayaBazaarVoice;

  /**
   * AlshayaBazaarVoiceUserController constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_account
   *   The current account object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\alshaya_bazaar_voice\Service\AlshayaBazaarVoice $alshayaBazaarVoice
   *   Alshaya bazaar voice service.
   */
  public function __construct(AccountProxyInterface $current_account,
                              ConfigFactoryInterface $config_factory,
                              ModuleHandlerInterface $module_handler,
                              AlshayaBazaarVoice $alshayaBazaarVoice) {
    $this->currentUser = $current_account;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->alshayaBazaarVoice = $alshayaBazaarVoice;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('alshaya_bazaar_voice.service')
    );
  }

  /**
   * Returns the build to the current user reviews page.
   *
   * @return array
   *   Build array.
   */
  public function getUserReviews() {
    $build = [];
    $config = $this->configFactory->get('bazaar_voice.settings');
    // Build bazaarvoice settings required for user reviews.
    $settings = [
      'bazaar_voice' => [
        'endpoint' => $config->get('api_base_url'),
        'api_version' => $config->get('api_version'),
        'passkey' => $config->get('conversations_apikey'),
        'locale' => $config->get('locale'),
        'max_age' => $config->get('max_age'),
        'reviews_initial_load' => $config->get('reviews_initial_load'),
        'reviews_on_loadmore' => $config->get('reviews_on_loadmore'),
        'user_id' => $this->currentUser->id(),
        'stats' => 'Reviews',
      ],
    ];

    $build['#attached']['drupalSettings']['userInfo'] = $settings;
    $build['myaccount']['#markup'] = '<div id="myaccount-reviews"></div>';
    $build['#attached']['library'][] = 'alshaya_bazaar_voice/myaccount';
    $build['#attached']['library'][] = 'alshaya_white_label/myaccount-reviews';
    $build['bazaar_voice_strings'] = $this->alshayaBazaarVoice->getBazaarvoiceStrings();

    return $build;
  }

  /**
   * Helper method to check access.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Return access result object.
   */
  public function checkAccess(UserInterface $user) {
    if (empty($user)) {
      return AccessResult::forbidden();
    }

    if ($user->id() === 0 || $user->id() !== $this->currentUser->id()) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
