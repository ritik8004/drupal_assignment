<?php

namespace Drupal\alshaya_loyalty\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides 'Join the club' block.
 *
 * @Block(
 *   id = "join_the_club",
 *   admin_label = @Translation("Join the club")
 * )
 */
class JoinClub extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current account object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRoute;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * MyAccountNavBlock constructor.
   *
   * @param array $configuration
   *   Configuration data.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_account
   *   The current account object.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route
   *   The current route match service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_account, CurrentRouteMatch $current_route, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_account;
    $this->currentRoute = $current_route;
    $this->configFactory = $config_factory;
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
      $container->get('current_route_match'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build = [];

    // If loyalty enabled on site.
    $loyality_settings = alshaya_loyalty_get_validation_settings();
    if ($loyality_settings['enable_disable_loyality']) {
      $join_club_content = $this->configFactory->get('alshaya_loyalty.join_club');
      if ($image_fid = $join_club_content->get('join_club_image.fid')) {
        $image_file = File::load($image_fid);
        $image_path = $image_file->getFileUri();
      }
      else {
        // Default image.
        $image_path = drupal_get_path('module', 'alshaya_loyalty') . '/images/alshaya-priv-card.jpg';
      }

      $build['image'] = [
        '#theme' => 'image',
        '#uri' => $image_path,
        '#title' => $this->label(),
        '#alt' => $this->label(),
      ];

      $build['description']['#markup'] = $join_club_content->get('join_club_description.value');
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user', 'route']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['loyality-on-off']);
  }

}
