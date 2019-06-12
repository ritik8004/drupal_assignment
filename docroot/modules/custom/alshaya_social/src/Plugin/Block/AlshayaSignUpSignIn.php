<?php

namespace Drupal\alshaya_social\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

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
    $output = [];
    if ($route_name === 'user.register') {
      $sub_text = $this->t('already have an account?');
      $link_text = $this->t('sign in here');
    }
    elseif ($route_name === 'user.login') {
      $sub_text = $this->t('dont have an account yet?');
      $link_text = $this->t('sign up here');
    }
    else {
      $sub_text = $link_text = NULL;
    }

    if (isset($sub_text) && isset($link_text)) {
      $output['#markup'] = '<div class="sub-text">' . $sub_text . '</div><div class="link-button">' . $link_text . '</div>';
    }
    return $output;

  }

}
