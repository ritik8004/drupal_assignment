<?php

namespace Drupal\alshaya_acm_product\Plugin\Field\FieldFormatter;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\Plugin\Field\FieldFormatter\SKUFieldFormatter;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'sku_gallery_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "sku_gallery_formatter",
 *   label = @Translation("Sku gallery formatter"),
 *   field_types = {
 *     "sku"
 *   }
 * )
 */
class SkuGalleryFormatter extends SKUFieldFormatter implements ContainerFactoryPluginInterface {

  /**
   * The Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * The current route matcher service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * SkuGalleryFormatter constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Sku Manager service.
   */
  public function __construct($plugin_id,
                              $plugin_definition,
                              FieldDefinitionInterface $field_definition,
                              array $settings,
                              $label,
                              $view_mode,
                              array $third_party_settings,
                              CurrentRouteMatch $currentRouteMatch,
                              SkuManager $skuManager,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->skuManager = $skuManager;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Implement default settings.
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      // Implement settings form.
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \InvalidArgumentException
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $stock_mode = $this->configFactory->get('acq_sku.settings')->get('stock_mode');

    $promotion_page_nid = NULL;

    $current_route_name = $this->currentRouteMatch->getRouteName();
    $current_node = $this->currentRouteMatch->getParameter('node');
    if ($current_route_name === 'entity.node.canonical' && $current_node->bundle() === 'acq_promotion') {
      $promotion_page_nid = $current_node->id();
    }
    elseif ($current_route_name === 'views.ajax') {
      // We use $_REQUEST directly as views remove the value we want to access.
      if (isset($_REQUEST['view_args']) && $_REQUEST['view_args'] > 0) {
        $promotion_page_nid = $_REQUEST['view_args'];
      }
    }

    $elements = [];
    $product_url = $product_label = '';

    // Fetch Product in which this sku is referenced.
    $entity_adapter = $items->first()->getParent()->getParent();
    if ($entity_adapter instanceof EntityAdapter) {
      $node = $entity_adapter->getValue();
      if ($node instanceof Node) {
        $translatedNode = $node->getTranslation(\Drupal::service('language_manager')->getCurrentLanguage()->getId());
        $product_url = $translatedNode->url();
        $product_label = $translatedNode->getTitle();
      }
    }

    $thumbnails_display_status = $this->configFactory->get('alshaya_acm_product.display_settings')->get('image_thumb_gallery');
    foreach ($items as $delta => $item) {
      /** @var \Drupal\acq_sku\Entity\SKU $sku */
      $sku = $this->viewValue($item);
      $skus[$delta] = $sku;
      if ($sku instanceof SKU) {
        $promotion_cache_tags = [];

        $sku_media = $this->skuManager->getSkuMedia($sku);
        $search_main_image = $thumbnails = [];

        // Loop through all media items and prepare thumbnails array.
        foreach ($sku_media as $key => $media_item) {
          // For now we are displaying only image slider on search results page
          // and PLP.
          if ($media_item['media_type'] === 'image') {
            $media_item['label'] = $product_label;
            if (empty($search_main_image)) {
              $search_main_image = $this->skuManager->getSkuImage($media_item, '291x288');
            }

            if ($thumbnails_display_status) {
              $thumbnails[] = $this->skuManager->getSkuImage($media_item, '59x60', '291x288');
            }
          }
        }

        $promotion_types = ['cart'];
        $promotions = $this->skuManager->getPromotionsFromSkuId($sku, FALSE, $promotion_types);

        foreach ($promotions as $key => $promotion) {
          $promotions[$key]['render_link'] = TRUE;
          // Check if current page is promotion page,
          // render current promotion as text.
          if ($promotion_page_nid && $promotion_page_nid == $key) {
            $promotions[$key]['render_link'] = FALSE;
          }
          $promotion_cache_tags[] = 'node:' . $key;
        }

        $sku_gallery = [
          '#theme' => 'alshaya_search_gallery',
          '#mainImage' => $search_main_image,
          '#thumbnails' => $thumbnails,
          '#attached' => [
            'library' => [
              'alshaya_search/alshaya_search',
            ],
          ],
        ];

        $stock_placeholder = NULL;

        if (alshaya_acm_product_is_buyable($sku)) {
          if ($stock_mode == 'pull') {
            $stock_placeholder = [
              '#markup' => '<div class="stock-placeholder out-of-stock">' . t('Checking stock...') . '</div>',
            ];
          }
          // In push mode we check stock on page load only.
          elseif (!alshaya_acm_get_stock_from_sku($sku)) {
            $stock_placeholder = [
              '#markup' => '<div class="out-of-stock"><span class="out-of-stock">' . t('out of stock') . '</span></div>',
            ];
          }
        }

        $elements[$delta] = [
          '#theme' => 'sku_teaser',
          '#gallery' => $sku_gallery,
          '#product_url' => $product_url,
          '#product_label' => $product_label,
          '#promotions' => $promotions,
          '#stock_placeholder' => $stock_placeholder,
          '#cache' => [
            'tags' => array_merge($promotion_cache_tags, ['sku:' . $sku->id()]),
            'contexts' => ['url'],
          ],
        ];

        $this->skuManager->buildPrice($elements[$delta], $sku);

        $elements[$delta]['#price_block'] = $this->skuManager->getPriceBlock($sku);

        $sku_identifier = strtolower(Html::cleanCssIdentifier($sku->getSku()));
        $elements[$delta]['#price_block_identifier']['#markup'] = 'price-block-' . $sku_identifier;

        $elements[$delta]['#attached']['library'][] = 'alshaya_acm_product/stock_check';
      }
    }

    // Invoke the alter hook to allow all modules to update the element.
    \Drupal::moduleHandler()->alter('acq_sku_gallery_view', $elements, $skus);

    foreach ($elements as $delta => $element) {
      // If main image is empty.
      if (empty($element['#gallery']['#mainImage'])) {
        if (!empty($default_image = _alshaya_acm_product_get_product_default_main_image())) {
          $main_image = $this->skuManager->getSkuImage(['file' => $default_image], '291x288');
          $main_image['#attributes']['class'] = ['product-default-image'];
          $elements[$delta]['#gallery']['#mainImage'] = $main_image;

          if (empty($element['#gallery']['#thumbnails'])) {
            $elements[$delta]['#gallery']['#thumbnails'][] = $this->skuManager->getSkuImage(['file' => $default_image], '59x60', '291x288');
          }
        }
      }
    }

    return $elements;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_route_match'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('config.factory')
    );
  }

}
