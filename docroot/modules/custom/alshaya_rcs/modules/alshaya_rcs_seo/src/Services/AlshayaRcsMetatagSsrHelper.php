<?php

namespace Drupal\alshaya_rcs_seo\Services;

use Drupal\alshaya_rcs_listing\Services\AlshayaRcsListingHelper;
use Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper;
use Drupal\alshaya_rcs_promotion\Services\AlshayaRcsPromotionHelper;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;
use Drupal\rcs_placeholders\Service\RcsPhPlaceholderHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Alshaya RCS Metatag SSR Manager.
 */
class AlshayaRcsMetatagSsrHelper {

  /**
   * RCS Product Helper.
   *
   * @var \Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper
   */
  protected $rcsProductHelper;

  /**
   * RCS Listing Helper.
   *
   * @var \Drupal\alshaya_rcs_listing\Services\AlshayaRcsListingHelper
   */
  protected $rcsListingHelper;

  /**
   * RCS Promotion Helper.
   *
   * @var \Drupal\alshaya_rcs_promotion\Services\AlshayaRcsPromotionHelper
   */
  protected $rcsPromotiontHelper;

  /**
   * Alshaya GraphQL API Wrapper.
   *
   * @var \Drupal\alshaya_rcs_seo\Services\AlshayGraphqlApiWrapper
   */
  protected $graphqlApiWrapper;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The RCS Path processor service.
   *
   * @var \Drupal\rcs_placeholders\Service\RcsPhPathProcessor
   */
  protected $rcsPathProcessor;

  /**
   * Static storage for processed metatag data.
   *
   * @var array
   */
  private static $processedMetatagData = [];

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $rcsSeoConfig;

  /**
   * The current user making the request.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The RCS placeholder helper object.
   *
   * @var \Drupal\rcs_placeholders\Service\RcsPhPlaceholderHelper
   */
  protected RcsPhPlaceholderHelper $rcsPlaceholderHelper;

  /**
   * Constructs RCS Metatag Helper service.
   *
   * @param \Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper $rcs_product_helper
   *   RCS Product Helper.
   * @param \Drupal\alshaya_rcs_listing\Services\AlshayaRcsListingHelper $rcs_listing_helper
   *   RCS Listing Helper.
   * @param \Drupal\alshaya_rcs_promotion\Services\AlshayaRcsPromotionHelper $rcs_promotion_helper
   *   RCS Promotion Helper.
   * @param \Drupal\alshaya_rcs_seo\Services\AlshayGraphqlApiWrapper $graphql_api_wrapper
   *   Alshaya GraphQL api wrapper.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service.
   * @param \Drupal\rcs_placeholders\Service\RcsPhPathProcessor $rcs_path_processor
   *   RCS path processor service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\rcs_placeholders\Service\RcsPhPlaceholderHelper $rcs_placeholder_helper
   *   The RCS placeholder helper.
   */
  public function __construct(
    AlshayaRcsProductHelper $rcs_product_helper,
    AlshayaRcsListingHelper $rcs_listing_helper,
    AlshayaRcsPromotionHelper $rcs_promotion_helper,
    AlshayGraphqlApiWrapper $graphql_api_wrapper,
    RequestStack $request_stack,
    RcsPhPathProcessor $rcs_path_processor,
    ModuleHandlerInterface $module_handler,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory,
    AccountProxyInterface $current_user,
    RcsPhPlaceholderHelper $rcs_placeholder_helper
  ) {
    $this->rcsProductHelper = $rcs_product_helper;
    $this->rcsListingHelper = $rcs_listing_helper;
    $this->rcsPromotiontHelper = $rcs_promotion_helper;
    $this->graphqlApiWrapper = $graphql_api_wrapper;
    $this->requestStack = $request_stack;
    $this->rcsPathProcessor = $rcs_path_processor;
    $this->moduleHandler = $module_handler;
    $this->logger = $logger_factory->get('AlshayaRcsMetatagSsrHelper');
    $this->rcsSeoConfig = $config_factory->get('alshaya_rcs_seo.settings');
    $this->currentUser = $current_user;
    $this->rcsPlaceholderHelper = $rcs_placeholder_helper;
  }

  /**
   * Get processed metatags.
   *
   * @return array
   *   Response with meta details array.
   */
  private function getProcessedMetatags(): array {
    return self::$processedMetatagData;
  }

  /**
   * Set processed metatags.
   *
   * @param array $data
   *   Page type to look for in magento.
   */
  private function setProcessedMetatags(array $data): void {
    self::$processedMetatagData = $data;
  }

  /**
   * Get Metatags for particular page type.
   *
   * @param string $page_type
   *   Page type to look for.
   *
   * @return mixed
   *   Response with meta details array.
   */
  private function getRcsMetatagFromMagento(string $page_type): mixed {
    $item_key = $type = NULL;
    $fields = [];
    // Get query fields based on page type.
    switch ($page_type) {
      case 'product':
        $type = 'pdp_product';
        $item_key = 'products';
        break;

      case 'category':
        $type = 'categories';
        $item_key = 'categories';
        break;

      case 'promotion':
        $type = 'promotions';
        $item_key = 'promotionUrlResolver';
        break;
    }

    // Get the graphQL query for the type.
    if (!empty($type)) {
      $fields = $this->rcsPlaceholderHelper->getRcsPlaceholderGraphqlQueryForType($type);
    }

    // Check if the fields are available.
    if (!empty($fields)) {
      $response = $this->graphqlApiWrapper->doGraphqlRequest('GET', $fields);
      return $this->handleRcsResponse($item_key, $response);
    }
    return [];
  }

  /**
   * Handle response from graphql request.
   *
   * @param string $item_key
   *   Item key to check.
   * @param array $response
   *   Response to handle.
   *
   * @return mixed
   *   Response with data.
   */
  private function handleRcsResponse(string $item_key, array $response): mixed {
    // Check if response is empty.
    if (empty($response) || empty($response[$item_key])) {
      return [];
    }

    // Check if the response have count is zero. return 404.
    // Check if the request is for the free gift, return 404.
    if (empty($response[$item_key]['total_count'])
      || ($item_key === 'products'
        && !empty($response['products']['items'][0]['price_range'])
        && $this->isProductFreeGift($response['products']['items'][0]['price_range']))) {
      $currentRequest = $this->requestStack->getCurrentRequest();
      $this->logger->warning('GraphQL data is empty for request @request or its a free gift page request with price @price_range.', [
        '@request' => $currentRequest->getUri(),
        '@price_range' => json_encode($response['products']['items'][0]['price_range']),
      ]);
      $response = new RedirectResponse(Url::fromRoute('system.404', [
        'referer' => $currentRequest->getRequestUri(),
      ])->toString());
      $response->send();
      exit;
    }

    return !empty($response[$item_key]['items'])
      ? $response[$item_key]['items'][0]
      : $response[$item_key];
  }

  /**
   * Helper function to check if product is a free gift.
   *
   * @param array $price_range
   *   Price range to check.
   *
   * @return bool
   *   TRUE if free gift, else FALSE.
   */
  public function isProductFreeGift(array $price_range): bool {
    $freeGift = FALSE;
    if (!empty($price_range['maximum_price'])
      && !empty($price_range['maximum_price']['final_price'])
    ) {
      $productPrice = (float) $price_range['maximum_price']['final_price']['value'];
      $freeGift = $productPrice === 0.0 || $productPrice === 0.01;
    }
    return $freeGift;
  }

  /**
   * Do extra processing for metatags with page type.
   *
   * @param string $page_type
   *   Page type to look for in magento.
   * @param array $data
   *   Data to do extra processing from request.
   */
  private function processMetaForPageType(string $page_type, array &$data): void {
    // Get query fields based on page type.
    switch ($page_type) {
      case 'product':
        if ($this->getProductAssetStatus()
          && !empty($data['variants'])
          && !empty($data['variants'][0]['product']['assets_pdp'])) {
          $assets_pdp = json_decode($data['variants'][0]['product']['assets_pdp'], TRUE);
          if (!empty($assets_pdp)) {
            $data['_self|first_image'] = $assets_pdp[0]['url'];
          }
        }
        elseif (!empty($data['variants']) && !empty($data['variants'][0]['product']['media_gallery'])) {
          $data['_self|first_image'] = $data['variants'][0]['product']['media_gallery'][0]['url'];
        }
        $data['_self|name'] = $data['name'];
        break;

      case 'promotion':
        $data['url_path'] = rtrim(strtok($this->requestStack->getCurrentRequest()->getUri(), '?'), '/');
        $data['name'] = $data['title'];
        break;
    }
  }

  /**
   * Get metatag type for attribute.
   *
   * @param array $attachment
   *   An array of metatag objects to be attached to the current page.
   *
   * @return string
   *   Return attribute type supported for SEO.
   */
  private function getRcsSeoMetatagAttribute(array $attachment): string {
    $attribute_type = '';
    if (array_key_exists('content', $attachment[0]['#attributes'])) {
      $attribute_type = 'content';
    }
    elseif (array_key_exists('href', $attachment[0]['#attributes'])) {
      $attribute_type = 'href';
    }

    return $attribute_type;
  }

  /**
   * Check if the asset mapping is enabled for product.
   *
   * We will get status based on alshaya_rcs_assets module
   * is enabled to check whether we need to use assets
   * for media. ex. HM and COS. As we wanted to keep the SSR related code
   * available in one module, added a check for alshaya_rcs_assets module.
   *
   * This is the temporary solution, we will refactor once
   * permanent solution is placed.
   *
   * @return bool
   *   Return true if applicable otherwise false.
   */
  private function getProductAssetStatus(): bool {
    return $this->moduleHandler->moduleExists('alshaya_rcs_assets');
  }

  /**
   * Check if the SSR need to render for metatags.
   *
   * @return bool
   *   Return true if applicable otherwise false.
   */
  public function getMetatagSsrEnabled(): bool {
    if ($this->currentUser->isAuthenticated()) {
      return FALSE;
    }
    return (bool) $this->rcsSeoConfig->get('enable_ssr_metatag');
  }

  /**
   * Process metatag attachments.
   *
   * @param array $attachments
   *   An array of metatag objects to be attached to the current page.
   */
  public function processMetatagAttachments(array &$attachments): void {
    // Get page type from request i.e. product, category or promotion.
    $page_type = $this->rcsPathProcessor->getRcsPageType();
    if (empty($page_type)) {
      return;
    }

    $rcs_metatags = $this->getProcessedMetatags();
    // Get metatag details using graphQL call to magento for page type.
    if (empty($rcs_metatags[$page_type])) {
      $rcs_metatags[$page_type] = $this->getRcsMetatagFromMagento($page_type);
      // We will not process if data is not available.
      if (empty($rcs_metatags[$page_type])) {
        return;
      }
      // Process meta details w.r.t page type.
      $this->processMetaForPageType($page_type, $rcs_metatags[$page_type]);
      $this->setProcessedMetatags($rcs_metatags);
    }

    // Replace the RCS placeholders in metatags with actual data.
    foreach ($attachments['#attached']['html_head'] as &$attachment) {
      $attribute_type = $this->getRcsSeoMetatagAttribute($attachment);
      if (empty($attribute_type)) {
        continue;
      }

      // Do the replacement based on RCS data.
      $attribute_data = &$attachment[0]['#attributes'][$attribute_type];
      if (is_string($attribute_data) && strpos($attribute_data, "#rcs.$page_type") > -1) {
        $rcs_key = explode('#', $attribute_data)[1];
        $rcs_metatag = str_replace("rcs.$page_type.", '', $rcs_key);
        if (array_key_exists($rcs_metatag, $rcs_metatags[$page_type])) {
          $attribute_data = str_replace("#$rcs_key#", $rcs_metatags[$page_type][$rcs_metatag], $attribute_data);
        }
      }
    }
  }

  /**
   * Process metatag on page.
   *
   * @param array $variables
   *   An array of variable attached to the current page.
   */
  public function preProcessMetatagForPage(array &$variables): void {
    // Get page type from request i.e. product, category or promotion.
    $page_type = $this->rcsPathProcessor->getRcsPageType();
    if ($page_type === 'category') {
      $rcs_metatags = $this->getProcessedMetatags();
      if (empty($rcs_metatags[$page_type])) {
        return;
      }
      // Category name and description replacement using SSR.
      $variables['category_term_name'] = $rcs_metatags[$page_type]['name'];
      $variables['category_term_description'] = $rcs_metatags[$page_type]['description'];
      if (isset($variables['page']['#title'])
        && strpos($variables['page']['#title'], '#rcs.category.name#') > -1) {
        $variables['page']['#title'] = $rcs_metatags[$page_type]['name'];
      }
    }
  }

  /**
   * Process metatag on page.
   *
   * @param array $variables
   *   An array of variable attached to the current page.
   */
  public function preProcessMetatagForBlock(array &$variables): void {
    // Check for only category and promo pages.
    $page_type = $this->rcsPathProcessor->getRcsPageType();
    if (!in_array($page_type, ['category', 'promotion'])) {
      return;
    }

    $rcs_metatags = $this->getProcessedMetatags();
    // Process only for promo and category pages.
    if (empty($rcs_metatags[$page_type])) {
      return;
    }

    // Get the SSR CF ttl from config.
    $cf_cache_ttl = $this->rcsSeoConfig->get('ssr_cf_cache_ttl');
    $rcs_metatags = $rcs_metatags[$page_type];
    switch ($variables['base_plugin_id']) {
      case 'page_title_block':
        $title = &$variables['content']['#title'];
        if ($page_type === 'category'
          && is_array($title)
          && strpos($title['#markup'], '#rcs.category.name#') > -1) {
          $title['#markup'] = str_replace('#rcs.category.name#', $rcs_metatags['name'], $title['#markup']);
        }
        elseif ($page_type === 'promotion' && strpos($title, '#rcs.promotion.name#') > -1) {
          $title = str_replace('#rcs.promotion.name#', '@rcs.promotion.name', $title);
          $title = new FormattableMarkup($title, ['@rcs.promotion.name' => $rcs_metatags['name']]);
        }
        break;

      case 'system_branding_block':
        if ($page_type === 'category'
          && isset($variables['content']['site_name']['#markup'])
          && strpos($variables['content']['site_name']['#markup'], '#rcs.category.meta_title#') > -1) {
          // Setting max-age to get refresh after 20min with CF settings.
          $variables['#cache']['max-age'] = $cf_cache_ttl;
          $variables['content']['site_name']['#markup'] = $variables['site_name'] = $rcs_metatags['name'];
        }
        break;

      case 'rcs_term_description':
        if (isset($variables['content']['#markup'])
          && strpos($variables['content']['#markup'], '#rcs.category.description#') > -1) {
          // Setting max-age to get refresh after 20min with CF settings.
          $variables['#cache']['max-age'] = $cf_cache_ttl;
          $variables['content']['#markup'] =
            str_replace('#rcs.category.description#', (string) $rcs_metatags['description'], $variables['content']['#markup']);
        }
        break;

      case 'alshaya_rcs_promotion_description':
        // Replacement for promo description.
        if (isset($variables['content']['inside']['#children'])
          && strpos($variables['content']['inside']['#children'], '#rcs.promotion.description#') > -1) {
          // Setting max-age to get refresh after 20min with CF settings.
          $variables['#cache']['max-age'] = $cf_cache_ttl;
          $variables['content']['inside']['#children'] =
            str_replace('#rcs.promotion.description#', (string) $rcs_metatags['description'], $variables['content']['inside']['#children']);
        }
        break;
    }
  }

}
