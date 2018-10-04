<?php

namespace Drupal\alshaya_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
   * Module Handler service object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Entity Type Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * WelcomeUserBlock constructor.
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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AccountProxyInterface $current_account,
                              CurrentRouteMatch $current_route,
                              ConfigFactoryInterface $config_factory,
                              ModuleHandlerInterface $module_handler,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_account;
    $this->currentRoute = $current_route;
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // If loyalty enabled on site.
    if ($this->moduleHandler->moduleExists('alshaya_loyalty')) {
      $loyalty_settings = alshaya_loyalty_get_validation_settings();

      if (!($loyalty_settings['enable_disable_loyalty'])) {
        return $build;
      }
    }

    // Default image.
    $image_path = drupal_get_path('module', 'alshaya_user') . '/images/alshaya-priv-card.png';

    // Brand specific default image.
    $brand_module = $this->configFactory->get('alshaya.installed_brand')->get('module');
    if (file_exists(drupal_get_path('module', $brand_module) . '/images/alshaya-priv-card.svg')) {
      $image_path = drupal_get_path('module', $brand_module) . '/images/alshaya-priv-card.svg';
    }

    $join_club_content = $this->configFactory->get('alshaya_user.join_club');
    if ($image_fid = $join_club_content->get('join_club_image.fid')) {
      if ($image_file = $this->entityTypeManager->getStorage('file')->load($image_fid)) {
        $image_path = $image_file->getFileUri();
      }
    }

    $build['image'] = [
      '#theme' => 'image',
      '#uri' => $image_path,
      '#title' => $this->label(),
      '#alt' => $this->label(),
      '#prefix' => '<div class="block-join-the-club__image-wrapper">',
      '#suffix' => '</div>',
    ];

    $build['description']['#markup'] = $join_club_content->get('join_club_description.value');

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
