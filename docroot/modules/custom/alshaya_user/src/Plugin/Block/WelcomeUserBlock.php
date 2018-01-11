<?php

namespace Drupal\alshaya_user\Plugin\Block;

use Drupal\alshaya_user\AlshayaUserInfo;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides 'Welcome user' block.
 *
 * @Block(
 *   id = "welcome user",
 *   admin_label = @Translation("Welcome User")
 * )
 */
class WelcomeUserBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current account object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Alshaya User Info service object.
   *
   * @var \Drupal\alshaya_user\AlshayaUserInfo
   */
  protected $userInfo;

  /**
   * WelcomeUserBlock constructor.
   *
   * @param array $configuration
   *   Configuration data.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_account
   *   The current account object.
   * @param \Drupal\alshaya_user\AlshayaUserInfo $user_info
   *   Alshaya User Info service object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AccountProxyInterface $current_account,
                              AlshayaUserInfo $user_info) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_account;
    $this->userInfo = $user_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('alshaya_user.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<h3 class="my-account-title">' . $this->t('Welcome, @name', [
        '@name' => $this->userInfo->getName(),
      ]) . '</h3>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['user:' . $this->currentUser->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user']);
  }

}
