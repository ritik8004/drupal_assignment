<?php

namespace Drupal\alshaya_social\Plugin\Block;

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
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf(alshaya_social_display_social_authentication_links() !== NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Render object.
    $output = [
      '#theme' => 'alshaya_social',
    ];

    $networks = alshaya_social_display_social_authentication_links();
    // Check for Social Login Enable status.
    if ($networks !== NULL) {
      $output['#social_networks'] = $networks;
      // Check Route.
      $route_name = $this->routeMatch->getRouteName();
      if ($route_name === 'user.login') {
        $output['#section_title'] = $this->t('sign in with social media');
      }
      elseif ($route_name === 'user.register') {
        $output['#section_title'] = $this->t('sign up with social media');
      }
    }

    return $output;
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
