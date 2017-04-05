<?php

namespace Drupal\alshaya_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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

    // My account link.
    $links['my_account'] = [
      '#markup' => Link::createFromRoute($this->t('My account'), 'entity.user.canonical', ['user' => $uid])->toString(),
    ];

    // Orders link.
    $links['orders'] = [
      '#markup' => Link::createFromRoute($this->t('Orders'), 'acq_customer.orders', ['user' => $uid])->toString(),
    ];

    // Contact details link.
    $links['contact_details'] = [
      '#markup' => Link::createFromRoute($this->t('Contact details'), 'entity.user.edit_form', ['user' => $uid])->toString(),
    ];

    // Address book link.
    $links['address_book'] = [
      '#markup' => Link::createFromRoute($this->t('Address book'), 'entity.profile.type.address_book.user_profile_form',
        [
          'user' => $uid,
          'profile_type' => 'address_book',
        ])->toString(),
    ];

    // Communication preferences link.
    $links['communication_preference'] = [
      '#markup' => Link::createFromRoute($this->t('Communication preferences'), 'entity.user.canonical', ['user' => $uid])->toString(),
    ];

    // Change password link.
    $links['change_password'] = [
      '#markup' => Link::createFromRoute($this->t('Change password'), 'entity.user.canonical', ['user' => $uid])->toString(),
    ];

    $build = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $links,
      '#attributes' => [
        'class' => [
          'my-account-nav',
        ],
      ],
    ];

    return $build;
  }

}
