<?php

namespace Drupal\alshaya_seo_transac\Plugin\Block;

use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_user\AlshayaUserInfo;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block for GTM data.
 *
 * @Block(
 *   id = "alshaya_gtm_user_data_block",
 *   admin_label = @Translation("GTM User data block"),
 * )
 */
class AlshayaGtmUserDataBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * Array of terms for cache bubbling up.
   *
   * @var array
   */
  protected $cacheTags = [];

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Orders Manager.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * AlshayaGtmUserDataBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManager object.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user, OrdersManager $orders_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->currentUser = $current_user;
    $this->ordersManager = $orders_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('alshaya_acm_customer.orders_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (_alshaya_seo_process_gtm()) {
      $current_user = NULL;
      $current_user_id = $this->currentUser->id();
      $privilege_customer = 'Regular Customer';
      $email = '';
      $customer_type = 'New Customer';
      // Initialize the custom_id value as 0.
      $customer_id = 0;
      if ($this->currentUser->isAuthenticated()) {
        $current_user = $this->userStorage->load($current_user_id);
        $email = $current_user->get('mail')->getString();

        $customer_id = (int) $current_user->get('acq_customer_id')->getString();
        $customer_type = $this->ordersManager->getOrdersCount($customer_id) > 1
          ? 'Repeat Customer'
          : $customer_type;

        $privilege_customer = !empty($current_user->get('field_privilege_card_number')->getString()) ? 'Privilege Customer' : $privilege_customer;
      }

      $user_details = [
        'userID' => $customer_id,
        'userEmailID' => $email,
        'userPhone' => $current_user_id ? ($current_user->get('field_mobile_number')->value ?? '') : '',
        'customerType' => $customer_type,
        'userName' => $current_user_id ? AlshayaUserInfo::getFullName($current_user, ' ') : '',
        'userType' => $current_user_id ? 'Logged in User' : 'Guest User',
        'privilegeCustomer' => $privilege_customer,
      ];
      $build['#attached']['drupalSettings']['userDetails'] = $user_details;

      return $build;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    if ($this->currentUser->isAuthenticated()) {
      $this->cacheTags[] = 'user:' . $this->currentUser->id();
    }
    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->cacheTags
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['user']);
  }

}
