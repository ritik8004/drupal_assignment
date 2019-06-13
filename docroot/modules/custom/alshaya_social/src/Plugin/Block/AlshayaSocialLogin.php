<?php

namespace Drupal\alshaya_social\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
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
      $output['#section_title'] = $this->t('sign in with social media');
    }

    return $output;
  }

}
