<?php

namespace Drupal\alshaya_rcs_seo\Services;

use Drupal\alshaya_rcs_listing\Services\AlshayaRcsListingHelper;
use Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper;
use Drupal\alshaya_rcs_promotion\Services\AlshayaRcsPromotionHelper;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;
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
  ) {
    $this->rcsProductHelper = $rcs_product_helper;
    $this->rcsListingHelper = $rcs_listing_helper;
    $this->rcsPromotiontHelper = $rcs_promotion_helper;
    $this->graphqlApiWrapper = $graphql_api_wrapper;
    $this->requestStack = $request_stack;
    $this->rcsPathProcessor = $rcs_path_processor;
    $this->moduleHandler = $module_handler;
    $this->logger = $logger_factory->get('alshaya_rcs_seo');
  }

  /**
   * Get product metatags graphQL query in RCS.
   *
   * @return array
   *   GraphQL query params
   */
  private function getProductMetatagFields(): array {
    $url_key = $this->rcsProductHelper->getProductUrlKey();
    return [
      'query' => [
        'query($url: String)' => [
          'products(filter: { url_key: { eq: $url } })' => [
            'total_count',
            'items' => [
              'sku',
              'id',
              'type_id',
              'name',
              'url_key',
              'meta_title',
              'meta_description',
              'meta_keyword',
              'og_meta_title',
              'og_meta_description',
              'price_range' => [
                'maximum_price' => [
                  'final_price' => [
                    'value',
                  ],
                ],
              ],
              '... on ConfigurableProduct' => [
                'variants' => [
                  'product' => [
                    'id',
                    'sku',
                    'media_gallery' => [
                      'url',
                      '... on ProductImage' => [
                        'url',
                      ],
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
      'variables' => [
        'url' => $url_key,
      ],
    ];
  }

  /**
   * Get listing page metatags graphQL query in RCS.
   *
   * @return array
   *   GraphQL query params
   */
  private function getListingMetatagFields(): array {
    $url_key = $this->rcsListingHelper->getListingUrlKey();
    return [
      'query' => [
        'query($urlKey: [String])' => [
          'categories(filters: { url_path: { in: $urlKey }})' => [
            'total_count',
            'items' => [
              'id',
              'name',
              'level',
              'url_path',
              'description',
              'meta_title',
              'meta_keyword',
              'meta_description',
              'image',
            ],
          ],
        ],
      ],
      'variables' => [
        'urlKey' => [$url_key],
      ],
    ];
  }

  /**
   * Get promotion metatags graphQL query in RCS.
   *
   * @return array
   *   GraphQL query params
   */
  private function getPromotionMetatagFields(): array {
    $url_key = $this->rcsPromotiontHelper->getPromotionUrlKey();
    return [
      'query' => [
        'query($urlKey: String)' => [
          'promotionUrlResolver(url_key: $urlKey)' => [
            'id',
            'title',
            'description',
          ],
        ],
      ],
      'variables' => [
        'urlKey' => $url_key,
      ],
    ];
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
    $item_key = NULL;
    $fields = [];
    // Get query fields based on page type.
    switch ($page_type) {
      case 'product':
        $fields = $this->getProductMetatagFields();
        // Check if the assets are set for media.
        if ($this->getProductAssetStatus()) {
          $query = &$fields['query']['query($url: String)']['products(filter: { url_key: { eq: $url } })'];
          $query['items']['... on ConfigurableProduct']['variants']['product'][] = 'assets_pdp';
        }
        $item_key = 'products';
        break;

      case 'category':
        $fields = $this->getListingMetatagFields();
        $item_key = 'categories';
        break;

      case 'promotion':
        $fields = $this->getPromotionMetatagFields();
        $item_key = 'promotionUrlResolver';
        break;
    }

    if (!empty($fields) && $item_key) {
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
   * @return bool
   *   Return true if applicable otherwise false.
   */
  private function getProductAssetStatus(): bool {
    return $this->moduleHandler->moduleExists('alshaya_rcs_assets');
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
          $variables['#cache']['max-age'] = 1200;
          $variables['content']['site_name']['#markup'] = $variables['site_name'] = $rcs_metatags['name'];
        }
        break;

      case 'rcs_term_description':
        if (isset($variables['content']['#markup'])
          && strpos($variables['content']['#markup'], '#rcs.category.description#') > -1) {
          // Setting max-age to get refresh after 20min with CF settings.
          $variables['#cache']['max-age'] = 1200;
          $variables['content']['#markup'] =
            str_replace('#rcs.category.description#', (string) $rcs_metatags['description'], $variables['content']['#markup']);
        }
        break;

      case 'alshaya_rcs_promotion_description':
        // Replacement for promo description.
        if (isset($variables['content']['inside']['#children'])
          && strpos($variables['content']['inside']['#children'], '#rcs.promotion.description#') > -1) {
          // Setting max-age to get refresh after 20min with CF settings.
          $variables['#cache']['max-age'] = 1200;
          $variables['content']['inside']['#children'] =
            str_replace('#rcs.promotion.description#', (string) $rcs_metatags['description'], $variables['content']['inside']['#children']);
        }
        break;
    }
  }

}
