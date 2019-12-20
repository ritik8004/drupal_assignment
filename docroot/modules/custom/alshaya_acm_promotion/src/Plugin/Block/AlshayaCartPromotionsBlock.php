<?php

namespace Drupal\alshaya_acm_promotion\Plugin\Block;

use Drupal\acq_cart\CartStorageInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Driver\Exception\Exception;
use Drupal\node\NodeInterface;
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
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        AlshayaPromotionsManager $alshaya_acm_promotion_manager,
        CartStorageInterface $cartSessionStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaAcmPromotionManager = $alshaya_acm_promotion_manager;
    $this->cartStorage = $cartSessionStorage;
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
      $container->get('acq_cart.cart_storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'source' => 'dynamic',
      'promotion_types' => [
        'free_shipping_order' => 'free_shipping_order',
        'fixed_percentage_discount_order' => 'fixed_percentage_discount_order',
        'others' => 'others',
      ],
      'promotions' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['source'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Promotions Source'),
      '#options' => [
        'static' => $this->t('Static Text'),
        'dynamic' => $this->t('Dynamic Text'),
      ],
      '#default_value' => $this->configuration['source'],
      '#weight' => -1,
    ];

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
    $form['static'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Static Promotions Configurations'),
      '#states' => [
        'visible' => [
          ':input[name="settings[source]"]' => ['value' => 'static'],
        ],
      ],
    ];
    $form['static']['promotions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Promotions'),
      '#description' => $this->t('Selection promotions to display in block.'),
      '#options' => $options,
      '#default_value' => $this->configuration['promotions'],
      '#weight' => '0',
    ];

    $promotion_types = $this->alshayaAcmPromotionManager->getAcqPromotionTypes();
    $form['dynamic'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Dynamic Promotions Configurations'),
      '#states' => [
        'visible' => [
          ':input[name="settings[source]"]' => ['value' => 'dynamic'],
        ],
      ],
    ];
    $form['dynamic']['promotion_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Dynamic Promotions'),
      '#description' => $this->t('Selection promotions to display in block.'),
      '#options' => $promotion_types,
      '#default_value' => !empty($this->configuration['promotion_types']) ? $this->configuration['promotion_types'] : [],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['source'] = $values['source'];
    $this->configuration['promotions'] = $values['static']['promotions'];
    $this->configuration['promotion_types'] = $values['dynamic']['promotion_types'];
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
    $active_promotions = $this->getActivePromotionLabels();

    // Fetch inactive promotion labels.
    $inactive_promotions = $this->getInactivePromotionLabels();

    if (!empty($active_promotions) || !empty($inactive_promotions)) {
      $build = [
        '#theme' => 'cart_top_promotions',
        '#active_promotions' => $active_promotions,
        '#inactive_promotions' => $inactive_promotions,
      ];
    }
  }

  /**
   * Get cart promotions.
   *
   * @return array
   *   List of promotions.
   */
  protected function getCartPromotions() {
    $cartPromotionsApplied = &drupal_static(__FUNCTION__);

    if (!isset($cartPromotionsApplied)) {
      $cartRulesApplied = $this->cartStorage->getCart(FALSE)->getCart()->cart_rules;

      if (!empty($cartRulesApplied)) {
        foreach ($cartRulesApplied as $rule_id) {
          $promotion_node = $this->alshayaAcmPromotionManager->getPromotionByRuleId($rule_id);
          if ($promotion_node instanceof NodeInterface) {
            $cartPromotionsApplied[$rule_id] = $promotion_node;
          }
        }
      }
    }

    return $cartPromotionsApplied;
  }

  /**
   * Get Active promotion labels.
   *
   * @return array
   *   Promotion Data - Label and Type.
   */
  protected function getActivePromotionLabels() {
    $config = $this->configuration['promotion_types'];
    $active_promotions = [];

    if ($cartPromotionsApplied = $this->getCartPromotions()) {
      foreach ($cartPromotionsApplied as $rule_id => $promotion) {
        $promotion_type = $promotion->get('field_alshaya_promotion_subtype')->getString();

        // Process based on block config to display labels.
        if (!empty($config[$promotion_type])) {
          $promotion_data = $this->alshayaAcmPromotionManager->getPromotionData($promotion);

          if (!empty($promotion_data)) {
            $active_promotions[$rule_id] = [
              'type' => $promotion_data['type'],
              'label' => [
                '#markup' => $promotion_data['label'],
              ],
            ];
          }
        }
      }
    }

    return $active_promotions;
  }

  /**
   * Get Inactive promotion labels.
   *
   * @return array
   *   Promotion Data - Label and Type.
   */
  protected function getInactivePromotionLabels() {
    $config = $this->configuration['promotion_types'];
    $cartPromotionsApplied = $this->getCartPromotions() ?: [];
    $inactive_promotions = [];

    $applicableInactivePromotion = $this->alshayaAcmPromotionManager->getInactiveCartPromotion($config, $cartPromotionsApplied);

    if ($applicableInactivePromotion instanceof NodeInterface) {
      $rule_id = $applicableInactivePromotion->get('field_acq_promotion_rule_id')->getString();
      $promotion_data = $this->alshayaAcmPromotionManager->getPromotionData($applicableInactivePromotion, FALSE);

      if (!empty($promotion_data)) {
        $inactive_promotions[$rule_id] = [
          'type' => $promotion_data['type'],
          'label' => [
            '#markup' => $promotion_data['label'],
          ],
        ];
      }
    }

    return $inactive_promotions;
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
