<?php

namespace Drupal\alshaya_acm_checkout\Plugin\Block;

use Drupal\block\Entity\Block;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;

/**
 * Provides a 'CheckoutRegisterBlock' block.
 *
 * @Block(
 *   id = "checkout_register_block",
 *   admin_label = @Translation("Checkout Registration Block"),
 * )
 */
class CheckoutRegisterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityFormBuilder;

  /**
   * Constructs a new UserRegisterBlock plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entityFormBuilder
   *   The entity form builder.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, EntityFormBuilderInterface $entityFormBuilder, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFormBuilder = $entityFormBuilder;
    $this->moduleHandler = $module_handler;
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
      $container->get('entity.form_builder'),
      $container->get('module_handler')
    );
  }

  /**
   * Implements \Drupal\block\BlockBase::build().
   */
  public function build() {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $temp_store = \Drupal::service('user.private_tempstore')->get('alshaya_acm_checkout');
    $order_data = $temp_store->get('order');
    $email = $temp_store->get('email');

    // Throw access denied if nothing in session.
    if (empty($order_data) || empty($order_data['id']) || empty($email)) {
      return [];
    }

    // @TODO: Remove the fix when we get the full order details.
    $order_id = str_replace('"', '', $order_data['id']);
    $orders = alshaya_acm_customer_get_user_orders($email);
    $order_index = array_search($order_id, array_column($orders, 'order_id'));

    if ($order_index === FALSE) {
      return [];
    }

    $order = $orders[$order_index];

    $build = [];

    $account = $this->entityTypeManager->getStorage('user')->create([]);
    $account->get('field_first_name')->setValue($order['firstname']);
    $account->get('field_last_name')->setValue($order['lastname']);

    // Set the mobile number from last order details.
    if (isset($order['shipping'], $order['shipping']['address'], $order['shipping']['address']['phone'])) {
      $number = [
        'value' => $order['billing']['phone'],
      ];

      $account->get('field_mobile_number')->setValue($number);
    }

    $form = $this->entityFormBuilder->getForm($account, 'register');

    $form['title'] = [
      '#markup' => '<div>' . $this->t('your account') . '</div>',
      '#weight' => -99,
    ];

    $form['description'] = [
      '#markup' => '<div>' . $this->t('Save your details to make shopping easier next time') . '</div>',
      '#weight' => -98,
    ];

    $form['account']['mail']['#value'] = $order['email'];
    $form['account']['mail']['#attributes']['readonly'] = 'readonly';

    $form['field_first_name']['#access'] = FALSE;
    $form['field_last_name']['#access'] = FALSE;
    $form['privilege_card_wrapper']['#access'] = FALSE;

    $form['actions']['submit']['#value'] = $this->t('save');

    $build['form'] = $form;

    // Add the following block only if alshaya_loyaty is enabled.
    if ($this->moduleHandler->moduleExists('alshaya_loyalty')) {
      // TODO: Add condition around this to check if promo card was entered by
      // user in Basket. Also save that in user if it was entered.
      $block = Block::load('jointheclub');
      $build['joinclub'] = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
      $build['joinclub']['#weight'] = 100;
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf($account->isAnonymous() && (\Drupal::config('user.settings')->get('register') != USER_REGISTER_ADMINISTRATORS_ONLY))
      ->addCacheContexts(['user.roles'])
      ->addCacheTags(\Drupal::config('user.settings')->getCacheTags());
  }

}
