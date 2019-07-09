<?php

namespace Drupal\alshaya_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;

/**
 * Provides Login button on Register Page and Signup Button on Login Page.
 *
 * @Block(
 *   id = "alshaya_signup_signin_buttons",
 *   admin_label = @Translation("Alshaya Sign Up Sign In Buttons Block")
 * )
 */
class AlshayaSignUpSignIn extends BlockBase implements ContainerFactoryPluginInterface {

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
  public function build() {
    $route_name = $this->routeMatch->getRouteName();
    if (!in_array($route_name, ['user.register', 'user.login'])) {
      return [];
    }

    $build = [
      'user.register' => [
        '#theme' => 'alshaya_user_signin_signup_button',
        '#sub_text' => $this->t('already have an account?'),
        '#link_text' => $this->t('sign in here'),
        '#link_url' => Url::fromRoute('user.login')->toString(),
      ],
      'user.login' => [
        '#theme' => 'alshaya_user_signin_signup_button',
        '#sub_text' => $this->t('dont have an account yet?'),
        '#link_text' => $this->t('sign up here'),
        '#link_url' => Url::fromRoute('user.register')->toString(),
      ],
    ];

    return $build[$route_name];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
