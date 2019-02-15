<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\file\FileInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_acm_product\SkuManager;

/**
 * Class ProductSettingsForm.
 */
class ProductSettingsForm extends ConfigFormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Cache Backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * ProductSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   SkuManager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager,
                              CacheBackendInterface $cache,
                              SkuManager $skuManager
  ) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->cache = $cache;
    $this->skuManager = $skuManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('cache.default'),
      $container->get('alshaya_acm_product.skumanager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'product_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.settings');
    $config->set('show_cart_form_in_related', $form_state->getValue('show_cart_form_in_related'));
    $config->set('related_items_size', $form_state->getValue('related_items_size'));
    $config->set('list_view_items_per_page', $form_state->getValue('list_view_items_per_page'));
    $config->set('auto_load_trigger_offset', $form_state->getValue('auto_load_trigger_offset'));
    $config->set('cross_up_sell_items_settings.pdp_carousel_items_size_0', $form_state->getValue('pdp_carousel_items_size_0'));
    $config->set('cross_up_sell_items_settings.pdp_carousel_items_size_768', $form_state->getValue('pdp_carousel_items_size_768'));
    $config->set('cross_up_sell_items_settings.pdp_carousel_items_size_1025', $form_state->getValue('pdp_carousel_items_size_1025'));

    $config->set('list_view_auto_page_load_count', $form_state->getValue('list_view_auto_page_load_count'));
    $config->set('brand_logo_base_path', $form_state->getValue('brand_logo_base_path'));
    $config->set('brand_logo_extension', $form_state->getValue('brand_logo_extension'));
    $config->set('all_products_buyable', $form_state->getValue('all_products_buyable'));
    $config->set('not_buyable_message', $form_state->getValue('not_buyable_message'));
    $config->set('not_buyable_help_text', $form_state->getValue('not_buyable_help_text'));
    $config->set('vat_text', $form_state->getValue('vat_text'));
    $config->set('vat_text_footer', $form_state->getValue('vat_text_footer'));
    $config->set('back_to_list', $form_state->getValue('back_to_list'));
    $config->set('pdp_layout', $form_state->getValue('pdp_layout'));

    // Product default image.
    $product_default_image = NULL;

    // Product default image.
    $config->set('product_default_image', NULL);
    if (!empty($default_image = $form_state->getValue('product_default_image'))) {
      $file = $this->storeDefaultImageInSystem($default_image);
      if ($file instanceof FileInterface) {
        $config->set('product_default_image', $file->id());
        $product_default_image = $file;
      }
    }

    // Set the cache for default product image.
    $this->cache->set('product_default_image', $product_default_image);

    $config->save();

    // Invalidate caches so that PDP reflects the changes.
    $this->skuManager->invalidatePdpCache();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_acm_product.settings');

    $form['show_cart_form_in_related'] = [
      '#type' => 'select',
      '#options' => [
        0 => $this->t('no'),
        1 => $this->t('yes'),
      ],
      '#default_value' => $config->get('show_cart_form_in_related'),
      '#title' => $this->t('Show add to cart form in related item blocks'),
      '#description' => $this->t('Show add to cart form in Up sell / Cross sell / Related products blocks.'),
    ];

    $form['related_items_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of related items to show'),
      '#description' => $this->t('Number of related items to show in Up sell / Cross sell / Related products blocks.'),
      '#required' => TRUE,
      '#default_value' => $config->get('related_items_size'),
    ];

    $form['list_view_items_per_page'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Number of items to show on listing pages'),
      '#description' => $this->t('Number of items to show per page for listing pages like PLP / Search pages. Please clear all caches after updating this.'),
      '#required' => TRUE,
      '#default_value' => $config->get('list_view_items_per_page'),
    ];

    $form['list_view_auto_page_load_count'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of pages to load automatically'),
      '#description' => $this->t('Number of pages to load automatically on scroll down, before showing button to load more content. Set this to 0 to disable this feature.'),
      '#required' => TRUE,
      '#default_value' => $config->get('list_view_auto_page_load_count'),
    ];

    $form['auto_load_trigger_offset'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Distance away from load more button where we need to trigger auto-loading.'),
      '#description' => $this->t('This is the scoll offset where we want to start pre-loading the next page items. Values should be in integer without any units e.g., 800.'),
      '#required' => TRUE,
      '#default_value' => $config->get('auto_load_trigger_offset'),
    ];

    $form['back_to_list'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable back to list'),
      '#description' => $this->t('This will enable the back button feature on search/plp/promo pages.'),
      '#default_value' => $config->get('back_to_list'),
    ];

    $form['cross_up_sell_items_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Cross sell / Up sell carousel settings'),
      '#open' => TRUE,
    ];

    $form['cross_up_sell_items_settings']['pdp_carousel_items_size_0'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile view'),
      '#description' => $this->t('Number of items to show in Up sell / Cross sell carousel blocks.'),
      '#required' => TRUE,
      '#default_value' => $config->get('cross_up_sell_items_settings.pdp_carousel_items_size_0'),
    ];

    $form['cross_up_sell_items_settings']['pdp_carousel_items_size_768'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tablet view'),
      '#description' => $this->t('Number of items to show in Up sell / Cross sell carousel blocks.'),
      '#required' => TRUE,
      '#default_value' => $config->get('cross_up_sell_items_settings.pdp_carousel_items_size_768'),
    ];

    $form['cross_up_sell_items_settings']['pdp_carousel_items_size_1025'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Desktop view'),
      '#description' => $this->t('Number of items to show in Up sell / Cross sell carousel blocks.'),
      '#required' => TRUE,
      '#default_value' => $config->get('cross_up_sell_items_settings.pdp_carousel_items_size_1025'),
    ];

    $form['brand_logo_base_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base path on server for Brand Logo'),
      '#description' => $this->t('Do not include trailing or leading slashes.'),
      '#required' => TRUE,
      '#default_value' => $config->get('brand_logo_base_path'),
    ];

    $form['brand_logo_extension'] = [
      '#type' => 'textfield',
      '#title' => $this->t('File extension for Brand Logo'),
      '#description' => $this->t('Do not include leading dots.'),
      '#required' => TRUE,
      '#default_value' => $config->get('brand_logo_extension'),
    ];

    $form['all_products_buyable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Set all products to be buyable'),
      '#default_value' => $config->get('all_products_buyable'),
    ];

    $form['not_buyable_message'] = [
      '#type' => 'text_format',
      '#format' => $config->get('not_buyable_message.format'),
      '#title' => $this->t('Not-buyable product message'),
      '#default_value' => $config->get('not_buyable_message.value'),
    ];

    $form['not_buyable_help_text'] = [
      '#type' => 'text_format',
      '#format' => $config->get('not_buyable_help_text.format'),
      '#title' => $this->t('Not-buyable product help text'),
      '#default_value' => $config->get('not_buyable_help_text.value'),
    ];

    $form['vat_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('VAT Inclusion text'),
      '#default_value' => $config->get('vat_text'),
    ];

    $form['vat_text_footer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('VAT disclaimer text for the footer'),
      '#default_value' => $config->get('vat_text_footer'),
    ];

    $form['product_default_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Product default image'),
      '#description' => $this->t('Please upload image of resolution of 797X647 or more.'),
      '#upload_location' => 'public://product_default_image/',
      '#upload_validators'  => [
        'file_validate_extensions' => ['png gif jpg jpeg svg'],
      ],
      '#default_value' => !empty($config->get('product_default_image')) ? [$config->get('product_default_image')] : [],
    ];

    // Prepare PDP layout select options.
    $pdp_layout_options = alshaya_acm_product_pdp_layout_values();
    unset($pdp_layout_options['inherit']);
    $form['pdp_layout'] = [
      '#type' => 'select',
      '#title' => $this->t('PDP layout'),
      '#description' => $this->t('This will change the layout/appearence of the PDP page.'),
      '#options' => $pdp_layout_options,
      '#default_value' => $config->get('pdp_layout'),
    ];

    return $form;
  }

  /**
   * Stores the default image in system.
   *
   * @param array $default_image
   *   Default image value.
   *
   * @return null|\Drupal\file\Entity\File
   *   File object.
   */
  protected function storeDefaultImageInSystem(array $default_image) {
    if (!empty($default_image)) {
      $file = $this->entityTypeManager->getStorage('file')->load($default_image[0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
        return $file;
      }
    }

    return NULL;
  }

}
