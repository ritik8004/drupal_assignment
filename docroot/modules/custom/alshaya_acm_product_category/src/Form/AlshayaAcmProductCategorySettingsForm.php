<?php

namespace Drupal\alshaya_acm_product_category\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;

/**
 * Class Alshaya Acm Product Category Settings Form.
 */
class AlshayaAcmProductCategorySettingsForm extends ConfigFormBase {

  /**
   * Product category tree manager.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  private $productCategoryTree;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $productCategoryTree
   *   Product category tree manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              ProductCategoryTree $productCategoryTree) {
    $this->setConfigFactory($config_factory);
    $this->productCategoryTree = $productCategoryTree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('alshaya_acm_product_category.product_category_tree')
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
      ->set('new_arrival_category_ids', $form_state->getValue('new_arrival_category_ids'))
      ->set('old_categorization_enabled', $form_state->getValue('old_categorization_enabled'))
      ->set('enable_lhn_tree', $form_state->getValue('enable_lhn_tree'))
      ->set('grouping_page_header_style', $form_state->getValue('grouping_page_header_style'))
      ->set('enable_auto_sale_categorisation', $form_state->getValue('enable_auto_sale_categorisation'))
      ->set('enable_lhn_tree_search', $form_state->getValue('enable_lhn_tree_search'))
      ->set('all_categories_department_page', $form_state->getValue('all_categories_department_page'))

      ->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product_category.settings');

    $options = $this->productCategoryTree->getChildTermIds();
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

    $form['enable_auto_sale_categorisation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Auto Sale Categorization.'),
      '#description' => $this->t('Enable auto sale categorization if sale category is not selected.'),
      '#states' => [
        'unchecked' => [
          ':input[name="sale_category_ids[]"]' => ['!value' => []],
        ],
        'checked' => [
          ':input[name="sale_category_ids[]"]' => ['value' => $config->get('sale_category_ids')],
        ],
      ],
    ];

    $new_arrivals = [];

    foreach ($options as $tid => $name) {
      if ($children = $this->productCategoryTree->getChildTermIds($tid)) {
        $new_arrivals[$name] = $children;
      }
    }

    $form['new_arrival_category_ids'] = [
      '#type' => 'select',
      '#title' => $this->t('New-Arrival Categories'),
      '#description' => $this->t('Sub-categories (tree) of selected L2 categories will be considered as New-Arrival categories.'),
      '#required' => FALSE,
      '#multiple' => TRUE,
      '#options' => $new_arrivals,
      '#size' => count($new_arrivals) + 1,
      '#default_value' => $config->get('new_arrival_category_ids'),
    ];

    $form['old_categorization_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable old categorization rule'),
      '#description' => $this->t('Checking this will disable the old categorization rule and will use the new `is_sale` and `is_new` based rule.'),
      '#default_value' => $config->get('old_categorization_enabled'),
    ];

    $form['enable_lhn_tree'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable LHN'),
      '#description' => $this->t('LHN is a left sidebar tree of categories which will be available on PLP pages for Desktop.'),
      '#default_value' => $config->get('enable_lhn_tree'),
    ];

    $form['enable_lhn_tree_search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable LHN For Search Page'),
      '#description' => $this->t('LHN is a left sidebar tree of categories which will be available on search pages for Desktop.'),
      '#default_value' => $config->get('enable_lhn_tree_search'),
    ];

    $form['grouping_page_header_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Grouping Page Header Style'),
      '#description' => $this->t('Select header style that should be displayed in selected manner when any sub-category is displayed on a PLP grouped by sub-categories.'),
      '#required' => FALSE,
      '#options' => [
        'left_aligned' => $this->t('Left Aligned'),
        'center_aligned' => $this->t('Center Aligned'),
      ],
      '#default_value' => $config->get('grouping_page_header_style'),
    ];

    $form['all_categories_department_page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow all categories under department page.'),
      '#description' => $this->t('Allow all the levels of categories can be used under department page.'),
      '#default_value' => $config->get('all_categories_department_page'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
