<?php

namespace Drupal\alshaya_replicate\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\replicate_ui\Form\ReplicateUISettingsForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Override replicate ui settings.
 */
class AlshayaReplicateUISettingsForm extends ReplicateUISettingsForm {

  /**
   * The Entity Bundle Type Info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Overrides ReplicateUISettingsForm instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router builder.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The router builder.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $config_factory, RouteBuilderInterface $router_builder, EntityTypeBundleInfoInterface $entityTypeBundleInfo) {
    parent::__construct($entityTypeManager, $config_factory, $router_builder);
    $this->configFactory = $config_factory;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('router.builder'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_replicate.settings', 'replicate_ui.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['entity_types']['#ajax'] = [
      'callback' => [$this, 'getContentTypesCallback'],
      'wrapper' => 'content-types-list',
      'method' => 'replace',
      'event' => 'change',
    ];

    $form['content_types'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'content-types-list'],
    ];

    $entity_types = $this->config('replicate_ui.settings')->get('entity_types');
    $form_values = $form_state->getValues();

    if (!empty($form_values['entity_types']['node']) || (in_array('node', $entity_types))) {
      $entity_bundles = $this->getEntityBundles();
      foreach ($entity_bundles as $key => $bundle) {
        $options[$key] = $bundle['label'];
      }
      $form['content_types']['list'] = [
        '#type' => 'checkboxes',
        '#open' => TRUE,
        '#title' => $this->t('Content Types'),
        '#type' => 'checkboxes',
        '#title' => $this->t('Choose content types that you want to replicate'),
        '#options' => $options,
        '#default_value' => $this->config('alshaya_replicate.settings')->get('content_types'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $form_state->cleanValues();
    $form_values = $form_state->getValues();

    $bundle_names = [];
    if (!empty($form_values['entity_types']['node'])) {
      foreach (array_filter($form_values['list']) as $key => $type) {
        $bundle_names[$key] = $type;
      }
    }

    $this->config('alshaya_replicate.settings')->set('content_types', $bundle_names)->save();
  }

  /**
   * {@inheritdoc}
   */
  public static function getContentTypesCallback(array $form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    if (!empty($form_values['entity_types']['node'])) {
      return $form['content_types'];
    }
  }

  /**
   * Get list of bundles.
   */
  public function getEntityBundles() {
    static $bundles;
    if (!isset($bundles)) {
      $bundles = $this->entityTypeBundleInfo->getBundleInfo('node');
    }
    return $bundles;
  }

}
