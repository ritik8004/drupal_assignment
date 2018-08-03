<?php

namespace Drupal\alshaya_acm\Form;

use Drupal\acq_commerce\Conductor\IngestAPIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_promotion\AcqPromotionsManager;
use Drupal\acq_sku\CategoryManagerInterface;
use Drupal\acq_sku\ProductOptionsManager;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\alshaya_addressbook\AlshayaAddressBookManagerInterface;
use Drupal\alshaya_admin\QueueHelper;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_stores_finder_transac\StoresFinderManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SyncForm.
 */
class SyncForm extends FormBase {


  /**
   * Conductor product categories manager.
   *
   * @var \Drupal\acq_sku\CategoryManagerInterface
   */
  private $productCategoriesManager;


  /**
   * Conductor product options manager.
   *
   * @var \Drupal\acq_sku\ProductOptionsManager
   */
  private $productOptionsManager;

  /**
   * Conductor promotions manager.
   *
   * @var \Drupal\acq_promotion\AcqPromotionsManager
   */
  private $promotionsManager;

  /**
   * Conductor Ingest API Helper.
   *
   * @var \Drupal\acq_commerce\Conductor\IngestAPIWrapper
   */
  private $ingestApi;

  /**
   * Alshaya API Helper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  private $alshayaApi;

  /**
   * I18n Helper.
   *
   * @var \Drupal\acq_commerce\I18nHelper
   */
  private $i18nHelper;

  /**
   * Language Manager service object.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Stores Finder Manager service object.
   *
   * @var \Drupal\alshaya_stores_finder_transac\StoresFinderManager
   */
  private $storesManager;

  /**
   * Address Book Manager service object.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  private $addressBookManager;

  /**
   * Queue Helper service object.
   *
   * @var \Drupal\alshaya_admin\QueueHelper
   */
  private $queueHelper;

  /**
   * ProductSyncForm constructor.
   *
   * @param \Drupal\acq_sku\CategoryManagerInterface $product_categories_manager
   *   Category manager interface.
   * @param \Drupal\acq_sku\ProductOptionsManager $product_options_manager
   *   Product options manager interface.
   * @param \Drupal\acq_promotion\AcqPromotionsManager $promotions_manager
   *   Promotions manager interface.
   * @param \Drupal\acq_commerce\Conductor\IngestAPIWrapper $ingest_api
   *   IngestAPI manager interface.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api
   *   AlshayaAPI manager interface.
   * @param \Drupal\acq_commerce\I18nHelper $i18n_helper
   *   I18nHelper object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager service object.
   * @param \Drupal\alshaya_stores_finder_transac\StoresFinderManager $stores_manager
   *   Stores Finder Manager service object.
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   AddressBook Manager service object.
   * @param \Drupal\alshaya_admin\QueueHelper $queue_helper
   *   Queue Helper service object.
   */
  public function __construct(
    CategoryManagerInterface $product_categories_manager,
    ProductOptionsManager $product_options_manager,
    AcqPromotionsManager $promotions_manager,
    IngestAPIWrapper $ingest_api,
    AlshayaApiWrapper $alshaya_api,
    I18nHelper $i18n_helper,
    LanguageManagerInterface $language_manager,
    StoresFinderManager $stores_manager,
    AlshayaAddressBookManager $address_book_manager,
    QueueHelper $queue_helper) {
    $this->productCategoriesManager = $product_categories_manager;
    $this->productOptionsManager = $product_options_manager;
    $this->promotionsManager = $promotions_manager;
    $this->ingestApi = $ingest_api;
    $this->alshayaApi = $alshaya_api;
    $this->i18nHelper = $i18n_helper;
    $this->languageManager = $language_manager;
    $this->storesManager = $stores_manager;
    $this->addressBookManager = $address_book_manager;
    $this->queueHelper = $queue_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_sku.category_manager'),
      $container->get('acq_sku.product_options_manager'),
      $container->get('acq_promotion.promotions_manager'),
      $container->get('acq_commerce.ingest_api'),
      $container->get('alshaya_api.api'),
      $container->get('acq_commerce.i18n_helper'),
      $container->get('language_manager'),
      $container->get('alshaya_stores_finder_transac.manager'),
      $container->get('alshaya_addressbook.manager'),
      $container->get('alshaya_admin.queue_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_acm_sync';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $action = $form_state->getUserInput()['op'];

    switch ($action) {
      case $this->t('Synchronize listed SKUs'):
        if (empty($form_state->getValue('products_list_text'))) {
          $form_state->setErrorByName('products_list_text', $this->t('Please list at least one SKU.'));
        }
        break;

      case $this->t('Synchronize ALL products'):
        if (empty(array_filter($form_state->getValue('products_full_languages'), function ($v, $k) {
          return !empty($v);
        }, ARRAY_FILTER_USE_BOTH))) {
          // @TODO: Check why placing the error on checkboxes does not work.
          $form_state->setErrorByName('products_full_fieldset', $this->t('Please select at least one language.'));
        }
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $action = $form_state->getUserInput()['op'];

    switch ($action) {
      case $this->t('Synchronize product categories'):
        $this->productCategoriesManager->synchronizeTree('acq_product_category');
        drupal_set_message($this->t('Product categories synchronization complete.'), 'status');
        break;

      case $this->t('Synchronize product options'):
        $this->productOptionsManager->synchronizeProductOptions();
        drupal_set_message($this->t('Product options synchronization complete.'), 'status');
        break;

      case 'Synchronize listed SKUs':
        $skus = array_map('trim', explode(',', $form_state->getValue('products_list_text')));

        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          foreach (array_chunk($skus, 5) as $chunk) {
            // @TODO: Make page size a config. It can be used in multiple places.
            $this->ingestApi->productFullSync($store_id, $langcode, $chunk, 2);
          }
        }

        drupal_set_message($this->t('Selected products synchronization launched.'), 'status');
        break;

      case $this->t('Synchronize ALL products'):
        $languages = $this->languageManager->getLanguages();
        $message_addition = [];

        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          if (!empty($form_state->getValue('products_full_languages')[$langcode])) {
            $this->ingestApi->productFullSync($store_id, $langcode);

            $message_addition[] = $languages[$langcode]->getName();
          }
        }

        $message_addition = $this->formatPlural(
          count($message_addition),
          implode(' and ', $message_addition) . ' language',
          implode(' and ', $message_addition) . ' languages'
        );

        drupal_set_message($this->t('Full product synchronization launched on @addition.', [
          '@addition' => $message_addition,
        ]), 'status');
        break;

      case $this->t('Synchronize promotions'):
        $this->promotionsManager->syncPromotions();
        $this->queueHelper->processQueues(['acq_promotion_detach_queue', 'acq_promotion_attach_queue']);
        drupal_set_message($this->t('Promotions synchronization complete.'), 'status');
        break;

      case $this->t('Synchronize stores'):
        $this->storesManager->syncStores();
        drupal_set_message($this->t('Stores synchronization complete.'), 'status');
        break;

      case $this->t('Synchronize areas'):
        $this->addressBookManager->syncAreas();
        drupal_set_message($this->t('Areas synchronization complete.'), 'status');
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    drupal_set_message($this->t('Syncing data can have a performance impact. Please use with caution.'), 'warning');

    foreach ($this->languageManager->getLanguages() as $language) {
      $options[$language->getId()] = $language->getName();
    }

    // Product categories.
    $form['product_categories_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Product categories'),
      'product_categories' => [
        '#type' => 'actions',
        'product_categories_action' => [
          '#type' => 'submit',
          '#value' => $this->t('Synchronize product categories'),
        ],
      ],
    ];

    // Product options.
    $form['product_options_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Product options'),
      'product_options' => [
        '#type' => 'actions',
        'product_options_action' => [
          '#type' => 'submit',
          '#value' => $this->t('Synchronize product options'),
        ],
      ],
    ];

    // Products.
    $form['products_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Products'),
    ];
    // @TODO: Check if it is possible to specify the language.
    $form['products_fieldset']['products_list_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Specific SKUs'),
      'products_list_text' => [
        '#type' => 'textarea',
        '#title' => $this->t('SKUs'),
        '#description' => $this->t('A comma-separated list of SKUs.'),
      ],
      'products_list' => [
        '#type' => 'actions',
        'product_list_action' => [
          '#type' => 'submit',
          '#value' => $this->t('Synchronize listed SKUs'),
        ],
      ],
    ];
    $form['products_fieldset']['products_full_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Full'),
      'products_full_warning' => [
        '#type' => 'markup',
        '#prefix' => '<div class="messages messages--error"><h2 class="visually-hidden">' . $this->t('Warning message') . '</h2>',
        '#suffix' => '</div>',
        '#markup' => $this->t('Full synchronization is a VERY HEAVY asynchronous task. All SKUs will be queued for synchronization.')
        . ' ' . $this->t('Depending on the number of SKUs, it may take up to 12 hours to get all the SKUs synchronized.'),
      ],
      'products_full_validate' => [
        '#type' => 'checkbox',
        '#title' => $this->t('I understand the risk'),
      ],
      'products_full_container' => [
        '#type' => 'container',
        '#states' => [
          'invisible' => [
            'input[name="products_full_validate"]' => ['checked' => FALSE],
          ],
        ],
        'products_full_languages' => [
          '#type' => 'checkboxes',
          '#prefix' => '<div class="container-inline clearfix">',
          '#suffix' => '</div>',
          '#options' => $options,
        ],
        'products_full' => [
          '#type' => 'actions',
          'product_full_full' => [
            '#type' => 'submit',
            '#value' => $this->t('Synchronize ALL products'),
            '#button_type' => 'primary',
          ],
        ],
      ],
    ];

    // Promotions.
    // @TODO: Add checkbox to choose between cart and category promotions.
    $form['promotions_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Promotions'),
      'promotions' => [
        '#type' => 'actions',
        'promotions_action' => [
          '#type' => 'submit',
          '#value' => $this->t('Synchronize promotions'),
        ],
      ],
    ];

    // Stores.
    $form['stores_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Stores'),
      'stores' => [
        '#type' => 'actions',
        'stores_action' => [
          '#type' => 'submit',
          '#value' => $this->t('Synchronize stores'),
        ],
      ],
    ];

    if ($this->addressBookManager->getDmVersion() == AlshayaAddressBookManagerInterface::DM_VERSION_2) {
      // Areas.
      $form['areas_fieldset'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Areas'),
        'areas' => [
          '#type' => 'actions',
          'stores_action' => [
            '#type' => 'submit',
            '#value' => $this->t('Synchronize areas'),
          ],
        ],
      ];
    }

    $form['#attached']['library'][] = 'alshaya_acm/alshaya_acm.sync_form';

    return $form;
  }

}
