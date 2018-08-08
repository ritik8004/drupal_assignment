<?php

namespace Drupal\alshaya_acm_product;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\AcqSkuLinkedSku;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya\AlshayaArrayUtils;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\file\FileInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SkuManager.
 *
 * @package Drupal\alshaya_acm_product
 */
class SkuManager {

  use StringTranslationTrait;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $connection;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The Entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Linked SKUs service.
   *
   * @var \Drupal\acq_sku\AcqSkuLinkedSku
   */
  protected $linkedSkus;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Cache Backend service for alshaya.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Cache Backend service for product labels.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $productLabelsCache;

  /**
   * Cache Backend service for product info.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $productCache;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current Route object.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRoute;

  /**
   * Node storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * File storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * SKU storage object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $skuStorage;

  /**
   * Request stock service object.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * SkuManager constructor.
   *
   * @param \Drupal\Core\Database\Driver\mysql\Connection $connection
   *   Database service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route
   *   Current Route object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   The lnaguage manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger service.
   * @param \Drupal\acq_sku\AcqSkuLinkedSku $linked_skus
   *   Linked SKUs service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param \Drupal\Core\Cache\CacheBackendInterface $product_labels_cache
   *   Cache Backend service for product labels.
   * @param \Drupal\Core\Cache\CacheBackendInterface $product_cache
   *   Cache Backend service for configurable price info.
   */
  public function __construct(Connection $connection,
                              ConfigFactoryInterface $config_factory,
                              CurrentRouteMatch $current_route,
                              RequestStack $request_stack,
                              EntityTypeManagerInterface $entity_type_manager,
                              LanguageManager $languageManager,
                              EntityRepositoryInterface $entityRepository,
                              LoggerChannelFactoryInterface $logger_factory,
                              AcqSkuLinkedSku $linked_skus,
                              ModuleHandlerInterface $module_handler,
                              CacheBackendInterface $cache,
                              CacheBackendInterface $product_labels_cache,
                              CacheBackendInterface $product_cache) {
    $this->connection = $connection;
    $this->configFactory = $config_factory;
    $this->currentRoute = $current_route;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->skuStorage = $entity_type_manager->getStorage('acq_sku');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->languageManager = $languageManager;
    $this->entityRepository = $entityRepository;
    $this->logger = $logger_factory->get('alshaya_acm_product');
    $this->linkedSkus = $linked_skus;
    $this->moduleHandler = $module_handler;
    $this->cache = $cache;
    $this->productLabelsCache = $product_labels_cache;
    $this->productCache = $product_cache;
  }

  /**
   * Get Image tag from media item array.
   *
   * @param array $media
   *   Media array containing image details.
   * @param string $image_style
   *   Image style to apply to the image.
   * @param string $rel_image_style
   *   For some sliders we may want full/big image url in rel.
   *
   * @return array
   *   Image build array.
   */
  public function getSkuImage(array $media, $image_style = '', $rel_image_style = '') {
    $media['label'] = $media['label'] ?? '';

    $image = [
      '#theme' => 'image_style',
      '#style_name' => $image_style,
      '#uri' => $media['file']->getFileUri(),
      '#title' => $media['label'],
      '#alt' => $media['label'],
    ];

    if ($rel_image_style) {
      $image['#attributes']['rel'] = ImageStyle::load($rel_image_style)->buildUrl($image['#uri']);
    }

    return $image;
  }

  /**
   * Helper function to add price, final_price and discount info in build array.
   *
   * @param array $build
   *   Build array to modify.
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU entity to use for getting price.
   */
  public function buildPrice(array &$build, SKU $sku_entity) {
    // Get the price, discounted price and discount.
    $build['price'] = $build['final_price'] = $build['discount'] = [];

    if ($sku_entity->bundle() == 'configurable') {
      $prices = $this->getMinPrices($sku_entity);
      $price = $prices['price'];
      $final_price = $prices['final_price'];
    }
    else {
      $price = (float) $sku_entity->get('price')->getString();
      $final_price = (float) $sku_entity->get('final_price')->getString();
    }

    if ($price) {
      $build['price'] = [
        '#theme' => 'acq_commerce_price',
        '#price' => $price,
      ];

      // Get the discounted price.
      if ($final_price) {
        // Final price could be same as price, we dont need to show discount.
        if ($final_price >= $price) {
          return;
        }

        $build['final_price'] = [
          '#theme' => 'acq_commerce_price',
          '#price' => $final_price,
        ];

        // Get discount if discounted price available.
        $discount = round((($price - $final_price) * 100) / $price);
        $build['discount'] = [
          '#markup' => $this->t('Save @discount%', ['@discount' => $discount]),
        ];
      }
    }
    elseif ($final_price) {
      $build['price'] = [
        '#theme' => 'acq_commerce_price',
        '#price' => $final_price,
      ];
    }
  }

  /**
   * Get minimum final price and associated initial price for configurable.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU Entity.
   *
   * @return array
   *   Minimum final price and associated initial price.
   */
  public function getMinPrices(SKU $sku_entity) {
    $prices = [
      'price' => (float) $sku_entity->get('price')->getString(),
      'final_price' => (float) $sku_entity->get('final_price')->getString(),
    ];

    // This function might get called from other places, add condition again
    // before processing for configurable products.
    if ($sku_entity->bundle() != 'configurable') {
      return $prices;
    }

    if ($cache = $this->getProductCachedData($sku_entity, 'price')) {
      return $cache;
    }

    $sku_price = 0;

    $combinations = $this->getConfigurableCombinations($sku_entity);
    $children = isset($combinations['by_sku']) ? array_keys($combinations['by_sku']) : [];

    foreach ($children as $child_sku_code) {
      try {
        $child_sku_entity = SKU::loadFromSku($child_sku_code, $sku_entity->language()->getId());

        if ($child_sku_entity instanceof SKU) {
          $price = (float) $child_sku_entity->get('price')->getString();
          $final_price = (float) $child_sku_entity->get('final_price')->getString();

          $new_sku_price = 0;
          if ($final_price > 0) {
            $new_sku_price = $sku_price > 0 ? min($sku_price, $final_price) : $final_price;
          }
          elseif ($price > 0) {
            $new_sku_price = $sku_price > 0 ? min($sku_price, $price) : $price;
          }

          // Do we need to update selected prices?
          if ($new_sku_price != 0) {
            // Have we found a new min final price?
            if ($sku_price != $new_sku_price) {
              $sku_price = $new_sku_price;
              $prices = ['price' => $price, 'final_price' => $final_price];
            }
            // Is the difference between initial an final bigger?
            elseif (
              $price != 0 && $final_price != 0 && $prices['price'] != 0 && $prices['final_price'] != 0
              && ($price - $final_price) > ($prices['price'] - $prices['final_price'])
            ) {
              $prices = ['price' => $price, 'final_price' => $final_price];
            }
          }
        }
      }
      catch (\Exception $e) {
        // Child SKU might be deleted or translation not available.
        // Log messages are already set in previous functions.
      }
    }

    // Set the price info to cache.
    $this->setProductCachedData($sku_entity, 'price', $prices);

    return $prices;
  }

  /**
   * Function to get price block build for a SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU Entity.
   * @param string $view_mode
   *   The view mode of ACQ product, if the value is teaser, VAT text won't be
   *   rendered.
   *
   * @return array
   *   Price block build array.
   */
  public function getPriceBlock(SKU $sku_entity, $view_mode = 'full') {
    $build = [];
    $vat_text = '';
    $this->buildPrice($build, $sku_entity);
    // Adding vat text to product page.
    // Do not pass VAT text part of the price block for teaser and
    // product_category_carousel modes.
    if ($view_mode != 'teaser' && $view_mode != 'product_category_carousel') {
      if ($this->currentRoute->getRouteName() == 'entity.node.canonical'
        || $this->currentRoute->getRouteName() == 'alshaya_acm_product.get_cart_form') {
        $vat_text = $this->configFactory->get('alshaya_acm_product.settings')->get('vat_text');
      }
    }
    $price_build = [
      '#theme' => 'product_price_block',
      '#price' => $build['price'],
      '#final_price' => $build['final_price'],
      '#discount' => $build['discount'],
      '#vat_text' => $vat_text,
    ];

    return $price_build;
  }

  /**
   * Helper function to build discounted price for Sku in cart.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   Sku entity from cart for which discount needs to be calculated.
   * @param float $item_price
   *   Unit price for the sku item in the cart.
   *
   * @return mixed
   *   Calculated cart item price.
   */
  public function buildCartItemPrice(SKU $sku_entity, $item_price) {
    $sku_cart_price['price'] = (float) $sku_entity->get('price')->getString();
    $final_price = (float) $item_price;

    if ($final_price !== $sku_cart_price['price']) {
      if ($final_price > $sku_cart_price['price']) {
        // There must be something wrong. Trust the price coming from commerce
        // backend.
        $sku_cart_price['price'] = $final_price;
        $this->logger->error(
          'The @sku sku has a final price greater than the initial price. There must be a synchronisation issue.',
          ['@sku' => $sku_entity->sku->value]
        );
      }
      else {
        $sku_cart_price['final_price'] = number_format($final_price, 3);
        $discount = round((($sku_cart_price['price'] - $final_price) * 100) / $sku_cart_price['price']);
        $sku_cart_price['discount']['prefix'] = $this->t('Save', [], ['context' => 'discount']);
        $sku_cart_price['discount']['value'] = $discount . '%';
      }
    }

    return $sku_cart_price;
  }

  /**
   * Helper function to fetch sku from entity id rather than loading the SKU.
   *
   * @param array $sku_entity_ids
   *   Entity id of the Sku item.
   *
   * @return array
   *   Array of Sku Ids of the item.
   */
  public function getSkusByEntityId(array $sku_entity_ids) {
    if (empty($sku_entity_ids)) {
      return [];
    }

    $query = $this->connection->select('acq_sku_field_data', 'asfd')
      ->fields('asfd', ['sku'])
      ->distinct()
      ->condition('id', $sku_entity_ids, 'IN');

    return $query->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Helper function to fetch entity id from sku rather than loading the SKU.
   *
   * @param array $sku_texts
   *   Sku text of the Sku item.
   *
   * @return array
   *   Array of Entity Ids of sku items.
   *
   * @throws \Drupal\Core\Database\InvalidQueryException
   */
  public function getEntityIdsBySku(array $sku_texts) {
    if (empty($sku_texts)) {
      return [];
    }

    $query = $this->connection->select('acq_sku_field_data', 'asfd')
      ->fields('asfd', ['id'])
      ->distinct()
      ->condition('sku', $sku_texts, 'IN');

    return $query->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Helper function to fetch child skus of a configurable Sku.
   *
   * @param mixed $sku
   *   sku text or Sku object.
   * @param bool $first_only
   *   Boolean flag to indicate if we want to load only the first child.
   *
   * @return \Drupal\acq_sku\Entity\SKU[]|\Drupal\acq_sku\Entity\SKU
   *   Array of child skus/ Child SKU when loading first child only.
   */
  public function getChildSkus($sku, $first_only = FALSE) {
    $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku);
    $child_skus = [];

    if ($sku_entity->getType() == 'configurable') {
      foreach ($sku_entity->get('field_configured_skus') as $child_sku) {
        try {
          $child_sku_entity = SKU::loadFromSku(
            $child_sku->getString(), $sku_entity->language()->getId()
          );

          if ($child_sku_entity instanceof SKU) {
            // Return the first valid SKU if only one is required.
            if ($first_only) {
              return $child_sku_entity;
            }

            $child_skus[] = $child_sku_entity;
          }
        }
        catch (\Exception $e) {
          continue;
        }
      }
    }

    return $child_skus;
  }

  /**
   * Get SKU based on attribute option id.
   *
   * @param \Drupal\acq_sku\Entity\SKU $parent_sku
   *   Parent Sku.
   * @param string $attribute
   *   Attribute to search for.
   * @param int $option_id
   *   Option id for selected attribute.
   *
   * @return \Drupal\acq_sku\Entity\SKU|null
   *   SKU object matching the attribute option id.
   */
  public function getChildSkuFromAttribute(SKU $parent_sku, $attribute, $option_id) {
    $combinations = $this->getConfigurableCombinations($parent_sku);
    $sku = reset($combinations['attribute_sku'][$attribute][$option_id]);
    return SKU::loadFromSku($sku);
  }

  /**
   * Get Promotion node object(s) related to provided SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   The SKU Entity, for which linked promotions need to be fetched.
   * @param bool $getLinks
   *   Boolen to identify if Links are required.
   * @param array $types
   *   Type of promotion to filter on.
   *
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   blank array, if no promotions found, else Array of promotion entities.
   */
  public function getPromotionsFromSkuId(SKU $sku,
                                         $getLinks = FALSE,
                                         array $types = ['cart', 'category']) {

    $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

    $promos = [];
    $promotion_nids = [];

    $promotion = $sku->get('field_acq_sku_promotions')->getValue();

    foreach ($promotion as $promo) {
      $promotion_nids[] = $promo['target_id'];
    }

    if (!empty($promotion_nids)) {
      $promotion_nids = array_unique($promotion_nids);

      $promotion_nodes = $this->nodeStorage->loadMultiple($promotion_nids);

      /* @var \Drupal\node\Entity\Node $promotion_node */
      foreach ($promotion_nodes as $promotion_node) {
        $promotion_type = $promotion_node->get('field_acq_promotion_type')->getString();

        if (in_array($promotion_type, $types, TRUE)) {
          // Get the promotion with language fallback, if it did not have a
          // translation for $langcode.
          $promotion_node = $this->entityRepository->getTranslationFromContext($promotion_node, $langcode);

          $promotion_text = $promotion_node->get('field_acq_promotion_label')->getString();

          // Let's not display links with empty text and show empty space.
          if (empty($promotion_text)) {
            continue;
          }

          $discount_type = $promotion_node->get('field_acq_promotion_disc_type')->getString();
          $discount_value = $promotion_node->get('field_acq_promotion_discount')->getString();

          if ($getLinks) {
            $promos[$promotion_node->id()] = $promotion_node->toLink($promotion_text)
              ->toString()
              ->getGeneratedLink();
          }
          else {
            $description = '';
            $description_item = $promotion_node->get('field_acq_promotion_description')->first();
            if ($description_item) {
              $description = $description_item->getValue();
            }
            $promos[$promotion_node->id()] = [
              'text' => $promotion_text,
              'description' => $description,
              'discount_type' => $discount_type,
              'discount_value' => $discount_value,
            ];
          }
        }
      }
    }

    // For configurable products there are many rules like rules on product
    // category that get applied to child SKUs even if they don't have the
    // category but parent SKU has the category.
    // To avoid issues in display we check for parent SKU promotions if current
    // SKU (child) has no promotions attached.
    // This is done here to reduce processing in Magento, current process
    // (indexer) in Magento is already heavy and requires enhancement, so
    // it is done in Drupal to avoid more performance issues Magento.
    if (empty($promos)) {
      if ($parentSku = $this->getParentSkuBySku($sku)) {
        return $this->getPromotionsFromSkuId($parentSku, $getLinks, $types);
      }
    }

    return $promos;
  }

  /**
   * Function to return labels files for a SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   Sku Entity.
   * @param string $type
   *   Type of image required - plp or pdp.
   * @param bool $reset
   *   Flag to reset cache and generate array again from serialized string.
   *
   * @return array
   *   Array of media files.
   */
  public function getLabels(SKU $sku_entity, $type = 'plp', $reset = FALSE) {
    static $static_labels_cache = [];

    $sku = $sku_entity->getSku();

    if (!$reset && !empty($static_labels_cache[$sku][$type])) {
      return $static_labels_cache[$sku][$type];
    }

    $static_labels_cache[$sku][$type] = [];

    $labels_data = $this->getSkuLabel($sku_entity);

    if (empty($labels_data)) {
      return [];
    }
    else {
      $image_key = $type . '_image';
      $text_key = $type . '_image_text';
      $position_key = $type . '_position';

      foreach ($labels_data as &$data) {
        $row = [];

        // Check if label is available for desired type.
        if (empty($data[$image_key])) {
          continue;
        }

        // Check if label is currently active.
        $from = strtotime($data['from']);
        $to = strtotime($data['to']);

        // First check if we have date filter.
        if ($from > 0 && $to > 0) {
          $now = REQUEST_TIME;

          // Now, check if current date lies between from and to dates.
          if ($from > $now || $to < $now) {
            continue;
          }
        }

        $fid = $this->productLabelsCache->get($data[$image_key]);

        if (empty($fid)) {
          try {
            // Prepare the File object when we access it the first time.
            $fid = $this->downloadLabelsImage($sku_entity, $data, $image_key);
            $this->productLabelsCache->set($data[$image_key], $fid, CacheBackendInterface::CACHE_PERMANENT);
          }
          catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            continue;
          }
        }
        else {
          $fid = $fid->data;
        }

        $image_file = $this->fileStorage->load($fid);

        $image = [
          '#theme' => 'image',
          '#uri' => $image_file->getFileUri(),
          '#title' => $data[$text_key],
          '#alt' => $data[$text_key],
        ];

        $row['image'] = render($image);
        $row['position'] = $data[$position_key];

        $static_labels_cache[$sku][$type][] = $row;

        // Disable subsequent images if flag is true.
        if ($data['disable_subsequents']) {
          break;
        }
      }
    }

    return $static_labels_cache[$sku][$type];
  }

  /**
   * Function to get the product label for given SKU.
   *
   * First try to get the product label from SKU and then Check for
   * parent SKU if given SKU return empty file.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU entity object.
   * @param bool $parent
   *   True if current sku is parent SKU, default to FALSE.
   *
   * @return array
   *   Return array of labels data.
   */
  protected function getSkuLabel(SKU $sku_entity, $parent = FALSE) {
    if ($labels = $sku_entity->get('attr_labels')->getString()) {
      $labels_data = unserialize($labels);
      if (!empty($labels_data)) {
        return $labels_data;
      }
      // Process only when current sku is not parent SKU.
      elseif (!$parent) {
        // Get parent sku of the sku.
        $parent_sku = $this->getParentSkuBySku($sku_entity);
        if (!empty($parent_sku)) {
          return $this->getSkuLabel($parent_sku, TRUE);
        }
      }
    }
    return [];
  }

  /**
   * Function to save image file into public dir.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   SKU entity object.
   * @param array $data
   *   File data.
   * @param string $file_key
   *   File key.
   *
   * @return int
   *   File id.
   */
  protected function downloadLabelsImage(SKU $sku_entity, array $data, $file_key) {
    if (empty($data[$file_key])) {
      throw new \Exception('Image not available.');
    }

    // Preparing args for all info/error messages.
    $args = ['@file' => $data[$file_key], '@sku_id' => $sku_entity->id()];

    // Download the file contents.
    $file_data = file_get_contents($data[$file_key]);

    // Check to ensure errors like 404, 403, etc. are catched and empty file
    // not saved in SKU.
    if (empty($file_data)) {
      throw new \Exception(new FormattableMarkup('Failed to download labels image file "@file" for SKU id @sku_id.', $args));
    }

    // Get the path part in the url, remove hostname.
    $path = parse_url($data[$file_key], PHP_URL_PATH);

    // Remove slashes from start and end.
    $path = trim($path, '/');

    // Get the file name.
    $file_name = basename($path);

    // Prepare the directory path.
    $directory = 'public://labels/' . str_replace('/' . $file_name, '', $path);

    // Prepare the directory.
    file_prepare_directory($directory, FILE_CREATE_DIRECTORY);

    // Save the file as file entity.
    // @TODO: Check for a way to remove old files and file objects.
    // To be done here and in SKU.php both.
    /** @var \Drupal\file\Entity\File $file */
    if ($file = file_save_data($file_data, $directory . '/' . $file_name, FILE_EXISTS_REPLACE)) {
      return $file->id();
    }
    else {
      throw new \Exception(new FormattableMarkup('Failed to save labels image file "@file" for SKU id @sku_id.', $args));
    }
  }

  /**
   * Helper function to fetch sku tree.
   *
   * @return array
   *   Sku tree with keyed by configurable sku entity id.
   */
  public function getSkuTree() {
    if (!empty($this->cache->get('sku_tree'))) {
      $sku_tree_cache = $this->cache->get('sku_tree');
      $sku_tree = $sku_tree_cache->data;
      return $sku_tree;
    }
    else {
      $query = $this->connection->select('acq_sku__field_configured_skus', 'asfcs');
      $query->fields('asfcs', []);
      $results = $query->execute()->fetchAll();
      $processed_skus = [];
      $sku_tree = [];

      foreach ($results as $result) {
        if (!in_array($result->field_configured_skus_value, $processed_skus)) {
          $sku_tree[$result->field_configured_skus_value] = $result->entity_id;
          $processed_skus[] = $result->field_configured_skus_value;
        }
      }

      $this->cache->set('sku_tree', $sku_tree, Cache::PERMANENT, ['acq_sku_list']);
    }

    return $sku_tree;
  }

  /**
   * Helper function to fetch sku text from entity_id.
   *
   * @param string $entity_id
   *   Entity id for which sku text needs to be fetched.
   *
   * @return string
   *   SKU text corresponding to entity_id.
   */
  public function getSkuTextFromId($entity_id) {
    $sku_text = $this->connection->select('acq_sku_field_data', 'asfd')
      ->fields('asfd', ['sku'])
      ->condition('asfd.id', $entity_id)
      ->range(0, 1)
      ->execute()->fetchField();

    return $sku_text;
  }

  /**
   * Helper function to fetch SKUs by langcode and type.
   *
   * @param string $langcode
   *   Language code.
   * @param string $type
   *   SKUs type (configurable, simple).
   *
   * @return array
   *   An array of SKUs.
   */
  public function getSkus($langcode, $type) {
    $query = $this->connection->select('acq_sku_field_data', 'asfd')
      ->fields('asfd', ['sku'])
      ->condition('type', $type, '=')
      ->condition('langcode', $langcode, '=');

    return array_keys($query->execute()->fetchAllKeyed(0, 0));
  }

  /**
   * Helper function to do a cheaper call to fetch skus for a promotion.
   *
   * @param \Drupal\node\Entity\Node $promotion
   *   Promotion for which we need to fetch skus.
   *
   * @return array
   *   List of skus related with a promotion.
   */
  public function getSkutextsForPromotion(Node $promotion) {
    $skus = [];

    $cid = 'promotions_sku_' . $promotion->id();
    if (!empty($this->cache->get($cid))) {
      $skus_cache = $this->cache->get($cid);
      $skus = $skus_cache->data;
    }
    else {
      // Get configurable SKUs.
      $query = $this->connection->select('acq_sku__field_acq_sku_promotions', 'fasp');
      $query->join('acq_sku_field_data', 'asfd', 'asfd.id = fasp.entity_id');
      $query->condition('fasp.field_acq_sku_promotions_target_id', $promotion->id());
      $query->condition('asfd.type', 'configurable');
      $query->fields('asfd', ['id', 'sku']);
      $query->distinct();
      $config_skus = $query->execute()->fetchAllKeyed(0, 1);

      // We may not have anything in Simple.
      $skus = $config_skus;

      // Get Simple SKUs.
      $query = $this->connection->select('acq_sku__field_acq_sku_promotions', 'fasp');
      $query->join('acq_sku_field_data', 'asfd', 'asfd.id = fasp.entity_id');
      $query->condition('fasp.field_acq_sku_promotions_target_id', $promotion->id());
      $query->condition('asfd.type', 'simple');
      $query->fields('asfd', ['id', 'sku']);
      $query->distinct();
      $simple_skus = $query->execute()->fetchAllKeyed(0, 1);

      if ($simple_skus) {
        $skus = array_unique(array_merge($skus, $simple_skus));

        // Get all parent SKUs for simple ones.
        $parent_skus = $this->getParentSkus($simple_skus);
        $skus = array_unique(array_merge($skus, $parent_skus));
      }

      $this->cache->set($cid, $skus, Cache::PERMANENT, ['acq_sku_list']);
    }

    return $skus;
  }

  /**
   * Function to format composition field content.
   *
   * @param array $array
   *   Array of composition field data.
   * @param bool $list
   *   Boolean value to generate or not generate list.
   *
   * @return string
   *   UL / LI HTML list.
   */
  public function transformCompositionArrayToList(array $array, $list = TRUE) {
    $out = '';
    $materials = [];

    if ($list) {
      $out = "<ul>";
    }

    foreach ($array as $key => $elem) {
      if (!is_array($elem)) {
        $materials[] = "$key $elem%";
      }
      else {
        // Eliminate "materials" from the list.
        if ((strcasecmp($key, 'materials') === 0) ||
          (strcasecmp($key, 'undefined') === 0)) {
          $out .= $this->transformCompositionArrayToList($elem, FALSE);
        }
        else {
          $out .= "<li>";
          if ($key) {
            $out .= "$key: ";
          }

          $out .= $this->transformCompositionArrayToList($elem, FALSE) . "</li>";
        }
      }
    }

    if ($list) {
      $out .= "</ul>";
    }
    elseif (!empty($materials)) {
      $out = implode('; ', $materials);
    }

    return $out;
  }

  /**
   * Helper function to get parent skus of all simple ones in one go.
   *
   * @param array $simple_skus
   *   Array containing simple skus.
   *
   * @return array
   *   Array containing all parent skus.
   */
  public function getParentSkus(array $simple_skus) {
    $query = $this->connection->select('acq_sku_field_data', 'acq_sku');
    $query->addField('acq_sku', 'id');
    $query->addField('acq_sku', 'sku');
    $query->join('acq_sku__field_configured_skus', 'child_sku', 'acq_sku.id = child_sku.entity_id');
    $query->condition('child_sku.field_configured_skus_value', array_values($simple_skus), 'IN');
    return $query->execute()->fetchAllKeyed(0);
  }

  /**
   * Utility function to get parent node of the sku.
   *
   * @param mixed $sku
   *   SKU name or full sku object.
   * @param string $langcode
   *   Language code.
   *
   * @return object
   *   Loaded node object.
   */
  public function getDisplayNode($sku, $langcode = '') {
    $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku, $langcode);

    if (empty($sku_entity)) {
      $this->logger->warning('SKU entity not found for @sku with langcode: @langcode. (@function)', [
        '@sku' => $sku,
        '@langcode' => $langcode,
        '@function' => 'SkuManager::getDisplayNode()',
      ]);

      return NULL;
    }

    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $sku_entity->getPluginInstance();

    $node = $plugin->getDisplayNode($sku_entity);

    if (!($node instanceof NodeInterface)) {
      $this->logger->warning('SKU entity available but no display node found for @sku with langcode: @langcode. SkuManager::getDisplayNode().', [
        '@sku' => $sku_entity->getSku(),
      ]);

      return NULL;
    }

    return $node;
  }

  /**
   * Utility function to get parent SKU for a configurable child sku.
   *
   * @param mixed $sku
   *   SKU text or full entity object.
   * @param string $langcode
   *   Language code.
   *
   * @return \Drupal\acq_sku\Entity\SKU
   *   Loaded SKU entity.
   */
  public function getParentSkuBySku($sku, $langcode = '') {
    $sku_entity = $sku instanceof SKU ? $sku : SKU::loadFromSku($sku, $langcode);

    // Additional check, can be removed post go UAT.
    if (empty($sku_entity)) {
      return NULL;
    }

    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $sku_entity->getPluginInstance();

    return $plugin->getParentSku($sku_entity);
  }

  /**
   * Utility function to get linked SKUs.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU full entity object.
   * @param string $type
   *   Type of Linked SKUs to return related/upsell.
   *
   * @return array
   *   Linked SKUs for requested type.
   */
  public function getLinkedSkus(SKU $sku, $type) {
    $linked_skus = $this->linkedSkus->getLinkedSKus($sku);

    $linked_skus_requested = [];

    if (isset($linked_skus[$type]) && !empty($linked_skus[$type])) {
      $linked_skus_requested = $linked_skus[$type];
    }

    try {
      if ($linked_skus_from_product = $sku->get($type)->getValue()) {
        $linked_skus_from_product = array_column($linked_skus_from_product, 'value');
        $linked_skus_requested = array_merge($linked_skus_requested, $linked_skus_from_product);
      }
    }
    catch (\Exception $e) {
      // Do nothing.
    }

    return $linked_skus_requested;
  }

  /**
   * Utility function to get linked SKUs for current and first child too.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU full entity object.
   * @param string $type
   *   Type of Linked SKUs to return related/upsell.
   *
   * @return array
   *   Linked SKUs for requested type.
   */
  public function getLinkedSkusWithFirstChild(SKU $sku, $type) {
    // First always get the parent if available.
    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $sku->getPluginInstance();
    $parent = $plugin->getParentSku($sku);
    $sku_entity = $parent instanceof SKU ? $parent : $sku;

    $linked_skus_requested = $this->getLinkedSkus($sku_entity, $type);

    $first_child = $this->getChildSkus($sku_entity, TRUE);

    if ($first_child) {
      $child_linked_skus_requested = $this->getLinkedSkus($first_child, $type);
      $linked_skus_requested = array_merge($linked_skus_requested, $child_linked_skus_requested);
    }

    return $linked_skus_requested;
  }

  /**
   * Helper function to filter skus by stock status.
   *
   * @param array $skus
   *   Array containing skus as string.
   *
   * @return array
   *   Filtered skus.
   */
  public function filterRelatedSkus(array $skus) {
    $related_items_size = $this->configFactory->get('alshaya_acm_product.settings')->get('related_items_size');
    $stock_mode = $this->configFactory->get('acq_sku.settings')->get('stock_mode');

    $related = [];

    foreach ($skus as $sku) {
      try {
        $sku_entity = SKU::loadFromSku($sku);

        if (empty($sku_entity)) {
          continue;
        }

        /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
        $plugin = $sku_entity->getPluginInstance();

        $node = $plugin->getDisplayNode($sku_entity);

        if (empty($node)) {
          continue;
        }

        // No stock check for related items in pull mode.
        if ($stock_mode == 'pull') {
          $related[] = $node->id();
        }
        elseif (alshaya_acm_get_stock_from_sku($sku_entity)) {
          $related[] = $node->id();
        }
      }
      catch (\Exception $e) {
        // Do nothing.
      }

      if (count($related) >= $related_items_size) {
        break;
      }
    }

    return $related;
  }

  /**
   * Helper function to fetch attributes for PDP.
   *
   * Use configurable SKU for configurable attributes & simple SKUs as source
   * for non-configurable attribtues.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity for which the attribute data needs to be pulled.
   * @param string $attribute_machine_name
   *   Attribute field name.
   * @param string $search_direction
   *   Direction in which to look for fallback while fetching the attribute.
   * @param bool $multivalued
   *   Boolean value indicating if the field we looking for is multi-valued.
   *
   * @return array|string
   *   Attribute value.
   */
  public function fetchProductAttribute(SKU $sku, $attribute_machine_name, $search_direction, $multivalued = FALSE) {
    if (($search_direction == 'children') &&
      ($sku->getType() == 'configurable') &&
      ($child_sku = $this->getChildSkus($sku, TRUE))) {
      $sku = $child_sku;
    }
    elseif (($search_direction == 'parent') &&
      ($parent_sku = alshaya_acm_product_get_parent_sku_by_sku($sku))) {
      $sku = $parent_sku;
    }

    if ($sku instanceof SKU) {
      if (($multivalued) &&
        (!empty($first_index = $sku->get($attribute_machine_name)->first())) &&
        (!empty($attribute_value = $first_index->getString()))) {
        return $attribute_value;
      }
      elseif (!empty($attribute_value = $sku->get($attribute_machine_name)->getString())) {
        return $attribute_value;
      }
    }

    return '';
  }

  /**
   * Lighter function to fetch Children SKU text.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku_entity
   *   Configurable SKU for which the child SKUs need to be fetched.
   * @param bool $first_only
   *   Flag to indicate we need to fetch only the first item.
   *
   * @return mixed
   *   Array of SKU texts of single SKU text if first only is asked.
   */
  public function getChildrenSkuIds(SKU $sku_entity, $first_only = FALSE) {
    $child_skus = [];

    if ($sku_entity->getType() == 'configurable') {
      $query = $this->connection->select('acq_sku__field_configured_skus', 'asfcs');
      $query->fields('asfcs', ['field_configured_skus_value']);
      $query->join('acq_sku_field_data', 'asfd', 'asfd.sku=asfcs.field_configured_skus_value');
      $query->condition('asfcs.entity_id', $sku_entity->id());
      $query->distinct();

      if ($first_only) {
        $query->range(0, 1);
      }

      $result = $query->execute();

      while ($row = $result->fetchAssoc()) {
        if ($first_only) {
          return $row['field_configured_skus_value'];
        }
        $child_skus[] = $row['field_configured_skus_value'];
      }
    }

    return array_filter($child_skus);
  }

  /**
   * Helper function to fetch SKU's property value.
   *
   * @param string $sku
   *   SKU code for the product.
   * @param array $properties
   *   Property name that needs to be fetched.
   *
   * @return \stdClass
   *   Result object keyed with the list of properties.
   */
  public function getSkuPropertyValue($sku, array $properties) {
    $result = $this->connection->select('acq_sku_field_data', 'asfd')
      ->fields('asfd', $properties)
      ->condition('asfd.sku', $sku)
      ->condition('asfd.langcode', $this->languageManager->getCurrentLanguage()->getId())
      ->range(0, 1)
      ->execute()->fetchAll();

    if (!empty($result)) {
      return array_shift($result);
    }

    return NULL;
  }

  /**
   * Get possible combinations for a configurable SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   *
   * @return array
   *   Calculated combinations array.
   */
  public function getConfigurableCombinations(SKU $sku) {
    if ($sku->bundle() != 'configurable') {
      return [];
    }

    if ($cache = $this->getProductCachedData($sku, 'combinations')) {
      return $cache;
    }

    /** @var \Drupal\acq_sku\Plugin\AcquiaCommerce\SKUType\Configurable $plugin */
    $plugin = $sku->getPluginInstance();
    $tree = $plugin->deriveProductTree($sku);

    $configurable_codes = array_keys($tree['configurables']);
    $all_combinations = AlshayaArrayUtils::getAllCombinations($configurable_codes);

    $combinations = [];

    // Prepare array to get all combinations available grouped by SKU.
    foreach ($tree['products'] ?? [] as $sku_code => $sku_entity) {
      if (!($sku_entity instanceof SKU)) {
        continue;
      }

      // Disable OOS combinations too.
      if (!alshaya_acm_get_stock_from_sku($sku_entity)) {
        continue;
      }

      $attributes = $sku_entity->get('attributes')->getValue();
      $attributes = array_column($attributes, 'value', 'key');
      foreach ($configurable_codes as $code) {
        $value = $attributes[$code] ?? '';

        if (empty($value)) {
          continue;
        }

        $combinations['by_sku'][$sku_code][$code] = $value;
        $combinations['attribute_sku'][$code][$value][] = $sku_code;
      }
    }

    // Prepare combinations array grouped by attributes to check later which
    // combination is possible using isset().
    $combinations['by_attribute'] = [];

    foreach ($combinations['by_sku'] ?? [] as $combination) {
      foreach ($all_combinations as $possible_combination) {
        $combination_string = '';
        foreach ($possible_combination as $code) {
          $combination_string .= $code . '|' . $combination[$code] . '||';
          $combinations['by_attribute'][$combination_string] = 1;
        }
        $combinations['by_attribute'][$combination_string] = 1;
      }
    }

    $this->setProductCachedData($sku, 'combinations', $combinations);

    return $combinations;
  }

  /**
   * Disable configurable options not available in the system.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   * @param array $configurables
   *   Configurables in the form.
   * @param array $tree
   *   Configurable tree from form state.
   * @param array $selected
   *   Selected values.
   */
  public function disableUnavailableOptions(SKU $sku, array &$configurables, array $tree, array &$selected = []) {
    $configurable_codes = array_keys($tree['configurables']);

    $combinations = $this->getConfigurableCombinations($sku);

    // Cleanup current selection.
    $selected = array_filter($selected);

    foreach ($selected as $code => $value) {
      // Check for selected values in current options.
      if (!isset($configurables[$code]['#options'][$value])) {
        unset($selected[$code]);
        continue;
      }
    }

    // Remove all options which are not available at all.
    foreach ($configurable_codes as $index => $code) {
      foreach ($configurables[$code]['#options'] as $key => $value) {
        if (empty($key) || isset($combinations['attribute_sku'][$code][$key])) {
          continue;
        }

        unset($configurables[$code]['#options'][$key]);
      }
    }

    $combination_key = '';
    foreach ($selected as $code => $value) {
      $index = array_search($code, $configurable_codes);
      if ($index !== FALSE) {
        unset($configurable_codes[$index]);
      }

      $combination_key .= $code . '|' . $value . '||';
      foreach ($configurable_codes as $configurable_code) {
        if (!isset($configurables[$configurable_code]) || empty($configurables[$configurable_code]['#options'])) {
          continue;
        }

        foreach ($configurables[$configurable_code]['#options'] as $key => $value) {
          $check_key1 = $combination_key . $configurable_code . '|' . $key . '||';
          $check_key2 = $configurable_code . '|' . $key . '||' . $combination_key;

          if (isset($combinations['by_attribute'][$check_key1])
            || isset($combinations['by_attribute'][$check_key2])) {
            continue;
          }

          if (isset($selected[$configurable_code]) && $selected[$configurable_code] == $key) {
            unset($selected[$configurable_code]);
            unset($configurables[$configurable_code]['#options_attributes'][$key]['selected']);
          }

          $configurables[$configurable_code]['#options_attributes'][$key]['disabled'] = 'disabled';
        }
      }
    }
  }

  /**
   * Get data from Cache for a product.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param string $key
   *   Key of the data to get from cache.
   *
   * @return array|null
   *   Data if found or null.
   */
  public function getProductCachedData(SKU $sku, $key = 'price') {
    $static = &drupal_static('alshaya_product_cached_data', []);

    $cid = $this->getProductCachedId($sku);

    // Try once in static cache.
    if (isset($static[$cid], $static[$cid][$key])) {
      return $static[$cid][$key];
    }

    // Load from cache.
    $cache = $this->productCache->get($cid);

    if (isset($cache->data, $cache->data[$key])) {
      $static[$cid][$key] = $cache->data[$key];
      return $cache->data[$key];
    }

    return NULL;
  }

  /**
   * Set data into Cache for a product.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU Entity.
   * @param string $key
   *   Key of the data to get from cache.
   * @param mixed $value
   *   Value to set for the provided key.
   */
  public function setProductCachedData(SKU $sku, $key, $value) {
    $cid = $this->getProductCachedId($sku);
    $cache = $this->productCache->get($cid);
    $data = $cache->data ?? [];
    $data[$key] = $value;
    $this->productCache->set($cid, $data);

    // Update value in static cache too.
    $static = &drupal_static('alshaya_product_cached_data', []);
    if (isset($static[$cid], $static[$cid][$key])) {
      $static[$cid][$key] = $value;
    }
  }

  /**
   * Get cache id for particular sku and language.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   *
   * @return string
   *   Cache key.
   */
  public function getProductCachedId(SKU $sku) {
    return 'alshaya_product:' . $sku->language()->getId() . ':' . $sku->getSku();
  }

  /**
   * Clear configurable product cache for particular SKU.
   *
   * @param \Drupal\acq_sku\Entity\SKU $sku
   *   SKU entity.
   */
  public function clearProductCachedData(SKU $sku) {
    $cid = $this->getProductCachedId($sku);
    $this->productCache->delete($cid);
  }

  /**
   * Get first child based on brand conditions if defined or from default.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU entity.
   * @param string $root_attribute_code
   *   Root attribute code.
   * @param array $selected
   *   Current selection.
   * @param array|null $root_attribute_form_item
   *   Form item containing options and disabled attributes.
   *
   * @return \Drupal\acq_sku\Entity\SKU
   *   First child SKU entity.
   */
  public function getFirstChildForSku(SKUInterface $sku, $root_attribute_code, array $selected = [], $root_attribute_form_item = []) {
    // Get the first child from user selected value if available.
    if (isset($selected[$root_attribute_code])) {
      $first_child = $this->getChildSkuFromAttribute($sku, $root_attribute_code, $selected[$root_attribute_code]);

      if ($first_child instanceof SKU) {
        return $first_child;
      }
    }

    // Select first child based on value provided in query params.
    $sku_id = (int) $this->currentRequest->query->get('selected');

    // Give preference to sku id passed via query params.
    if ($sku_id) {
      $first_child = SKU::load($sku_id);

      if ($first_child instanceof SKUInterface && alshaya_acm_get_stock_from_sku($first_child)) {
        // We do it again to get current translation.
        // We expect no performance impact as all the skus are already loaded
        // multiple times in the request.
        $first_child = SKU::loadFromSku($first_child->getSku());
        return $first_child;
      }
    }

    // Default use-case: User landing on PDP from PLP/Search/directly.
    // Get the first child from sorted options of root attribute.
    if ($root_attribute_form_item) {
      foreach ($root_attribute_form_item['#options'] as $key => $value) {
        if (isset($root_attribute_form_item['#options_attributes'][$key]['disabled'])) {
          continue;
        }

        $root_attribute_first_value = $key;
        break;
      }

      if (isset($root_attribute_first_value)) {
        return $this->getChildSkuFromAttribute(
          $sku,
          $root_attribute_code,
          $root_attribute_first_value
        );
      }
    }

    // Fallback.
    return $this->getChildSkus($sku, TRUE);
  }

  /**
   * Get all the swatch images with sku text as key.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   Parent SKU.
   * @param string $attribute_code
   *   Attribute code used for swatches.
   *
   * @return array
   *   Swatches array.
   */
  public function getSwatches(SKUInterface $sku, $attribute_code = 'color') {
    $swatches = $this->getProductCachedData($sku, 'swatches');

    // We may have nothing for an SKU, we should not keep processing for it.
    // If value is not set, function returns NULL above so we check for array.
    if (is_array($swatches)) {
      return $swatches;
    }

    $swatches = [];
    $duplicates = [];
    $children = $this->getChildSkus($sku);

    foreach ($children as $child) {
      $value = $child->get('attr_' . $attribute_code)->getString();

      if (empty($value) || isset($duplicates[$value])) {
        continue;
      }

      // Do not show OOS swatches.
      if (!alshaya_acm_get_stock_from_sku($child)) {
        continue;
      }

      $swatch_item = $child->getSwatchImage();

      if (empty($swatch_item) || !($swatch_item['file'] instanceof FileInterface)) {
        continue;
      }

      $duplicates[$value] = 1;
      $swatches[$child->id()] = $swatch_item['file']->url();
    }

    $this->setProductCachedData($sku, 'swatches', $swatches);

    return $swatches;
  }

}
