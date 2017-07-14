<?php

namespace Drupal\alshaya_acm_promotion\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        AlshayaPromotionsManager $alshaya_acm_promotion_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaAcmPromotionManager = $alshaya_acm_promotion_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_acm_promotion.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
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
      foreach ($promotion_nodes as $key => $promotion_node) {
        $promotion_rule_id = $promotion_node->get('field_acq_promotion_rule_id')->first()->getValue();
        $options[$promotion_rule_id['value']] = $promotion_node->getTitle();
      }
    }

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
    $this->configuration['promotions'] = $form_state->getValue('promotions');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function build() {
    $promotions = [];
    $selected_promotions = $this->configuration['promotions'];
    if (!empty($selected_promotions)) {
      foreach ($selected_promotions as $key => $promotion_rule_id) {
        if ($promotion_rule_id) {
          $node = $this->alshayaAcmPromotionManager->getPromotionByRuleId($promotion_rule_id);
          if ($node) {
            $promotions[] = $node->get('field_acq_promotion_description')->first()->getValue();
          }
        }
      }
    }

    $build = [
      '#theme' => 'cart_top_promotions',
      '#promotions' => $promotions,
    ];

    return $build;
  }

}
