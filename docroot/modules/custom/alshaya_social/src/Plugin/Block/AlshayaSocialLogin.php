<?php

namespace Drupal\alshaya_social\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\alshaya_social\AlshayaSocialHelper;
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
   * Alshaya Social Helper Service.
   *
   * @var \Drupal\alshaya_social\AlshayaSocialHelper
   */
  protected $alshayaSocialHelper;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AlshayaSocialHelper $alshaya_social) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaSocialHelper = $alshaya_social;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_social.helper')
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

    // Check for Social Login Enable status.
    if ($this->alshayaSocialHelper->getStatus()) {
      $output['#social_networks'] = $this->alshayaSocialHelper->getSocialNetworks();
      $output['#section_title'] = $this->t('sign in with social media');
    }

    return $output;
  }

}
