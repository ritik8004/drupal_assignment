<?php

namespace Drupal\alshaya_options_list\Plugin\Block;

use Drupal\alshaya_options_list\AlshayaOptionsListHelper;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides options list page menu block.
 *
 * @Block(
 *   id = "alshaya_options_list_menu",
 *   admin_label = @Translation("Alshaya options list page menu")
 * )
 */
class AlshayaOptionsListMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Alshaya Options List Service.
   *
   * @var Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshaya_options_service
   *   Alshaya options service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              AlshayaOptionsListHelper $alshaya_options_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->alshayaOptionsService = $alshaya_options_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('alshaya_options_list.alshaya_options_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'link_title' => $this->t('Shop by'),
      'link_align' => 'right',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['link_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#description' => $this->t('Title to be displayed for the link.'),
      '#default_value' => $this->configuration['link_title'],
    ];
    $form['link_align'] = [
      '#type' => 'radios',
      '#title' => $this->t('Link alignment'),
      '#description' => $this->t('Align the menu to the left or right.'),
      '#default_value' => $this->configuration['link_align'] ?? '',
      '#options' => [
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['link_title'] = $form_state->getValue('link_title');
    $this->configuration['link_align'] = $form_state->getValue('link_align');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $menu_title = $this->configuration['link_title'];
    $alignment_class = 'alshaya-options-' . $this->configuration['link_align'];
    $links = $this->alshayaOptionsService->getOptionsPagesLinks();
    $menu_class = (is_countable($links) ? count($links) : 0) > 1 ? 'alshaya-multiple-links' : 'alshaya-single-link';

    return [
      '#theme' => 'alshaya_options_menu_link',
      '#menu_title' => $menu_title,
      '#links' => $links,
      '#attached' => [
        'library' => [
          'alshaya_white_label/optionlist_menu',
        ],
      ],
      '#attributes' => [
        'class' => [$alignment_class, $menu_class],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    return AccessResult::allowedIf($this->alshayaOptionsService->optionsPageEnabled());
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      AlshayaOptionsListHelper::OPTIONS_PAGE_CACHETAG,
      'config:alshaya_options_list.settings',
    ]);
  }

}
