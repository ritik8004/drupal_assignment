<?php

namespace Drupal\alshaya_promo_panel\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Path\PathValidatorInterface;

/**
 * Admin form for promo panel config.
 */
class AlshayaPromoPanelForm extends ConfigFormBase {

  /**
   * The block storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $blockStorage;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity config prefix.
   *
   * @var string
   */
  protected $configPrefix;

  /**
   * Constructs a AlshayaPromoPanelForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Block field manger service.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, PathValidatorInterface $path_validator) {
    parent::__construct($config_factory);
    $this->configFactory = $config_factory;
    $this->blockStorage = $entity_type_manager->getStorage('block');
    $this->configPrefix = $entity_type_manager->getDefinition('block')->getConfigPrefix();
    $this->pathValidator = $path_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('path.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_promo_panel.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_promo_panel_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_promo_panel.settings');

    $current_theme = $this->configFactory->get('system.theme')->get('default');
    $blocks = $this->blockStorage->loadByProperties([
      'theme' => $current_theme,
      'region' => 'footer_secondary',
    ]);

    $options = [];
    if (!empty($blocks)) {
      foreach ($blocks as $id => $block) {
        $options[$id] = $block->label();
      }
    }

    $promo_panel_blocks = !empty($config->get('promo_panel_blocks')) ? $config->get('promo_panel_blocks') : [];
    $form['blocks'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select Promo panel block(s)'),
      '#description' => $this->t('Select block(s) which should displayed as promo panel.'),
      '#options' => $options,
      '#multiple' => TRUE,
      '#default_value' => array_keys($promo_panel_blocks),
      '#ajax' => [
        'callback' => $this->buildAjaxConfigForm(...),
        'wrapper' => 'page-urls',
        'effect' => 'fade',
      ],
    ];

    $allvalues = $form_state->getValues();
    if (!empty($allvalues['blocks'])) {
      $default_blocks = array_filter($allvalues['blocks']);
    }
    elseif ((is_countable($promo_panel_blocks) ? count($promo_panel_blocks) : 0) > 0) {
      $default_blocks = array_keys($promo_panel_blocks);
    }

    $form['urls'] = [
      '#type' => 'container',
      '#tree' => FALSE,
      '#prefix' => '<div id="page-urls">',
      '#suffix' => '</div>',
    ];

    if (!empty($default_blocks)) {
      $form['urls']['page_urls'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Configure page urls for selected blocks'),
        '#description' => $this->t('Add page urls, user will navigate to this page while accessing from mobile.'),
        '#tree' => TRUE,
      ];

      foreach ($default_blocks as $id) {
        if (!isset($blocks[$id])) {
          continue;
        }
        $form['urls']['page_urls'][$id] = [
          '#type' => 'textfield',
          '#title' => $this->t('Page url for @id block', ['@id' => $blocks[$id]->label()]),
          '#required' => TRUE,
          '#default_value' => !empty($promo_panel_blocks) ? $promo_panel_blocks[$id]['mobile_path'] : '',
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Ajax callback method to render textfield to add urls.
   */
  public function buildAjaxConfigForm(&$form, FormStateInterface $form_state) {
    return $form['urls'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $blocks = array_filter($form_state->getValue('blocks'));
    $block_values = !empty($blocks) ? $form_state->getValue('page_urls') : [];

    $tags = [];
    $promo_panel_blocks = [];
    foreach ($block_values as $machine_name => $link) {
      $tags[] = "config:{$this->configPrefix}.$machine_name";
      $block_load = $this->blockStorage->loadByProperties(['id' => $machine_name]);
      if ($block = reset($block_load)) {
        $promo_panel_blocks[$machine_name] = [
          'mobile_path' => !str_starts_with($link, '/') ? '/' . $link : $link,
          'plugin_id' => $block->getPluginId(),
        ];
      }
    }

    $promo_config = $this->config('alshaya_promo_panel.settings');
    $promo_config->set('promo_panel_blocks', $promo_panel_blocks);
    $promo_config->save();

    parent::submitForm($form, $form_state);
    Cache::invalidateTags($tags);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $blocks = array_filter($form_state->getValue('blocks'));
    $page_urls = $form_state->getValue('page_urls');

    if (!empty($blocks)) {
      foreach ($page_urls as $block => $page_url) {
        $url = $this->pathValidator->getUrlIfValid($page_url);
        if (!(bool) $url) {
          $form_state->setError($form['urls']['page_urls'][$block], $this->t('Value is not a valid path.'));
        }
        elseif ($url->isExternal()) {
          $form_state->setError($form['urls']['page_urls'][$block], $this->t('External url is not allowed.'));
        }
      }
    }
    parent::validateForm($form, $form_state);
  }

}
