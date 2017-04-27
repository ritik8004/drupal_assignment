<?php

namespace Drupal\alshaya_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\user\Entity\User;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
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

    // Get the current route to set active class.
    $currentRoute = \Drupal::routeMatch()->getRouteName();

    // Prepare active class options.
    $activeLinkOptions = ['attributes' => ['class' => 'active']];

    // My account link.
    $links['my_account'] = [
      'text' => $this->t('My account'),
      'route' => 'entity.user.canonical',
      'options' => ['user' => $account->id()],
    ];

    if ($uid != $account->id()) {
      $links['my_account']['text'] = $this->t('Account');
    }

    // Orders link.
    $links['orders'] = [
      'text' => $this->t('Orders'),
      'route' => 'acq_customer.orders',
      'options' => ['user' => $account->id()],
    ];

    // Contact details link.
    $links['contact_details'] = [
      'text' => $this->t('Contact details'),
      'route' => 'entity.user.edit_form',
      'options' => ['user' => $account->id()],
    ];

    // Address book link.
    $links['address_book'] = [
      'text' => $this->t('Address book'),
      'route' => 'entity.profile.type.address_book.user_profile_form',
      'options' => [
        'user' => $account->id(),
        'profile_type' => 'address_book',
      ],
    ];

    // Communication preferences link.
    // TODO: Update the route name once link is available.
    $links['communication_preference'] = [
      'text' => $this->t('Communication preferences'),
      'route' => 'alshaya_user.user_communication_preference',
      'options' => ['user' => $account->id()],
    ];

    // Change password link.
    $links['change_password'] = [
      'text' => $this->t('Change password'),
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
    }

    $build = [];

    $build['my_account_title'] = [
      '#markup' => '<h2>' . $this->getTitle() . '</h2>',
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

  /**
   * Get the dynamic value as title for the block.
   *
   * @return string
   *   Title for the block.
   */
  protected function getTitle() {
    $user = User::load($this->currentUser->id());
    $title = '';
    if ($user) {
      $fname = $user->get('field_first_name')->getString();
      $lname = $user->get('field_last_name')->getString();
      if (!empty($fname)) {
        $title = $this->t('Welcome, @fname @lname', ['@fname' => $fname, '@lname' => $lname]);
      }
    }

    return $title;
  }

}
