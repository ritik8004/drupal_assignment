<?php

namespace Drupal\alshaya_acm\Form;

use Drupal\acq_commerce\Conductor\IngestAPIWrapper;
use Drupal\acq_commerce\I18nHelper;
use Drupal\acq_promotion\AcqPromotionsManager;
use Drupal\acq_sku\CategoryManagerInterface;
use Drupal\alshaya_product_options\ProductOptionsHelper;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\alshaya_addressbook\AlshayaAddressBookManagerInterface;
use Drupal\alshaya_admin\QueueHelper;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_stores_finder_transac\StoresFinderManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\taxonomy\TermInterface;
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
   * Product Options Helper.
   *
   * @var \Drupal\alshaya_product_options\ProductOptionsHelper
   */
  private $productOptionshelper;

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
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * ProductSyncForm constructor.
   *
   * @param \Drupal\acq_sku\CategoryManagerInterface $product_categories_manager
   *   Category manager interface.
   * @param \Drupal\alshaya_product_options\ProductOptionsHelper $productOptionsHelper
   *   Product Options Helper.
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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(
    CategoryManagerInterface $product_categories_manager,
    ProductOptionsHelper $productOptionsHelper,
    AcqPromotionsManager $promotions_manager,
    IngestAPIWrapper $ingest_api,
    AlshayaApiWrapper $alshaya_api,
    I18nHelper $i18n_helper,
    LanguageManagerInterface $language_manager,
    StoresFinderManager $stores_manager,
    AlshayaAddressBookManager $address_book_manager,
    QueueHelper $queue_helper,
    EntityTypeManagerInterface $entity_type_manager,
    ModuleHandlerInterface $module_handler
  ) {
    $this->productCategoriesManager = $product_categories_manager;
    $this->productOptionshelper = $productOptionsHelper;
    $this->promotionsManager = $promotions_manager;
    $this->ingestApi = $ingest_api;
    $this->alshayaApi = $alshaya_api;
    $this->i18nHelper = $i18n_helper;
    $this->languageManager = $language_manager;
    $this->storesManager = $stores_manager;
    $this->addressBookManager = $address_book_manager;
    $this->queueHelper = $queue_helper;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_sku.category_manager'),
      $container->get('alshaya_product_options.helper'),
      $container->get('acq_promotion.promotions_manager'),
      $container->get('acq_commerce.ingest_api'),
      $container->get('alshaya_api.api'),
      $container->get('acq_commerce.i18n_helper'),
      $container->get('language_manager'),
      $container->get('alshaya_stores_finder_transac.manager'),
      $container->get('alshaya_addressbook.manager'),
      $container->get('alshaya_admin.queue_helper'),
      $container->get('entity_type.manager'),
      $container->get('module_handler')
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
        $response = $this->productCategoriesManager->synchronizeTree('acq_product_category');
        $deleted_orphans = [];
        $not_deleted_orphans = [];
        // If there is any term update/create during category sync.
        if (!empty($response['created']) || !empty($response['updated'])) {
          // Get orphan terms.
          $all_orphan_terms = $to_be_delete_orphans_terms = $this->productCategoriesManager->getOrphanCategories($response);
          // If there is any orphan term.
          if (!empty($to_be_delete_orphans_terms)) {
            // Allow other modules to skipping the deleting of terms.
            $this->moduleHandler->alter('acq_sku_sync_categories_delete', $to_be_delete_orphans_terms);

            // If there are orphans which we not deleting (due to alter hook).
            if (count($all_orphan_terms) != count($to_be_delete_orphans_terms)) {
              // Get orphans which are not deleted.
              $orphan_diff = array_diff($all_orphan_terms, $to_be_delete_orphans_terms);
              $not_deleted_orphans = array_map(function ($orphan) {
                return $orphan['name'];
              }, $orphan_diff);
            }

            foreach (array_keys($to_be_delete_orphans_terms) as $tid) {
              $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
              if ($term instanceof TermInterface) {
                $deleted_orphans[] = $term->getName();
                $term->delete();
              }
            }
          }
        }

        drupal_set_message($this->t('Product categories synchronization complete.'), 'status');

        // If any term deleted.
        if (!empty($deleted_orphans)) {
          $this->messenger()->addMessage($this->t('Orphan terms @deleted_terms deleted successfully.', [
            '@deleted_terms' => implode(', ', $deleted_orphans),
          ]));
        }

        // If any orphan term not deleted.
        if (!empty($not_deleted_orphans)) {
          $this->messenger()->addMessage($this->t('Orphan terms @not_deleted_terms not deleted.', [
            '@not_deleted_terms' => implode(', ', $not_deleted_orphans),
          ]));
        }
        break;

      case $this->t('Synchronize product options'):
        $this->productOptionshelper->synchronizeProductOptions();
        drupal_set_message($this->t('Product options synchronization complete.'), 'status');
        break;

      case $this->t('Synchronize listed SKUs'):
        $skus = array_map('trim', explode(',', $form_state->getValue('products_list_text')));
        foreach ($this->i18nHelper->getStoreLanguageMapping() as $langcode => $store_id) {
          // @TODO: Make the chunk size more realistic. Only limitation is the
          // length of the query sent to MDC.
          foreach (array_chunk($skus, 6) as $chunk) {
            // @TODO: Make page size a config. It can be used in multiple places.
            $this->ingestApi->productFullSync($store_id, $langcode, implode(',', $chunk), '', 2);
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
