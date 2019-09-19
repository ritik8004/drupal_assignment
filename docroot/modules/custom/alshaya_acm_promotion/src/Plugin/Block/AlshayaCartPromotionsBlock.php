<?php

namespace Drupal\alshaya_acm_promotion\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\acq_promotion\AcqPromotionPluginManager;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Driver\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;

/**
 * Provides a 'AlshayaCartPromotionsBlock' block.
 *
 * @Block(
 *  id = "alshaya_cart_promotions_block",
 *  admin_label = @Translation("Alshaya cart promotions block"),
 * )
 */
class AlshayaCartPromotionsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\alshaya_acm_promotion\AlshayaPromotionsManager definition.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager
   */
  protected $alshayaAcmPromotionManager;

  /**
   * The cart storage service.
   *
   * @var \Drupal\acq_cart\CartStorageInterface
   */
  protected $cartStorage;

  /**
   * ACQ Promotion Plugin Manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $acqPromotionPluginManager;

  /**
   * Constructs a new AlshayaCartPromotionsBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $alshaya_acm_promotion_manager
   *   The alshaya promotion manager service.
   * @param \Drupal\acq_cart\CartStorageInterface $cartSessionStorage
   *   The cart storage service.
   * @param \Drupal\acq_promotion\AcqPromotionPluginManager $pluginManager
   *   ACQ Promotion Plugin Manager.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        AlshayaPromotionsManager $alshaya_acm_promotion_manager,
        CartStorageInterface $cartSessionStorage,
        AcqPromotionPluginManager $pluginManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaAcmPromotionManager = $alshaya_acm_promotion_manager;
    $this->cartStorage = $cartSessionStorage;
    $this->acqPromotionPluginManager = $pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_promotion.manager'),
      $container->get('acq_cart.cart_storage'),
      $container->get('plugin.manager.acq_promotion')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'source' => 'static',
      'promotions' => [],
    ] + parent::defaultConfiguration();

  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $promotion_nodes = $this->alshayaAcmPromotionManager->getAllPromotions();
    $options = [];

    if (!empty($promotion_nodes)) {
      foreach ($promotion_nodes as $promotion_node) {
        // Only allow promotions with value "other".
        if ($promotion_node->get('field_alshaya_promotion_subtype')->getString() == AlshayaPromotionsManager::SUBTYPE_OTHER) {
          $promotion_rule_id = $promotion_node->get('field_acq_promotion_rule_id')->first()->getValue();
          $options[$promotion_rule_id['value']] = $promotion_node->getTitle();
        }
      }
    }

    $form['source'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Promotions Source'),
      '#options' => [
        'static' => $this->t('Static'),
        'dynamic' => $this->t('Dynamic'),
      ],
      '#weight' => -1,
    ];

    $form['promotions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Promotions'),
      '#description' => $this->t('Selection promotions to display in block.'),
      '#options' => $options,
      '#default_value' => $this->configuration['promotions'],
      '#weight' => '0',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['source'] = $form_state->getValue('source');
    $this->configuration['promotions'] = $form_state->getValue('promotions');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function build() {
    $build = [
      // We need empty markup to ensure wrapper div is always available.
      '#markup' => '',
    ];

    if ($this->configuration['source'] === 'static') {
      $this->getStaticBuild($build);
    }
    else {
      $this->getDynamicBuild($build);
    }

    return $build;
  }

  /**
   * Builds static promotions.
   *
   * @param array $build
   *   Render Array.
   */
  protected function getStaticBuild(array &$build) {
    $free_shipping = [];

    // This is for R1 and all promotions except for the three types for which
    // we check for conditions below.
    $selected_promotions = $this->configuration['promotions'];

    // Get all the rules applied in cart.
    $cartRulesApplied = $this->cartStorage->getCart(FALSE)->getCart()->cart_rules;

    $promotions = $this->alshayaAcmPromotionManager->getAllCartPromotions($selected_promotions, $cartRulesApplied);

    if (!empty($promotions) || !empty($free_shipping)) {
      $build = [
        '#theme' => 'cart_top_promotions',
        '#promotions' => $promotions,
        '#free_shipping' => $free_shipping,
      ];
    }
  }

  /**
   * Build dynamic promotions.
   *
   * @param array $build
   *   Render Array.
   */
  protected function getDynamicBuild(array &$build) {
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    try {
      $cart = $this->cartStorage->getCart(FALSE);

      if ($cart) {
        $count = $cart->getCartItemsCount();
        if ($count > 0) {
          return AccessResult::allowed();
        }
      }

      return AccessResult::forbidden();
    }
    catch (Exception $e) {
      return AccessResult::forbidden();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Cart will be different for every session, even guests will have session
    // as soon as they add something to cart.
    return Cache::mergeContexts(parent::getCacheContexts(), ['cookies:Drupal_visitor_acq_cart_id']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cacheTags = [];

    // It depends on cart id of the user.
    if ($cart = $this->cartStorage->getCart(FALSE)) {
      $cacheTags[] = 'cart:' . $cart->id();
    }

    // It depends on promotions content.
    $cacheTags[] = 'node_type:acq_promotion';

    return Cache::mergeTags(parent::getCacheTags(), $cacheTags);
  }

}
