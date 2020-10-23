<?php

namespace Drupal\alshaya_social\Plugin\Block;

use Drupal\alshaya_social\AlshayaSocialHelper;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides Social Login alternative for login/register on Alshaya sites.
 *
 * @Block(
 *   id = "alshaya_social_login",
 *   admin_label = @Translation("Alshaya Social Login Block")
 * )
 */
class AlshayaSocialLogin extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Social helper.
   *
   * @var \Drupal\alshaya_social\AlshayaSocialHelper
   */
  protected $socialHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, AlshayaSocialHelper $social_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->socialHelper = $social_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('alshaya_social.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf($this->socialHelper->getStatus());
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (!in_array($this->routeMatch->getRouteName(), [
      'user.login',
      'user.register',
    ])) {
      return [];
    }

    $titles = [
      'user.login' => $this->t('sign in with social media'),
      'user.register' => $this->t('sign up with social media'),
    ];

    return [
      '#theme' => 'alshaya_social',
      '#social_networks' => $this->socialHelper->getSocialNetworks(),
      '#section_title' => $titles[$this->routeMatch->getRouteName()] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'config:alshaya_social.settings',
      'config:social_auth.settings',
    ]);
  }

}
