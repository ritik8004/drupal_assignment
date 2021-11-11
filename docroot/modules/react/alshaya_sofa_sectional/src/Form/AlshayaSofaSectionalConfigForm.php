<?php

namespace Drupal\alshaya_sofa_sectional\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Alshaya Sofa Section config form.
 *
 * @package Drupal\dynamic_yield\Form
 */
class AlshayaSofaSectionalConfigForm extends ConfigFormBase {
  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AlshayaSofaSectionalConfigForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                       EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_sofa_sectional_settings_form';
  }

  /**
   * Get Config name.
   *
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return ['alshaya_sofa_sectional.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $entity_storage = $this->entityTypeManager->getStorage('taxonomy_term');

    // Query the terms sorted by weight.
    $query_result = $entity_storage->getQuery()
      ->condition('vid', 'acq_product_category')
      ->sort('weight', 'ASC')
      ->execute();

    // Load the terms.
    $terms = $entity_storage->loadMultiple($query_result);

    $options = [];
    foreach ($terms as $term) {
      $option_label = $term->getName();
      // Check if commerce ID is available append it with label.
      if ($term->get('field_commerce_id')->getString()) {
        $option_label .= ' - (' . $term->get('field_commerce_id')->getString() . ')';
      }
      $options[$term->id()] = $option_label;
    }

    $form['sofa_sectional_status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable sofa and sectional feature'),
      '#description' => $this->t('Enable this to apply category from the the list below.'),
      '#default_value' => $this->config('alshaya_sofa_sectional.settings')->get('enabled'),
    ];

    $form['sofa_sectional_categories'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Select Categories'),
      '#description' => $this->t('Select categories for those products you want to show sofa and sectional form. Category Ids showing above are Magento category IDs.'),
      '#options' => $options,
      '#default_value' => $this->config('alshaya_sofa_sectional.settings')->get('category_ids'),
      '#size' => 10,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_sofa_sectional.settings');
    $categories = $form_state->getValue('sofa_sectional_categories');
    $config->set('category_ids', array_values($categories));
    $config->set('enabled', $form_state->getValue('sofa_sectional_status'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
