<?php

namespace Drupal\alshaya_options_list\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\Core\Routing\RouteBuilderInterface;

/**
 * Class AlshayaOptionsListForm.
 */
class AlshayaOptionsListForm extends ConfigFormBase {

  /**
   * Database connection service object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * SKU Fields Manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  protected $skuFieldsManager;

  /**
   * The router builder.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * AlshayaOptionsListForm constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service object.
   * @param \Drupal\acq_sku\SKUFieldsManager $sku_fields_manager
   *   SKU Fields Manager.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router builder service.
   */
  public function __construct(Connection $connection,
                              SKUFieldsManager $sku_fields_manager,
                              RouteBuilderInterface $router_builder) {
    $this->connection = $connection;
    $this->skuFieldsManager = $sku_fields_manager;
    $this->routerBuilder = $router_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('acq_sku.fields_manager'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_options_list_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_options_list.admin_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_options_list.admin_settings');
    $attribute_options = $config->get('alshaya_options_pages');

    $form['alshaya_options_on_off'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('alshaya_options_on_off'),
      '#title' => $this->t('Enable options page on site.'),
    ];

    $form['#tree'] = TRUE;
    $form['alshaya_options_page'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Options Page Settings'),
      '#prefix' => '<div id="options-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#states' => [
        'visible' => [
          ':input[name="alshaya_options_on_off"]' => ['checked' => TRUE],
        ],
      ],
    ];

    if (count($attribute_options) == 0) {
      $temp_count = $form_state->get('temp_count') + 1;
    }
    else {
      $temp_count = $form_state->get('temp_count');
    }

    if (!empty($attribute_options)) {
      foreach ($attribute_options as $key => $attribute_option) {
        $form['alshaya_options_page'][$key] = [
          '#type' => 'fieldset',
          '#Collapsible' => TRUE,
        ];
        $form['alshaya_options_page'][$key]['alshaya_options_page_url'] = [
          '#type' => 'textfield',
          '#default_value' => $attribute_option['url'],
          '#title' => $this->t('Page url on which options should be displayed.'),
        ];

        $form['alshaya_options_page'][$key]['alshaya_options_attributes'] = [
          '#type' => 'checkboxes',
          '#options' => $this->getAttributeCodeOptions(),
          '#default_value' => !empty($attribute_option['attributes']) ? $attribute_option['attributes'] : [],
          '#title' => $this->t('The attribute to list on the options page.'),
        ];
      }
    }

    if ($temp_count > 0) {
      for ($i = 0; $i < $temp_count; $i++) {
        $form['alshaya_options_page'][$i] = [
          '#type' => 'fieldset',
          '#Collapsible' => TRUE,
        ];
        $form['alshaya_options_page'][$i]['alshaya_options_page_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Page url on which options should be displayed.'),
        ];

        $form['alshaya_options_page'][$i]['alshaya_options_attributes'] = [
          '#type' => 'checkboxes',
          '#options' => $this->getAttributeCodeOptions(),
          '#title' => $this->t('The attribute to list on the options page.'),
        ];
      }
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['alshaya_options_page']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add More'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addmoreCallback',
        'wrapper' => 'options-fieldset-wrapper',
      ],
    ];

    $form_state->setCached(FALSE);
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $options_field = $form_state->get('temp_count') ?? 0;
    $add_button = $options_field + 1;
    $form_state->set('temp_count', $add_button);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['alshaya_options_page'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_options_list.admin_settings');
    $config->set('alshaya_options_on_off', $form_state->getValue('alshaya_options_on_off'));
    $values = $form_state->getValue('alshaya_options_page');
    foreach ($values as $value) {
      $url = ltrim($value['alshaya_options_page_url'] ?? '', '/');
      $attributes = $value['alshaya_options_attributes'] ?? '';
      if (empty($url) || empty($attributes)) {
        continue;
      }
      $options_page = [
        'url' => $url,
        'attributes' => $attributes,
      ];
      $config->set('alshaya_options_pages.' . str_replace('/', '-', $url), $options_page);
    }

    $config->save();

    // Rebuild routes so that new routes get added.
    $this->routerBuilder->rebuild();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeCodeOptions() {
    $query = $this->connection->select('taxonomy_term__field_sku_attribute_code', 'tfa');
    $query->fields('tfa', ['field_sku_attribute_code_value']);
    $query->groupBy('tfa.field_sku_attribute_code_value');
    $options = $query->execute()->fetchAllKeyed(0, 0);

    // Only show those fields which have a facet.
    $fields = $this->skuFieldsManager->getFieldAdditions();
    foreach ($options as $key => $option) {
      if (!isset($fields[$option]['facet']) || $fields[$option]['facet'] != 1) {
        unset($options[$key]);
      }
    }
    return $options;
  }

}
