<?php

namespace Drupal\alshaya_options_list\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Path\AliasStorage;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\Core\Language\LanguageInterface;

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
   * The alias storage.
   *
   * @var \Drupal\Core\Path\AliasStorage
   */
  protected $aliasStorage;

  /**
   * The alias manager interface.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * SKU Fields Manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  protected $skuFieldsManager;

  /**
   * AlshayaOptionsListForm constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service object.
   * @param \Drupal\Core\Path\AliasStorage $alias_storage
   *   The alias storage service.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The alias manager service.
   * @param \Drupal\acq_sku\SKUFieldsManager $sku_fields_manager
   *   SKU Fields Manager.
   */
  public function __construct(Connection $connection,
                              AliasStorage $alias_storage,
                              AliasManagerInterface $alias_manager,
                              SKUFieldsManager $sku_fields_manager) {
    $this->connection = $connection;
    $this->aliasStorage = $alias_storage;
    $this->aliasManager = $alias_manager;
    $this->skuFieldsManager = $sku_fields_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('path.alias_storage'),
      $container->get('path.alias_manager'),
      $container->get('acq_sku.fields_manager')
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
    $attribute_options = $config->get('alshaya_options_attributes');

    $form['alshaya_options_on_off'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('alshaya_options_on_off'),
      '#title' => $this->t('Enable options page on site.'),
    ];

    $form['alshaya_options_page_url'] = [
      '#type' => 'textfield',
      '#default_value' => $config->get('alshaya_options_page_url'),
      '#title' => $this->t('Page url on which options should be displayed.'),
    ];

    $form['alshaya_options_attributes'] = [
      '#type' => 'checkboxes',
      '#options' => $this->getAttributeCodeOptions(),
      '#default_value' => !empty($attribute_options) ? $attribute_options : [],
      '#title' => $this->t('The attribute to list on the options page.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_options_list.admin_settings');
    $alias = $form_state->getValue('alshaya_options_page_url');
    $config->set('alshaya_options_on_off', $form_state->getValue('alshaya_options_on_off'));
    $config->set('alshaya_options_page_url', $alias);
    $config->set('alshaya_options_attributes', $form_state->getValue('alshaya_options_attributes'));
    $config->save();

    // If an alias already exists for the path, load the pid.
    $path = '/shop-by';
    $pid = NULL;
    if ($existing_path = $this->aliasStorage->load(['source' => $path])) {
      $pid = $existing_path['pid'];
    }

    // Add a slash, if the alias, does not contain a trailing slash.
    if (isset($alias[0]) && $alias[0] != '/') {
      $alias = '/' . $alias;
    }
    $this->aliasStorage->save($path, $alias, LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid);

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
