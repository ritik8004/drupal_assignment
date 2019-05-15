<?php

namespace Drupal\alshaya_acm_product_category\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaAcmProductCategorySettingsForm.
 */
class AlshayaAcmProductCategorySettingsForm extends ConfigFormBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->setConfigFactory($config_factory);
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
    return 'alshaya_acm_product_category_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product_category.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_acm_product_category.settings')
      ->set('sale_category_ids', $form_state->getValue('sale_category_ids'))
      ->set('enable_lhn_tree', $form_state->getValue('enable_lhn_tree'))
      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product_category.settings');

    $options = $this->getL1TermIds();

    $form['sale_category_ids'] = [
      '#type' => 'select',
      '#title' => $this->t('SALE Categories'),
      '#description' => $this->t('Sub-categories (tree) of selected L1 categories will be considered as Sales categories.'),
      '#required' => FALSE,
      '#multiple' => TRUE,
      '#options' => $options,
      '#size' => count($options) + 1,
      '#default_value' => $config->get('sale_category_ids'),
    ];

    $form['enable_lhn_tree'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable LHN'),
      '#description' => $this->t('LHN is a left sidebar tree of categories which will be available on PLP pages for Desktop.'),
      '#default_value' => $config->get('enable_lhn_tree'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Wrapper function to get L1 categories.
   *
   * @return array
   *   Categories array with tid as key and name as value.
   *
   * @throws \Exception
   */
  private function getL1TermIds() {
    /** @var \Drupal\taxonomy\TermStorage $termStorage */
    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');
    $l1Terms = $termStorage->loadTree('acq_product_category', 0, 1);
    return $l1Terms ? array_column($l1Terms, 'name', 'tid') : [];
  }

}
