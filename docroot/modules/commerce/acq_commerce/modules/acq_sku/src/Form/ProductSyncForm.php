<?php

namespace Drupal\acq_sku\Form;

use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_sku\CategoryManagerInterface;
use Drupal\acq_commerce\Conductor\IngestAPIWrapper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContentEntityExampleSettingsForm.
 *
 * @package Drupal\acq_sku\Form
 *
 * @ingroup acq_sku
 */
class ProductSyncForm extends FormBase {

  /**
   * Conductor Category Manager.
   *
   * @var \Drupal\acq_sku\CategoryManagerInterface
   */
  private $catManager;

  /**
   * Conductor Ingest API Helper.
   *
   * @var \Drupal\acq_commerce\Conductor\IngestAPIWrapper
   */
  private $ingestApi;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * ProductSyncForm constructor.
   *
   * @param \Drupal\acq_sku\CategoryManagerInterface $cat_manager
   *   CategoryManagerInterface instance.
   * @param \Drupal\acq_commerce\Conductor\IngestAPIWrapper $api
   *   IngestAPIWrapper object.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   */
  public function __construct(CategoryManagerInterface $cat_manager, IngestAPIWrapper $api, I18nHelper $i18n_helper) {
    $this->catManager = $cat_manager;
    $this->ingestApi = $api;
    $this->i18nHelper = $i18n_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_sku.category_manager'),
      $container->get('acq_commerce.ingest_api'),
      $container->get('acq_commerce.i18n_helper')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'acq_sku_sync';
  }

  /**
   * Define the form used for settings.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['actions']['#type'] = 'actions';

    $form['actions']['cats'] = [
      '#type' => 'submit',
      '#value' => $this->t('Synchronize Categories'),
    ];

    $form['actions']['products'] = [
      '#type' => 'submit',
      '#value' => $this->t('Synchronize Products'),
    ];

    return ($form);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $action = $form_state->getUserInput()['op'];

    switch ($action) {
      case 'Synchronize Categories':
        $this->catManager->synchronizeTree('acq_product_category');
        $this->messenger()->addMessage($this->t('Category Synchronization Complete.'), 'status');
        break;

      case 'Synchronize Products':
        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          $this->ingestApi->productFullSync($store_id, $langcode);
        }
        $this->messenger()->addMessage($this->t('Product Synchronization Processing...'), 'status');
        break;
    }
  }

}
