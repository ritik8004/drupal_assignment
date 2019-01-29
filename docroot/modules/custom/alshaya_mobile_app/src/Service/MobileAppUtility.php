<?php

namespace Drupal\alshaya_mobile_app\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\node\NodeInterface;
use Drupal\file\FileInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\ResourceResponse;
use Drupal\acq_commerce\Conductor\APIWrapper;

/**
 * Utilty Class.
 */
class MobileAppUtility {

  use StringTranslationTrait;

  /**
   * Prefix used for the endpoint.
   */
  const ENDPOINT_PREFIX = '/rest/v1/';

  /**
   * Array of term urls for dependencies.
   *
   * @var array
   */
  protected $termUrls = [];

  /**
   * Array of term tags.
   *
   * @var array
   */
  protected $termTags = [];

  /**
   * Cache Backend service for alshaya.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * SKU manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * SKU images manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $skuImagesManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current language.
   *
   * @var string
   */
  protected $currentLanguage;

  /**
   * File storage object.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The acq_commerce.currency config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $currencyConfig;

  /**
   * API Wrapper object.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * Utility constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $sku_images_manager
   *   SKU images manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   The ApiWrapper object.
   */
  public function __construct(
    CacheBackendInterface $cache,
    LanguageManagerInterface $language_manager,
    RequestStack $request_stack,
    AliasManagerInterface $alias_manager,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository,
    SkuManager $sku_manager,
    SkuImagesManager $sku_images_manager,
    ModuleHandlerInterface $module_handler,
    ProductCategoryTreeInterface $product_category_tree,
    ConfigFactoryInterface $config_factory,
    APIWrapper $api_wrapper
  ) {
    $this->cache = $cache;
    $this->languageManager = $language_manager;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->aliasManager = $alias_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->skuManager = $sku_manager;
    $this->skuImagesManager = $sku_images_manager;
    $this->moduleHandler = $module_handler;
    $this->productCategoryTree = $product_category_tree;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->currentLanguage = $this->languageManager->getCurrentLanguage()->getId();
    $this->currencyConfig = $config_factory->get('acq_commerce.currency');
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * Return the current language id.
   *
   * @return string
   *   Return the current language id.
   */
  public function currentLanguage() {
    return $this->currentLanguage;
  }

  /**
   * Get Deep link based on give object.
   *
   * @param object $object
   *   Object of node or term or query containing node/term data.
   * @param string $type
   *   (optional) String containing info about data incase of query object.
   *
   * @return string
   *   Return deeplink url.
   */
  public function getDeepLink($object, $type = '') {
    $return = '';

    if ($object instanceof TermInterface) {
      switch ($object->bundle()) {
        case 'acq_product_category':
          $department_node = alshaya_advanced_page_is_department_page($object->id());
          if ($department_node) {
            $return = $this->pageDeepLink($department_node, 'advanced');
          }
          else {
            $return = 'category/' . $object->id() . '/product-list';
          }
          break;
      }
    }
    elseif (is_object($object) && !empty($object->tid)) {
      // In case of categories resource, we not getting full object.
      // If category is department page node.
      $department_node = alshaya_advanced_page_is_department_page($object->tid);
      if ($department_node) {
        $return = $this->pageDeepLink($department_node, 'advanced');
      }
      else {
        $return = 'category/' . $object->tid . '/product-list';
      }
    }
    elseif ($object instanceof NodeInterface) {
      switch ($object->bundle()) {
        case 'acq_product':
          $sku = $this->skuManager->getSkuForNode($object);
          $return = 'product/' . $sku;
          break;

        case 'acq_promotion':
          $return = 'promotion/' . $object->id() . '/product-list';
          break;

        case 'static_html':
          $return = $this->pageDeepLink($object->id(), 'simple');
          break;

        case 'advanced_page':
          $return = $this->pageDeepLink($object->id(), 'advanced');
          break;
      }
    }
    elseif ($object instanceof SKUInterface) {
      $return = 'product/' . $object->getSku();
    }

    return self::ENDPOINT_PREFIX . $return;
  }

  /**
   * Return simple page or advanced page deeplink.
   *
   * @param int $nid
   *   The node id.
   * @param string $type
   *   (optional) The type of page default is advanced. (simple or advanced)
   *
   * @return string
   *   Return string of deeplink.
   */
  protected function pageDeepLink($nid, $type = 'advanced') {
    return "page/{$type}?url=" .
    ltrim(
      $this->aliasManager->getAliasByPath(
        '/node/' . $nid,
        $this->currentLanguage
      ),
      '/'
    );
  }

  /**
   * Get Deep link based on given url object.
   *
   * @param \Drupal\Core\Url $url
   *   The url Object.
   *
   * @return string
   *   Return deeplink url.
   */
  public function getDeepLinkFromUrl(Url $url) {
    if (!$url->isRouted()) {
      return $url->isExternal()
      ? FALSE
      : self::ENDPOINT_PREFIX
        . 'deeplink?url='
        . $url->toString(TRUE)->getGeneratedUrl();
    }

    $params = $url->getRouteParameters();
    if (empty($params)) {
      return '';
    }

    if (isset($params['taxonomy_term'])) {
      $entity = $this->entityTypeManager->getStorage('taxonomy_term')->load($params['taxonomy_term']);
    }
    elseif (isset($params['node'])) {
      $entity = $this->entityTypeManager->getStorage('node')->load($params['node']);
    }
    return $entity instanceof ContentEntityInterface ? $this->getDeepLink($entity) : '';
  }

  /**
   * Get the alias language.
   *
   * @param string $alias
   *   The alias string.
   *
   * @return string
   *   Return the string of language code.
   */
  protected function getAliasLang($alias) {
    $alias_lang = NULL;
    if ($this->currentLanguage == 'ar' && !preg_match("/\p{Arabic}/u", $alias)) {
      $alias_lang = $this->languageManager->getDefaultLanguage()->getId();
    }
    elseif ($this->currentLanguage == 'en' && preg_match("/\p{Arabic}/u", $alias)) {
      // Get the correct language, based on user input.
      $languages = $this->languageManager->getLanguages();
      if (count($languages) > 1 && array_key_exists('ar', $languages)) {
        $alias_lang = $languages['ar']->getId();
      }
    }
    return $alias_lang;
  }

  /**
   * Get node entity object from given alias.
   *
   * @param string $alias
   *   The alias to use to get entity.
   * @param string $bundle
   *   (optional) The bundle to validate entity against.
   *
   * @return \Drupal\node\NodeInterface|bool
   *   The node object, or FALSE if nothing found.
   */
  public function getNodeFromAlias($alias, $bundle = '') {
    // Get the internal path of given alias and get route parameters.
    $internal_path = $this->aliasManager->getPathByAlias(
      '/' . $alias,
      $this->getAliasLang($alias)
    );

    // Get the parameters, to get node id from internal path.
    $params = Url::fromUri("internal:" . $internal_path)->getRouteParameters();
    if (empty($params)) {
      return FALSE;
    };

    if (!empty($params['taxonomy_term'])) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($params['taxonomy_term']);

      if ($term instanceof TermInterface
        && $term->bundle() == 'acq_product_category'
        && $department_nid = alshaya_advanced_page_is_department_page($term->id())
      ) {
        $node = $this->entityTypeManager->getStorage('node')->load($department_nid);
      }
    }
    elseif (!empty($params['node'])) {
      $node = $this->entityTypeManager->getStorage('node')->load($params['node']);
    }

    if (!isset($node) || !$node instanceof NodeInterface) {
      return FALSE;
    }

    if (!empty($bundle) && $node->bundle() !== $bundle) {
      return FALSE;
    }

    return $this->getEntityTranslation($node, $this->currentLanguage);
  }

  /**
   * Prepare multiple images array for given entity on given fieldname.
   *
   * @param object $entity
   *   The entity object.
   * @param string $field_name
   *   The field name from which it needs to create images array.
   * @param string $label
   *   (optional) The label.
   * @param string $type
   *   (optional) The type of the field.
   *
   * @return array
   *   The array containing information of images if image cardinality
   *   is greater then 1, otherwise return the first image array.
   */
  public function getImages($entity, $field_name, $label = NULL, $type = NULL) {
    if (!$entity->hasField($field_name)) {
      return [];
    }

    $images = [];
    if (!empty($entity->get($field_name)->getValue())) {
      foreach ($entity->get($field_name)->getValue() as $key => $value) {
        if (($file = $entity->get($field_name)->get($key)->entity) && $file instanceof FileInterface) {
          $images[] = [
            'url' => file_create_url($file->getFileUri()),
            'width' => (int) $value['width'],
            'height' => (int) $value['height'],
          ];
        }
      }
    }
    // Check cardinality of given field.
    if ($entity->get($field_name)->getFieldDefinition()->getFieldStorageDefinition()->isMultiple()) {
      return $images;
    }

    return !empty($images) ? $images[0] : [];
  }

  /**
   * Helper function to throw an error.
   *
   * @param string $message
   *   (Optional) status message when necessary.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function throwException($message = NULL) {
    throw new NotFoundHttpException($message ?? $this->t("page not found"));
  }

  /**
   * Helper method to return a response.
   *
   * @param string $message
   *   (Optional) status message when necessary.
   * @param bool $status
   *   (Optional) True if you want to send success => TRUE, else FALSE.
   *
   * @return \Drupal\rest\ResourceResponse
   *   HTTP Response.
   */
  public function sendStatusResponse(string $message = '', $status = FALSE) {
    // If status is false, throw a 404 exception.
    if (!$status) {
      return $this->throwException($message);
    }

    $response['success'] = (bool) ($status);
    if ($message) {
      $response['message'] = $message;
    }

    return (new ResourceResponse($response));
  }

  /**
   * Get the link parameters for link field type.
   *
   * @param object $entity
   *   The entity object.
   * @param string $field
   *   The link field name.
   * @param string $label
   *   (optional) The label.
   * @param string $type
   *   (optional) The type of the field.
   *
   * @return array
   *   Return the associative array with url and deeplink.
   */
  protected function getFieldLink($entity, string $field, $label = 'url', $type = NULL) {
    if (!$entity->hasField($field)) {
      return [];
    }

    if (empty($entity->get($field)->first())) {
      return [];
    }
    // Convert field link value.
    $url = $entity->get($field)->first()->getUrl();
    $url_string = $url->toString(TRUE);

    $return = [
      $label => $url_string->getGeneratedUrl(),
    ];

    if ($deeplink = $this->getDeepLinkFromUrl($url)) {
      $return['deeplink'] = $deeplink;
    }
    return $return;
  }

  /**
   * Return the term objects.
   *
   * @param string $langcode
   *   (optional) The language code.
   * @param int $parent
   *   (optional) The parent term id.
   * @param bool $child
   *   (optional) True to return child false otherwise.
   * @param bool $mobile_only
   *   (optional) True to mobile only links.
   *
   * @return array
   *   The array containing terms related data.
   */
  public function getAllCategories(string $langcode = '', $parent = 0, $child = TRUE, $mobile_only = FALSE) {
    $data = [];
    if (empty($langcode)) {
      $langcode = $this->currentLanguage;
    }

    $terms = $this->productCategoryTree->allChildTerms($langcode, $parent, FALSE, $mobile_only);
    foreach ($terms as $term) {
      $term_url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid])->toString(TRUE);
      $this->termUrls[] = $term_url;
      $this->termTags[] = "term:{$term->tid}";

      $record = [
        'id' => (int) $term->tid,
        'name' => $term->name,
        'description'  => !empty($term->description__value) ? $term->description__value : '',
        'path' => $term_url->getGeneratedUrl(),
        'deeplink' => $this->getDeepLink($term),
        'include_in_menu' => (bool) $term->include_in_menu,
      ];

      if (is_object($file = $this->productCategoryTree->getMobileBanner($term->tid, $langcode))
        && !empty($file->field_promotion_banner_mobile_target_id)
      ) {
        $image = $this->fileStorage->load($file->field_promotion_banner_mobile_target_id);
        $record['banner'] = [
          'url' => file_create_url($image->getFileUri()),
          'width' => (int) $file->field_promotion_banner_mobile_width,
          'height' => (int) $file->field_promotion_banner_mobile_height,
        ];
      }

      if ($child) {
        $record['child'] = $this->getAllCategories($langcode, $term->tid);
      }

      $data[] = $record;
    }
    return $data;
  }

  /**
   * Return term tags to cache.
   *
   * @return array
   *   Return Term urls array.
   */
  public function cacheableTermTags() {
    return $this->termTags;
  }

  /**
   * Return term urls to cache.
   *
   * @return array
   *   Return Term urls array.
   */
  public function cacheableTermUrls() {
    return $this->termUrls;
  }

  /**
   * Get the boolean field value.
   *
   * @param object $entity
   *   The entity object.
   * @param string $field
   *   The link field name.
   * @param string $label
   *   (optional) The label.
   * @param string $type
   *   (optional) The type of the field.
   *
   * @return array|string
   *   Return the associative array with label if label variable is not
   *   empty else return only value.
   */
  protected function getFieldBoolean($entity, string $field, $label = '', $type = NULL) {
    if (!$entity->hasField($field)) {
      return empty($label) ? '' : [];
    }
    $value = (bool) $entity->get($field)->first()->getValue()['value'];
    return empty($label) ? $value : [$label => $value];
  }

  /**
   * Wrapper function get promotions.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   *
   * @return array
   *   Promotions.
   */
  public function getPromotions(SKUInterface $sku) {
    $promotions = [];
    $promotions_data = $this->skuManager->getPromotionsFromSkuId($sku, '', ['cart'], 'full', FALSE);
    foreach ($promotions_data as $nid => $promotion) {
      $promotion_node = $this->entityTypeManager->getStorage('node')->load($nid);
      $promotions[] = [
        'text' => $promotion['text'],
        'deeplink' => $this->getDeepLink($promotion_node, 'promotion'),
      ];
    }
    return $promotions;
  }

  /**
   * Wrapper function get labels and make the urls absolute.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context.
   *
   * @return array
   *   Labels data.
   */
  public function getLabels(SKUInterface $sku, string $context): array {
    $labels = $this->skuManager->getLabels($sku, $context);

    if (empty($labels)) {
      return [];
    }

    foreach ($labels as &$label) {
      $doc = new \DOMDocument();
      $doc->loadHTML((string) $label['image']);
      $xpath = new \DOMXPath($doc);
      $label['image'] = Url::fromUserInput($xpath->evaluate("string(//img/@src)"), ['absolute' => TRUE])->toString();
    }

    return $labels;
  }

  /**
   * Wrapper function to get media items for an SKU.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $context
   *   Context.
   *
   * @return array
   *   Media Items.
   */
  public function getMedia(SKUInterface $sku, string $context): array {
    $media = $this->skuImagesManager->getProductMedia($sku, $context);

    // Keep schema consistent.
    if (empty($media)) {
      return ['images' => [], 'videos' => []];
    }

    if (!isset($media['images_with_type'])) {
      $media['images_with_type'] = array_map(function ($image) {
        return [
          'url' => $image,
          'image_type' => 'image',
        ];
      }, array_values($media['images']));
    }

    return [
      'images' => $media['images_with_type'],
      'videos' => array_values($media['videos']),
    ];
  }

  /**
   * Get light product data using give nid.
   *
   * @param int $nid
   *   Node id.
   * @param string $langcode
   *   Language of node.
   *
   * @return array
   *   Product data.
   */
  public function getLightProductFromNid(int $nid, string $langcode = 'en') {
    $node = $this->entityTypeManager->getStorage('node')->load($nid);

    if (!$node instanceof NodeInterface) {
      return [];
    }
    // Get translated node.
    $node = $this->entityRepository->getTranslationFromContext($node, $langcode);
    $color = $node->get('field_product_color')->getString();

    // Get SKU attached with node.
    $sku = $this->skuManager->getSkuForNode($node);
    $sku_entity = SKU::loadFromSku($sku);

    if ($sku_entity instanceof SKU) {
      return $this->getLightProduct($sku_entity, $color);
    }
    return [];
  }

  /**
   * Get Light Product.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string|null $color
   *   Color value.
   *
   * @return array
   *   Light Product.
   */
  public function getLightProduct(SKUInterface $sku, $color = NULL): array {
    $node = $this->skuManager->getDisplayNode($sku);
    if (!($node instanceof NodeInterface)) {
      return [];
    }

    // Get the prices.
    $prices = $this->skuManager->getMinPrices($sku);

    // Get the promotion data.
    $promotions = $this->getPromotions($sku);

    // Get promo labels.
    $promo_label = $this->skuManager->getDiscountedPriceMarkup($prices['price'], $prices['final_price']);
    if ($promo_label) {
      $promotions[] = [
        'text' => $promo_label,
      ];
    }

    // Get label for the SKU.
    $labels = $this->getLabels($sku, 'plp');

    // Get media (images/video) for the SKU.
    $sku_for_gallery = $this->skuImagesManager->getSkuForGalleryWithColor($sku, $color) ?? $sku;
    $images = $this->getMedia($sku_for_gallery, 'search');

    $link = $node->toUrl('canonical', ['absolute' => TRUE])
      ->toString(TRUE)
      ->getGeneratedUrl();

    $data = [
      'id' => (int) $sku->id(),
      'title' => $sku->label(),
      'sku' => $sku->getSku(),
      'deeplink' => $this->getDeepLink($sku),
      'link' => $link,
      'original_price' => $this->formatPriceDisplay($prices['price']),
      'final_price' => $this->formatPriceDisplay($prices['final_price']),
      'in_stock' => (bool) alshaya_acm_get_stock_from_sku($sku),
      'promo' => $promotions,
      'medias' => $images,
      'labels' => $labels,
      'color' => NULL,
    ];

    if ($color) {
      // Keep data type consistent here.
      $data['color'] = (string) $color;
    }

    // Allow other modules to alter light product data.
    $this->moduleHandler->alter('alshaya_mobile_app_light_product_data', $sku, $data);

    return $data;
  }

  /**
   * Wrapper function get fully loaded linked skus.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku
   *   SKU Entity.
   * @param string $linked_type
   *   Linked type.
   *
   * @return array
   *   Linked SKUs.
   */
  public function getLinkedSkus(SKUInterface $sku, string $linked_type) {
    $return = [];
    $linkedSkus = $this->skuManager->getLinkedSkus($sku, $linked_type);
    $linkedSkus = $this->skuManager->filterRelatedSkus($linkedSkus);

    foreach (array_keys($linkedSkus) as $linkedSku) {
      $linkedSkuEntity = SKU::loadFromSku($linkedSku);
      $return[] = $this->getLightProduct($linkedSkuEntity);
    }

    return $return;
  }

  /**
   * Get translation of given entity for given langcode.
   *
   * @param object $entity
   *   The entity object.
   * @param string $langcode
   *   The language code.
   *
   * @return object
   *   Return entity object with translation if exists otherwise as is.
   */
  public function getEntityTranslation($entity, $langcode) {
    if (($entity instanceof ContentEntityInterface
      || $entity instanceof ConfigEntityInterface)
      && $entity->language()->getId() != $langcode
      && $entity->hasTranslation($langcode)
    ) {
      $entity = $entity->getTranslation($langcode);
    }
    return $entity;
  }

  /**
   * Return formatted price.
   *
   * @param float $price
   *   The price.
   *
   * @return string
   *   Return string price upto configured decimal points.
   */
  public function formatPriceDisplay(float $price): string {
    return (string) number_format($price, $this->currencyConfig->get('decimal_points'));
  }

  /**
   * Convert relative url img tag in string with absolute url.
   *
   * @param string $string
   *   The string containing html tags.
   *
   * @return string
   *   Return the complete url string with domain.
   */
  public function convertRelativeUrlsToAbsolute(string $string): string {
    global $base_url;
    return preg_replace('#(src)="([^:"]*)(?:")#', '$1="' . $base_url . '$2"', $string);
  }

  /**
   * Get user info from mdc and create it.
   *
   * @param string $email
   *   The user mail string.
   * @param bool $block
   *   (Optional) True to block user after created, otherwise false.
   *
   * @return \Drupal\user\Entity\User|false
   *   Return user object or false.
   */
  public function createUserFromCommerce(string $email, $block = TRUE) {
    // Try to get user from mdc and create new user account.
    try {
      /** @var \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper */
      $customer = $this->apiWrapper->getCustomer($email);

      if (!empty($customer)) {
        $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.utility');
        /** @var \Drupal\user\Entity\User $user */
        $user = alshaya_acm_customer_create_drupal_user($customer);
        if ($block) {
          $user->block();
          $user->save();
        }
      }
    }
    catch (\Exception $e) {
      // Do nothing except for downtime exception, let default validation
      // handle the error messages.
      if (acq_commerce_is_exception_api_down_exception($e)) {
        $this->logger->error($e->getMessage());
      }
    }

    return $user;
  }

}
