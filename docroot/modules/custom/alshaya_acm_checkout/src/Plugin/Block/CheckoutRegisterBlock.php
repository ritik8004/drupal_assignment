<?php

namespace Drupal\alshaya_acm_checkout\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\user\UserInterface;

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
   * User Settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityFormBuilder;

  /**
   * CheckoutRegisterBlock constructor.
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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entityTypeManager,
                              EntityFormBuilderInterface $entityFormBuilder,
                              ModuleHandlerInterface $module_handler,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFormBuilder = $entityFormBuilder;
    $this->moduleHandler = $module_handler;
    $this->config = $config_factory->get('user.settings');
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
      $container->get('module_handler'),
      $container->get('config.factory')
    );
  }

  /**
   * Implements \Drupal\block\BlockBase::build().
   */
  public function build() {
    $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $order = _alshaya_acm_checkout_get_last_order_from_session();

    // By default we assume loyalty is disabled.
    $loyalty_enabled = FALSE;

    // Check first if loyalty module is enabled.
    if ($this->moduleHandler->moduleExists('alshaya_loyalty')) {
      // Check again if loyalty is enabled in settings.
      $loyalty_settings = alshaya_loyalty_get_validation_settings();
      $loyalty_enabled = (bool) $loyalty_settings['enable_disable_loyalty'];
    }

    $build = [];

    $account = $this->entityTypeManager->getStorage('user')->create([]);
    $account->get('acq_customer_id')->setValue($order['customer_id']);
    $account->get('field_first_name')->setValue($order['firstname']);
    $account->get('field_last_name')->setValue($order['lastname']);

    // Set the mobile number from last order details.
    if (isset($order['billing'], $order['billing']['telephone'])) {
      $number = [
        'value' => $order['billing']['telephone'],
      ];

      $account->get('field_mobile_number')->setValue($number);
    }

    $loyalty_card = '';
    if ($loyalty_enabled) {
      // Set value of privilege card number if user has entered loyalty card
      // number in basket.
      if (!empty($order['extension']['loyalty_card'])) {
        $loyalty_card = $order['extension']['loyalty_card'];
        $account->get('field_privilege_card_number')->setValue($loyalty_card);
      }
    }

    $form = $this->entityFormBuilder->getForm($account, 'register');

    $form['title'] = [
      '#markup' => '<div class="confirmation__signup--title">' . $this->t('Your account') . '</div>',
      '#weight' => -99,
    ];

    $form['description'] = [
      '#markup' => '<div class="confirmation__signup--description">' . $this->t('Save your details to make shopping easier next time') . '</div>',
      '#weight' => -98,
    ];

    $form['account']['mail']['#value'] = $order['email'];
    $form['account']['mail']['#attributes']['readonly'] = 'readonly';

    $form['field_first_name']['#access'] = FALSE;
    $form['field_last_name']['#access'] = FALSE;

    if (isset($form['privilege_card_wrapper'])) {
      $form['privilege_card_wrapper']['#prefix'] = '<div id="details-privilege-card-wrapper" class="hidden-important">';
      $form['privilege_card_wrapper']['privilege_card_number']['#value'] = $loyalty_card;
      $form['privilege_card_wrapper']['privilege_card_number2']['#value'] = $loyalty_card;
      $form['privilege_card_wrapper']['#suffix'] = '</div>';
    }

    $form['actions']['submit']['#value'] = $this->t('save', [], ['context' => 'button']);

    $build['form'] = $form;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf($account->isAnonymous() && ($this->config->get('register') != UserInterface::REGISTER_ADMINISTRATORS_ONLY));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // We will display register block based on value in session for last order.
    return Cache::mergeContexts(parent::getCacheContexts(), ['session']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = parent::getCacheTags();

    // Add cache tags related to config.
    $cache_tags = Cache::mergeTags($cache_tags, $this->config->getCacheTags());

    return $cache_tags;
  }

}
