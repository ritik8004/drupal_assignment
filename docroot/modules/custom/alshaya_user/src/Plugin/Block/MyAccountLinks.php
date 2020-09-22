<?php

namespace Drupal\alshaya_user\Plugin\Block;

use Drupal\alshaya_user\AlshayaUserInfo;
use Drupal\user\Entity\User;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides 'my account links' block.
 *
 * @Block(
 *   id = "alshaya_my_account_links",
 *   admin_label = @Translation("Alshaya my account links")
 * )
 */
class MyAccountLinks extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current account object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * ImmutableConfig object containing custom user config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Alshaya User Info service object.
   *
   * @var \Drupal\alshaya_user\AlshayaUserInfo
   */
  protected $userInfo;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Current Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * MyAccountLinks constructor.
   *
   * @param array $configuration
   *   Configuration data.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_account
   *   The current account object.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\alshaya_user\AlshayaUserInfo $user_info
   *   Alshaya User Info service object.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current Request object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AccountProxyInterface $current_account,
                              EntityRepositoryInterface $entity_repository,
                              ConfigFactoryInterface $config_factory,
                              ModuleHandlerInterface $module_handler,
                              AlshayaUserInfo $user_info,
                              RouteMatchInterface $route_match,
                              Request $current_request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_account;
    $this->entityRepository = $entity_repository;
    $this->config = $config_factory->get('alshaya_user.settings');
    $this->moduleHandler = $module_handler;
    $this->userInfo = $user_info;
    $this->routeMatch = $route_match;
    $this->currentRequest = $current_request;
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
      $container->get('entity.repository'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('alshaya_user.info'),
      $container->get('current_route_match'),
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * Function to return my account links.
   *
   * @return array
   *   My Account links.
   */
  public static function getMyAccountLinks() {
    $links = [];

    // My account link.
    $links['my_account'] = [
      'text' => t('my account'),
      'route' => 'entity.user.canonical',
      'weight' => 10,
    ];

    // Orders link.
    $links['orders'] = [
      'text' => t('orders'),
      'route' => 'acq_customer.orders',
      'weight' => 20,
    ];

    // Contact details link.
    $links['contact_details'] = [
      'text' => t('contact details'),
      'route' => 'entity.user.edit_form',
      'weight' => 30,
    ];

    // Address book link.
    $links['address_book'] = [
      'text' => t('address book'),
      'route' => 'entity.profile.type.user_profile_form',
      'options' => [
        'profile_type' => 'address_book',
      ],
      'weight' => 40,
    ];

    // Communication preferences link.
    $links['communication_preference'] = [
      'text' => t('communication preferences'),
      'route' => 'alshaya_user.user_communication_preference',
      'weight' => 50,
    ];

    // Change password link.
    $links['change_password'] = [
      'text' => t('change password'),
      'route' => 'change_pwd_page.change_password_form',
      'weight' => 60,
    ];

    // Sign out link.
    $links['sign_out'] = [
      'text' => t('Sign out'),
      'route' => 'user.logout',
      'options' => [],
      'weight' => 100,
    ];

    \Drupal::moduleHandler()->alter('alshaya_my_account_links', $links);

    array_multisort(array_column($links, 'weight'), SORT_ASC, $links);

    return $links;
  }

  /**
   * Function to return only those my account links which are enabled.
   *
   * @return array
   *   My Account links.
   */
  public function getMyAccountEnabledLinks() {
    $links = self::getMyAccountLinks();

    if ($config = $this->config->get('my_account_enabled_links')) {
      $config = unserialize($config);
      foreach ($links as $key => $link) {
        if (empty($config[$key])) {
          unset($links[$key]);
        }
      }
    }

    return $links;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Current user id.
    $uid = $this->currentUser->id();

    // Get user id of user who's profile is currently visit.
    $account = $this->currentRequest->attributes->get('user');
    if (!$account instanceof User) {
      $account = $this->currentUser;
    }

    // Check alshaya_acm_customer module status (enabled/disabled).
    $alshaya_acm_customer_status = $this->moduleHandler->moduleExists('alshaya_acm_customer');
    // Set the variable NULL.
    $is_customer = NULL;
    if ($alshaya_acm_customer_status) {
      $is_customer = alshaya_acm_customer_is_customer($account);
    }

    // Get the current route to set active class.
    $currentRoute = $this->routeMatch->getRouteName();

    // Prepare active class options.
    $activeLinkOptions = [
      'attributes' => [
        'class' => ['active'],
      ],
    ];

    $links = $this->getMyAccountEnabledLinks();

    if (!$is_customer) {
      unset($links['orders']);
      unset($links['address_book']);
      unset($links['communication_preference']);
      unset($links['payment_cards']);
    }

    if ($uid != $account->id() && isset($links['my_account'])) {
      $links['my_account']['text'] = $this->t('Account');
    }

    $items = [];

    foreach ($links as $key => $link) {
      $link['options']['user'] = $account->id();

      $options = [];

      if ($link['route'] == $currentRoute) {
        $options = $activeLinkOptions;
      }
      elseif (($currentRoute == 'entity.profile.edit_form' || $currentRoute == 'entity.profile.type.user_profile_form.add') && $link['route'] == 'entity.profile.type.user_profile_form') {
        $options = $activeLinkOptions;
      }
      elseif ($alshaya_acm_customer_status && ($link['route'] == 'acq_customer.orders' && $currentRoute == 'alshaya_acm_customer.orders_detail')) {
        $options = $activeLinkOptions;
      }

      if (!isset($options['attributes']) && (!isset($options['attributes']['class']))) {
        $options['attributes'] = [];
        $options['attributes']['class'] = [];
      }

      if ($key !== 'my_account') {
        $link_item_class_name = 'my-account-' . strtolower(str_replace(' ', '-', $link['text']));
        $options['attributes']['class'][] = ' ' . $link_item_class_name;
      }
      else {
        $link_item_class_name = strtolower(str_replace(' ', '-', $link['text']));
        $options['attributes']['class'][] = ' ' . $link_item_class_name;
      }

      $items[$key] = [
        '#markup' => Link::createFromRoute($link['text'], $link['route'], $link['options'], $options)->toString(),
      ];

      // Add class for sign-out.
      if ($key == 'sign_out') {
        $items[$key]['#wrapper_attributes'] = [
          'class' => [
            'sign-out',
          ],
        ];
      }
      // Add class for my account.
      elseif ($key == 'my_account') {
        $items[$key]['#wrapper_attributes'] = [
          'class' => [
            'my-account',
          ],
        ];
      }
    }

    $build = [];
    $build['my_account_title'] = [
      '#markup' => '<h3 class="my-account-title">' . $this->t('Welcome, @name', [
        '@name' => $this->userInfo->getName(),
      ]) . '</h3>',
    ];

    $build['my_account_mobile_title'] = [
      '#markup' => '<h3 class="my-account-mobile-title1">' . $this->t('my account') . '</h3><h4 class="my-account-mobile-title2">' . $this->t('logged in as @name', [
        '@name' => $this->userInfo->getName(),
      ]) . '</h4>',
    ];

    $build['my_account_links'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
      '#attributes' => [
        'class' => [
          'my-account-nav',
        ],
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route', 'user']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['user:' . $this->currentUser->id(), 'config:alshaya_user.settings']);
  }

}
