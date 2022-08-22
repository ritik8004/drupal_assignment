<?php

namespace Drupal\alshaya_hello_member\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_hello_member\Helper\HelloMemberHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides Hello member header block.
 *
 * @Block(
 *   id = "hello_member_header_block",
 *   admin_label = @Translation("Hello member header block")
 * )
 */
class HelloMemberHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Hello Member Helper service object.
   *
   * @var Drupal\alshaya_hello_member\Helper\HelloMemberHelper
   */
  protected $helloMemberHelper;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * MyMembershipInfoBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param Drupal\alshaya_hello_member\Helper\HelloMemberHelper $hello_member_helper
   *   The Hello Member service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              RouteMatchInterface $route_match,
                              ModuleHandlerInterface $module_handler,
                              HelloMemberHelper $hello_member_helper,
                              EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
    $this->helloMemberHelper = $hello_member_helper;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
                                array $configuration,
                                $plugin_id,
                                $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('module_handler'),
      $container->get('alshaya_hello_member.hello_member_helper'),
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $link = '';
    $config = $this->configFactory->get('alshaya_hello_member.settings');
    if ($config->get('membership_info_content_node')) {
      // Get the URL for hello member info page.
      $options = ['absolute' => TRUE];
      $url = Url::fromRoute('entity.node.canonical', ['node' => $config->get('membership_info_content_node')], $options);
      $link = Link::fromTextAndUrl($this->t('Membership Info', [], ['context' => 'hello_member']), $url)->toString();
    }

    return [
      '#markup' => '<div class="membership-info-link">' . $link . '</div>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Show block only on my accounts page if hello member is enabled.
    return AccessResult::allowedIf(
      $this->helloMemberHelper->isHelloMemberEnabled()
    );
  }

}
