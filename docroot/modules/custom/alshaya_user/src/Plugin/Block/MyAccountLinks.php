<?php

namespace Drupal\alshaya_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_account, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_account;
    $this->entityRepository = $entity_repository;
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
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Current user id.
    $uid = $this->currentUser->id();

    // Get user id of user who's profile is currently visit.
    $account = \Drupal::request()->attributes->get('user');
    if (empty($account)) {
      $account = $this->currentUser;
    }

    $is_customer = alshaya_acm_customer_is_customer($account);

    // Get the current route to set active class.
    $currentRoute = \Drupal::routeMatch()->getRouteName();

    // Prepare active class options.
    $activeLinkOptions = ['attributes' => ['class' => 'active']];

    // My account link.
    $links['my_account'] = [
      'text' => $this->t('my account'),
      'route' => 'entity.user.canonical',
      'options' => ['user' => $account->id()],
    ];

    if ($uid != $account->id()) {
      $links['my_account']['text'] = $this->t('Account');
    }

    if ($is_customer) {
      // Orders link.
      $links['orders'] = [
        'text' => $this->t('orders'),
        'route' => 'acq_customer.orders',
        'options' => ['user' => $account->id()],
      ];
    }

    // Contact details link.
    $links['contact_details'] = [
      'text' => $this->t('contact details'),
      'route' => 'entity.user.edit_form',
      'options' => ['user' => $account->id()],
    ];

    if ($is_customer) {
      // Address book link.
      $links['address_book'] = [
        'text' => $this->t('address book'),
        'route' => 'entity.profile.type.address_book.user_profile_form',
        'options' => [
          'user' => $account->id(),
          'profile_type' => 'address_book',
        ],
      ];
    }

    if ($is_customer) {
      // Communication preferences link.
      $links['communication_preference'] = [
        'text' => $this->t('communication preferences'),
        'route' => 'alshaya_user.user_communication_preference',
        'options' => ['user' => $account->id()],
      ];
    }

    // Change password link.
    $links['change_password'] = [
      'text' => $this->t('change password'),
      'route' => 'change_pwd_page.change_password_form',
      'options' => ['user' => $account->id()],
    ];

    // Sign out link.
    $links['sign_out'] = [
      'text' => $this->t('Sign out'),
      'route' => 'user.logout',
      'options' => [],
    ];

    $items = [];

    foreach ($links as $key => $link) {
      $options = [];

      if ($link['route'] == $currentRoute) {
        $options = $activeLinkOptions;
      }
      elseif (($currentRoute == 'entity.profile.edit_form' || $currentRoute == 'entity.profile.type.address_book.user_profile_form.add') && $link['route'] == 'entity.profile.type.address_book.user_profile_form') {
        $options = $activeLinkOptions;
      }

      $items[$key] = [
        '#markup' => Link::createFromRoute($link['text'], $link['route'], $link['options'], $options)
          ->toString(),
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
      if ($key == 'my_account') {
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
        '@name' => \Drupal::service('alshaya_user.info')
          ->getName(),
      ]) . '</h3>',
    ];

    $build['my_account_mobile_title'] = [
      '#markup' => '<h3 class="my-account-mobile-title1">' . $this->t('my account') . '</h3><h4 class="my-account-mobile-title2">' . $this->t('logged in as @name', [
        '@name' => \Drupal::service('alshaya_user.info')
          ->getName(),
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
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['user:' . $this->currentUser->id()]);
  }

}
