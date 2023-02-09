<?php

namespace Drupal\alshaya_mobile_app\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product_category\Service\ProductCategoryPage;
use Drupal\alshaya_acm_product\Service\SkuInfoHelper;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\node\NodeInterface;
use Drupal\file\FileInterface;
use Drupal\views\Views;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rest\ResourceResponse;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\redirect\RedirectRepository;
use Drupal\Core\Database\Connection;
use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Site\Settings;

/**
 * Mobile App Utility Class.
 */
class MobileAppUtility {

  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * Prefix used for the endpoint.
   */
  public const ENDPOINT_PREFIX = '/rest/v1/';

  /**
   * Array of term urls for dependencies.
   *
   * @var array
   */
  protected $termUrls = [];

  /**
   * Array of homepage cache tags.
   *
   * @var array
   */
  protected $homePageCache = [];

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
   * @var \Drupal\path_alias\AliasManagerInterface
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
   * The config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $configFactory;

  /**
   * API Wrapper object.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Listing config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $listingConfig;

  /**
   * Redirect repository.
   *
   * @var \Drupal\redirect\RedirectRepository
   */
  protected $redirectRepository;

  /**
   * Contains array of redirects urls.
   *
   * @var array
   *
   * @see self::getDeepLinkFromUrl()
   */
  protected $redirects = [];

  /**
   * Sku info helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuInfoHelper
   */
  protected $skuInfoHelper;

  /**
   * Database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The super category manager service.
   *
   * @var \Drupal\alshaya_super_category\AlshayaSuperCategoryManager
   */
  protected $superCategoryManager;

  /**
   * The Path Validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Product Category Page service.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage
   */
  protected $productCategoryPage;

  /**
   * Base url of current site.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * MobileAppUtility constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
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
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   The ApiWrapper object.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\redirect\RedirectRepository $redirect_repsitory
   *   Redirect repository.
   * @param \Drupal\alshaya_acm_product\Service\SkuInfoHelper $sku_info_helper
   *   Sku info helper object.
   * @param \Drupal\Core\Database\Connection $database
   *   Database service.
   * @param \Drupal\alshaya_super_category\AlshayaSuperCategoryManager $super_category_manager
   *   The super category manager service.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   Path Validator service object.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage $product_category_page
   *   Product Category Page service.
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   */
  public function __construct(CacheBackendInterface $cache,
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
                              AlshayaApiWrapper $api_wrapper,
                              RendererInterface $renderer,
                              RedirectRepository $redirect_repsitory,
                              SkuInfoHelper $sku_info_helper,
                              Connection $database,
                              AlshayaSuperCategoryManager $super_category_manager,
                              PathValidatorInterface $path_validator,
                              ProductCategoryPage $product_category_page,
                              RequestContext $request_context) {
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
    $this->configFactory = $config_factory;
    $this->apiWrapper = $api_wrapper;
    $this->renderer = $renderer;
    $this->redirectRepository = $redirect_repsitory;
    $this->skuInfoHelper = $sku_info_helper;
    $this->database = $database;
    $this->superCategoryManager = $super_category_manager;
    $this->pathValidator = $path_validator;
    $this->productCategoryPage = $product_category_page;
    $this->baseUrl = $request_context->getCompleteBaseUrl();
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
            $redirect_link = $this->getRedirectedTermDeeplink($object->id());
            if ($redirect_link) {
              $return = $redirect_link;
            }
            else {
              $return = 'category/' . $object->id() . '/product-list';
            }
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
        $redirect_link = $this->getRedirectedTermDeeplink($object->tid);
        if ($redirect_link) {
          $return = $redirect_link;
        }
        else {
          $return = 'category/' . $object->tid . '/product-list';
        }
      }
    }
    elseif ($object instanceof NodeInterface) {
      switch ($object->bundle()) {
        case 'acq_product':
          $sku = $this->skuManager->getSkuForNode($object);
          $return = 'product-exclude-linked/' . $sku;
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

        case 'magazine_article':
          $return = $this->pageDeepLink($object->id(), 'magazine-detail');
          break;

        case 'product_list':
          $return = $this->pageDeepLink($object->id(), 'product_list');
          break;
      }
    }
    elseif ($object instanceof SKUInterface) {
      $return = 'product-exclude-linked/' . $object->getSku();
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
    if ($url->isExternal()) {
      return FALSE;
    }
    $deeplink_url = $url->toString(TRUE)->getGeneratedUrl();
    $deeplink_url = $this->getRedirectUrl($deeplink_url);
    $params = !empty($url->isRouted()) ? $url->getRouteParameters() : NULL;
    if (empty($params)) {
      if ($url->isRouted() && $url->getRouteName() === 'view.magazine_articles.list') {
        return self::ENDPOINT_PREFIX
        . 'page/magazine-block';
      }
      else {
        return self::ENDPOINT_PREFIX
        . 'deeplink?url='
        . $deeplink_url;
      }
    }
    $entity = NULL;
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
    // Return false if there is no path associated with the alias.
    if ('/' . $alias === $internal_path) {
      return FALSE;
    }
    // Get the parameters, to get node id from internal path.
    $params = Url::fromUri("internal:" . $internal_path)->getRouteParameters();
    if (empty($params)) {
      return FALSE;
    }

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

    return $this->skuInfoHelper->getEntityTranslation($node, $this->currentLanguage);
  }

  /**
   * Prepare multiple images array for given entity on given fieldname.
   *
   * @param object $entity
   *   The entity object.
   * @param string $field_name
   *   The field name from which it needs to create images array.
   * @param string $image_style
   *   (optional) The image style to apply.
   *
   * @return array
   *   The array containing information of images if image cardinality
   *   is greater then 1, otherwise return the first image array.
   */
  public function getImages($entity, $field_name, string $image_style = NULL) {
    if (!$entity->hasField($field_name)) {
      return [];
    }

    $images = [];
    if (!empty($entity->get($field_name)->getValue())) {
      $image_style = empty($image_style) ? NULL : $this->entityTypeManager->getStorage('image_style')->load($image_style);
      foreach ($entity->get($field_name)->getValue() as $key => $value) {
        if (($file = $entity->get($field_name)->get($key)->entity) && $file instanceof FileInterface) {
          $file_path = $image_style
            ? $image_style->buildUrl($file->getFileUri())
            : file_create_url($file->getFileUri());

          $images[] = [
            'url' => $file_path,
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
  public function throwException($message = NULL): never {
    throw new NotFoundHttpException($message ?? "page not found");
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
    $response = [];
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

    // If check link field title is not empty.
    if (!empty($title = $entity->$field->title)) {
      $return['title'] = $title;
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
    $default_category_id = $this->superCategoryManager->getDefaultCategoryId();
    $homepage_node = $default_category_id > 0 ? $this->getHomepageNode() : NULL;
    foreach ($terms as $term) {
      $term_url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid])->toString(TRUE);
      $this->termUrls[] = $term_url;

      $path = $term_url->getGeneratedUrl();
      $deeplink = ($default_category_id === (int) $term->tid)
        ? $this->getDeepLink($homepage_node)
        : $this->getDeepLink($term);

      $redirected_term_deeplink = $this->getRedirectedTermDeeplink($term->tid);

      $record = [
        'id' => (int) $term->tid,
        'name' => $term->name,
        'description'  => !empty($term->description__value) ? $term->description__value : '',
        'path' => $path,
        'deeplink' => !empty($redirected_term_deeplink) ? $redirected_term_deeplink : $deeplink,
        'include_in_menu' => (bool) $term->include_in_menu,
        'show_on_dpt' => isset($term->show_on_dept) ? (int) $term->show_on_dept : NULL,
        'cta' => $term->cta ?? NULL ,
        'display_view_all' => isset($term->display_view_all) ? (int) $term->display_view_all : NULL,
      ];

      // Get all brand logo image data.
      $brand_logos = $this->productCategoryTree->getBrandIcons($term->tid);
      // Check for brand logos.
      if (!empty($brand_logos)) {
        $record['brand_logos'] = $brand_logos;
      }

      if (is_object($file = $this->productCategoryTree->getMobileBanner($term->tid, $langcode))
        && !empty($file->field_promotion_banner_mobile_target_id)) {
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

      // Add an alter hook to change the individual category record data. For
      // example some modules add new fields in category all API response based
      // on availability on the features like FL navigation fields.
      $this->moduleHandler->alter('categories_all_response', $record, $term);

      $data[] = $record;
    }
    return $data;
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
   * Return homepage cache tags.
   *
   * @return array
   *   Returns homepage cache tags.
   */
  public function cacheHomePage() {
    return $this->homePageCache;
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

    if (!$node instanceof NodeInterface || $node->bundle() != 'acq_product') {
      return [];
    }
    // Get translated node.
    $node = $this->entityRepository->getTranslationFromContext($node, $langcode);

    $color = ($this->skuManager->isListingModeNonAggregated()) ? $node->get('field_product_color')->getString() : '';

    // Get SKU attached with node.
    $sku = $this->skuManager->getSkuForNode($node);
    $sku_entity = SKU::loadFromSku($sku);

    if ($sku_entity instanceof SKU) {
      return $this->skuInfoHelper->getLightProduct($sku_entity, $color);
    }
    return [];
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
    $user = FALSE;
    // Try to get user from mdc and create new user account.
    try {
      /** @var \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper */
      $customer = $this->apiWrapper->getCustomer($email);

      if (!empty($customer)) {
        $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.utility');
        /** @var \Drupal\user\Entity\User $user */
        $user = alshaya_acm_customer_create_drupal_user($customer);

        $user->set('preferred_langcode', $this->currentLanguage);
        $user->save();

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
        $this->getLogger('MobileAppUtility')->error('Sorry, unable to process your request right now due to connection timeout. Please try again later.');
        throw $e;
      }
    }

    return $user;
  }

  /**
   * Get all the stores.
   *
   * If specified, return only around the specified area.
   *
   * @param string $lat
   *   Latitude.
   * @param string $lng
   *   Longitude.
   *
   * @return array
   *   Array containing stores and cacheable metadata.
   */
  public function getStores(string $lat = '', string $lng = '') {
    $response_data = [];
    $cacheable_metadata = NULL;

    // Get store finder view.
    $view = Views::getView('stores_finder');
    if (!empty($view)) {
      // Set the view display to page_1.
      $view->setDisplay('page_1');
      $proximity_handler = $view->getHandler('page_1', 'filter', 'field_latitude_longitude_proximity');

      if ($lat || $lng) {
        $input = [
          'field_latitude_longitude_proximity-lat' => $lat,
          'field_latitude_longitude_proximity-lng' => $lng,
          'field_latitude_longitude_proximity' => $proximity_handler['value']['value'] ?: 5,
        ];

        // Set exposed form input values.
        $view->setExposedInput($input);
      }

      $view_render_array = NULL;
      $rendered_view = NULL;

      $this->renderer->executeInRenderContext(new RenderContext(),
        function () use ($view, &$view_render_array, &$rendered_view) {
          $view_render_array = $view->render();
          $rendered_view = render($view_render_array);
        }
      );

      $cacheable_metadata = CacheableMetadata::createFromRenderArray($view_render_array);

      foreach ($view->result as $row) {
        $response_data[] = $row->_entity->get('field_store_locator_id')->getValue()[0]['value'];
      }
    }

    return [
      'data' => $response_data,
      'cacheable_metadata' => $cacheable_metadata,
    ];
  }

  /**
   * Get redirect url for a given url.
   *
   * @param string $url
   *   Url for which redirect needs to check.
   *
   * @return mixed|string
   *   Redirect url.
   */
  public function getRedirectUrl(string $url = '') {
    if (empty($url)) {
      return $url;
    }

    // Get the first occurence of string between '/' and '/'. So If url is
    // like '/en/abc/def/xyz', it will have 'en'.
    preg_match('#(?<=/)[^/]+#', $url, $match);
    $langcode = NULL;

    // If language exists for the given langcode.
    if (!empty($match) && $this->languageManager->getLanguage($match[0])) {
      $langcode = $match[0];
    }

    // If langcode exists in the url string.
    if ($langcode && str_contains($url, '/' . $langcode . '/')) {
      $url = str_replace('/' . $langcode . '/', '', $url);

      // Checking if redirects already available or not. If yes, then use or
      // find the redirects.
      // Populating the redirects array because to skip the infinite
      // redirection as well as it goes in in infinite redirection if
      // process/find the redirect for same url more than once in a request.
      if (empty($this->redirects[$langcode][$url])) {
        $redirect = $this->redirectRepository->findMatchingRedirect($url, [], $langcode);
        // We check url without forward slash as there are few without slash.
        if (!$redirect) {
          $redirect = $this->redirectRepository->findMatchingRedirect(rtrim($url, '/'), [], $langcode);
        }
        $redirect_url = $redirect
          ? $redirect->getRedirectUrl()->toString(TRUE)->getGeneratedUrl()
          : $url;
        $this->redirects[$langcode][$url] = $redirect_url;
        $url = $redirect_url;
      }
      else {
        $url = $this->redirects[$langcode][$url];
      }
    }

    return $url;
  }

  /**
   * Helper function to get the deep link for product list product option.
   *
   * @param string $term_url
   *   The option term url.
   *
   * @return null|string
   *   If deep link to the advanced page is found we send it else NULL is sent.
   */
  public function getDeepLinkForProductListProductOption(string $term_url) {
    $term_path = preg_replace("/\/$this->currentLanguage/", '', $term_url);
    $internal_path = $this->aliasManager->getPathByAlias($term_path);
    $url_parts = explode('/', $internal_path);
    // This can happen if proper node url was not found in $term_url.
    if (!isset($url_parts[2])) {
      // For consistency, we send this.
      return NULL;
    }

    // Using database query for low overhead as compared to node load.
    $type = $this->database->select('node_field_data', 'nfd')
      ->fields('nfd', ['type'])
      ->condition('nid', $url_parts[2])
      ->execute()
      ->fetchField();

    if ($type === 'product_list') {
      $redirect_path = $this->getRedirectUrl("/$this->currentLanguage" . $internal_path);
      if (!strpos($internal_path, (string) $redirect_path)) {
        // Although $redirect_path is an alias, we still get the proper url
        // object here.
        $url = Url::fromUri("internal:/$redirect_path");
        return $this->getDeepLinkFromUrl($url);
      }
    }

    // For consistency, we send this.
    return NULL;
  }

  /**
   * Helper function to get homepage node only once by adding static cache.
   */
  protected function getHomepageNode() {
    $homepage_node = &drupal_static(__FUNCTION__);
    if (!isset($homepage_node)) {
      $homepage_nid = $this->configFactory->get('alshaya_master.home')->get('entity')['id'];
      $homepage_node = $this->entityTypeManager->getStorage('node')->load($homepage_nid);
    }
    if (empty($homepage_node)) {
      return [];
    }
    // Associate homepage cache tags to be invalidated by.
    $this->homePageCache = $homepage_node->getCacheTags();
    return $homepage_node;
  }

  /**
   * Lhn status for node.
   *
   * @param string $url
   *   List of all options.
   * @param string $langcode
   *   List of all options.
   *
   * @return bool
   *   Status of LHN.
   */
  public function getProductListLhnStatus($url, $langcode) {
    $product_list_lhn_value = NULL;
    $url_object = $this->pathValidator->getUrlIfValid($url);
    $route_parameters = $url_object->getrouteParameters();
    if (!isset($route_parameters['node']) || !$route_parameters['node']) {
      return FALSE;
    }
    $node = $this->entityTypeManager->getStorage('node')->load($route_parameters['node']);
    if (!$node instanceof NodeInterface) {
      return FALSE;
    }
    // Get translated node.
    $node = $this->entityRepository->getTranslationFromContext($node, $langcode);
    if ($node->bundle() === 'product_list' && $node->get('field_show_in_lhn_options_list')) {
      $product_list_lhn_options_list_value = $node->get('field_show_in_lhn_options_list')[0];
      if ($product_list_lhn_options_list_value === NULL) {
        $product_list_lhn_value = 'Yes';
      }
      else {
        $product_list_lhn_value = $node->get('field_show_in_lhn_options_list')->getValue()[0]['value'];
      }
    }
    if ($product_list_lhn_value === 'No') {
      // IF No status will be TRUE.
      return FALSE;
    }
    // IF Empty or Yes status will be TRUE.
    return TRUE;
  }

  /**
   * Get the category tree exluding unused keys in mobile.
   *
   * @param array $term_data
   *   Data code.
   *
   * @return array
   *   Processed term data from lhn category tree.
   */
  public function excludeUnusedKeysMobile(array &$term_data) {
    $used_keys = [
      'label',
      'id',
      'path',
      'clickable',
      'child',
      'deep_link',
    ];
    foreach ($term_data as $parent_id => $parent_value) {
      $term_data[$parent_id] = $parent_value;
      foreach ($parent_value as $key => $value) {
        // Show category and tree only when `lhn` is enabled.
        if ($key == 'lhn' && empty($value)) {
          unset($term_data[$parent_id]);
        }
        else {
          if (!in_array($key, $used_keys)) {
            unset($term_data[$parent_id][$key]);
          }
          if ($key == 'child' && !empty($term_data[$parent_id][$key])) {
            $this->excludeUnusedKeysMobile($term_data[$parent_id][$key]);
          }
        }
      }
    }
    return $term_data;
  }

  /**
   * Function to get deeplink for term if it has a redirected path.
   *
   * @param string $tid
   *   Term ID.
   */
  public function getRedirectedTermDeeplink($tid) {
    $deeplink = NULL;
    // Check if any redirection is set up for the term path.
    // We provide the technical taxonomy term path here and not the alias
    // as alias redirection for taxonomy terms doesn't seem to work on Drupal
    // front end.
    $term_technical_path = '/taxonomy/term/' . $tid;
    $redirected_path = $this->getRedirectUrl("/{$this->currentLanguage}" . $term_technical_path);

    // If no redirect, then we get the same path we passed for getRedirectUrl
    // without the langcode and hence we do not process them further.
    if (trim($redirected_path, '/') != trim($term_technical_path, '/')) {
      // Process path and deeplink again if a redirection has been set up.
      // Get the path of the target term.
      $internal_path = $this->aliasManager->getPathByAlias(
        rtrim(str_replace("/{$this->currentLanguage}", '', $redirected_path), '/'),
        $this->currentLanguage
      );

      try {
        // Get the taxonomy term ID of the target term.
        $route = Url::fromUri('internal:' . $internal_path);
        $params = !empty($route->isRouted()) ? $route->getRouteParameters() : NULL;
        if (empty($params)) {
          if ($route->getRouteName() === 'view.magazine_articles.list') {
            $deeplink = self::ENDPOINT_PREFIX . 'page/magazine-block';
          }
          else {
            $deeplink = self::ENDPOINT_PREFIX
            . 'deeplink?url='
            . $internal_path;
          }
        }
        else {
          if (!empty($params) && !empty($params['taxonomy_term'])) {
            $redirected_term = $this->entityTypeManager->getStorage('taxonomy_term')->load($params['taxonomy_term']);

            // Get path and deeplink of target term.
            if ($redirected_term instanceof TermInterface
              && $redirected_term->bundle() == 'acq_product_category') {
              $deeplink = $this->getDeepLink($redirected_term);
            }
          }
        }
      }
      catch (\Exception) {
        $this->getLogger('MobileAppUtility')->warning('Internal path looks invalid, please check @internal_path for term id @id', [
          '@id' => $tid,
          '@internal_path' => $internal_path,
        ]);
      }
    }
    return $deeplink;
  }

  /**
   * Helper method to return an error response.
   *
   * @param string $message
   *   Error message.
   *
   * @return \Drupal\rest\ResourceResponse
   *   HTTP Response.
   */
  public function sendErrorResponse(string $message) {
    $response = [];
    $response['success'] = FALSE;
    $response['message'] = $message;
    return (new ResourceResponse($response));
  }

  /**
   * Helper method to get the algolia filter data.
   *
   * @param string $tid
   *   Term ID.
   * @param string $langcode
   *   The language code.
   *
   * @return array
   *   An array containing algolia data.
   */
  public function getAlgoliaData($tid, $langcode) {
    $category_field_temp = [];
    // Get term details in current language for filters.
    $term_details = $this->productCategoryPage->getCurrentSelectedCategory(
      $langcode,
      $tid
    );

    $filter_field = $term_details['category_field'];
    if (Settings::get('mobile_app_plp_index_new', FALSE)) {
      // Append 'en' in 'filter_field' of 'algolia_data'.
      // for ex:
      // 'field_category_name.lvl1' will be 'field_category_name.en.lvl1'.
      $category_field = explode('.', $term_details['category_field']);
      $category_field_temp[] = $category_field[0] . '.' . $langcode . '.';
      array_shift($category_field);
      $filter_field = implode(array_merge($category_field_temp, $category_field));
    }

    return [
      'filter_field' => $filter_field,
      'filter_value' => $term_details['hierarchy'],
      'rule_contexts' => $term_details['ruleContext'],
    ];
  }

  /**
   * Helper function to get deeplink.
   *
   * @param string $alias
   *   Alias value.
   */
  public function getDeeplinkForResource($alias) {
    $alias = str_replace($this->baseUrl, '', $alias);

    if (empty($alias) || UrlHelper::isExternal($alias)) {
      return $this->throwException();
    }

    if (str_contains($alias, 'search')) {
      $query_string_array = $this->requestStack->query->all();
      // Search url may have url like,
      // rest/v1/deeplink?url=search?keywords=dress&f[0]=category
      // %3A10711&sort_bef_combine=search_api_relevance DESC&show_on_load=12
      // So, the $alias contains query string like search?keywords=dress
      // Which further needs to be parsed and "keywords" needs to be added
      // back to query string array to generate complete search deep link.
      $parse = parse_url($alias);
      [$key, $value] = explode('=', $parse['query']);
      $query_string_array = array_merge($query_string_array, [$key => $value]);
      unset($query_string_array['url']);
      unset($query_string_array['_format']);
      $internal_url = Url::fromUri("internal:/rest/v1/{$parse['path']}", ['query' => $query_string_array])->toString(TRUE);
      $url = $internal_url->getGeneratedUrl();
    }
    else {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      $internal_path = $this->aliasManager->getPathByAlias(
        rtrim(str_replace("/{$langcode}", '', $alias), '/'),
        $langcode
      );
      if (strpos($internal_path, 'taxonomy/term')) {
        $redirect_url = $this->getRedirectUrl("/{$langcode}" . $internal_path);
        // Append '/' if it does not exist.
        $redirect_url = (!str_starts_with($redirect_url, '/')) ? ('/' . $redirect_url) : $redirect_url;
        if ($redirect_url !== $internal_path) {
          $internal_path = $this->aliasManager->getPathByAlias(
            rtrim(str_replace("/{$langcode}", '', $redirect_url), '/'),
            $langcode
          );
        }
      }
      else {
        $redirect_url = $this->getRedirectUrl($alias);
        // Get the internal path of given alias and get route object.
        // If $redirect_url is "/" or "/?xyz" we dont find its path.
        $internal_path = (preg_match('/^\/(\?.*)?$/', $redirect_url))
          ? $redirect_url
          : $this->aliasManager->getPathByAlias('/' . $redirect_url, $langcode);
      }

      // Get the internal path of given alias and get route object.
      $url_obj = Url::fromUri("internal:" . $internal_path);
      if (!$url_obj->isRouted()) {
        return $this->throwException();
      }
      $url = $this->getDeepLinkFromUrl($url_obj);
    }

    return $url;
  }

  /**
   * Preprocess the alias.
   *
   * @param string $alias
   *   Path alias.
   */
  private function preprocessAlias(&$alias) {
    // Remove the base url from the alias.
    $alias = str_replace($this->baseUrl, '', $alias);
    // Append .html in the end if it is a product url without .html.
    if (str_contains($alias, 'buy-') && !str_contains($alias, '.html')) {
      $alias = "$alias.html";
    }
  }

  /**
   * Check if its MDC url.
   *
   * @param string $alias
   *   Url alias.
   *
   * @return bool
   *   Returns true if its MDC url.
   */
  protected function checkMdcUrl($alias) {
    $this->preprocessAlias($alias);

    if (empty($alias) || UrlHelper::isExternal($alias)) {
      return $this->throwException();
    }
    // Get route name for the url.
    $url_object = $this->pathValidator->getUrlIfValid($alias);
    if ($url_object === FALSE) {
      return FALSE;
    }

    $route_name = $url_object->getRouteName();
    $route_parameters = $url_object->getRouteParameters();
    // Check if its PLP route.
    if ($route_name == 'entity.taxonomy_term.canonical') {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($route_parameters['taxonomy_term']);
      if ($term instanceof TermInterface
        && in_array($term->bundle(), ['acq_product_category', 'rcs_category'])
      ) {
        return TRUE;
      }
    }
    elseif ($route_name == 'entity.node.canonical') {
      // Check if its PDP route.
      $node = $this->entityTypeManager->getStorage('node')->load($route_parameters['node']);
      if ($node instanceof NodeInterface
        && in_array($node->bundle(), [
          'acq_product',
          'rcs_product',
          'acq_promotion',
          'rcs_promotion',
        ])
      ) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Helper function to get deeplink.
   *
   * @param string $alias
   *   Url alias.
   *
   * @return array
   *   Returns V3 deeplink response.
   */
  public function getDeeplinkForResourceV3($alias) {
    // Check if its mdc url.
    if ($this->checkMdcUrl($alias)) {
      return [
        'deeplink' => '',
        'source' => 'magento',
      ];
    }

    $url = self::getDeeplinkForResource($alias);
    return [
      'deeplink' => $url,
      'source' => 'drupal',
    ];
  }

}
