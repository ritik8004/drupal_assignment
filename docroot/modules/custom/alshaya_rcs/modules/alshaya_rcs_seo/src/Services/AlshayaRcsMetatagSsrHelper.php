<?php

namespace Drupal\alshaya_rcs_seo\Services;

use Drupal\alshaya_api\AlshayGraphqlApiWrapper;
use Drupal\alshaya_rcs_listing\Services\AlshayaRcsListingHelper;
use Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper;
use Drupal\alshaya_rcs_promotion\Services\AlshayaRcsPromotionHelper;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\rcs_placeholders\Service\RcsPhPathProcessor;
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
   * @var \Drupal\alshaya_api\AlshayGraphqlApiWrapper
   */
  protected $graphqlApiWrapper;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
  protected static $processedMetatagData = [];

  /**
   * Constructs RCS Metatag Helper service.
   *
   * @param \Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper $rcs_product_helper
   *   RCS Product Helper.
   * @param \Drupal\alshaya_rcs_listing\Services\AlshayaRcsListingHelper $rcs_listing_helper
   *   RCS Listing Helper.
   * @param \Drupal\alshaya_rcs_promotion\Services\AlshayaRcsPromotionHelper $rcs_promotion_helper
   *   RCS Promotion Helper.
   * @param \Drupal\alshaya_api\AlshayGraphqlApiWrapper $graphql_api_wrapper
   *   Alshaya GraphQL api wrapper.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service.
   * @param \Drupal\rcs_placeholders\Service\RcsPhPathProcessor $rcs_path_processor
   *   RCS path processor service.
   */
  public function __construct(
    AlshayaRcsProductHelper $rcs_product_helper,
    AlshayaRcsListingHelper $rcs_listing_helper,
    AlshayaRcsPromotionHelper $rcs_promotion_helper,
    AlshayGraphqlApiWrapper $graphql_api_wrapper,
    RequestStack $request_stack,
    ConfigFactoryInterface $config_factory,
    RcsPhPathProcessor $rcs_path_processor
  ) {
    $this->rcsProductHelper = $rcs_product_helper;
    $this->rcsListingHelper = $rcs_listing_helper;
    $this->rcsPromotiontHelper = $rcs_promotion_helper;
    $this->graphqlApiWrapper = $graphql_api_wrapper;
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
    $this->rcsPathProcessor = $rcs_path_processor;
  }

  /**
   * Get product metatags graphQL query in rcs.
   *
   * @return array
   *   GraphQL query params
   */
  public function getProductMetatagFields(): array {
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
   * Get listing page metatags graphQL query in rcs.
   *
   * @return array
   *   GraphQL query params
   */
  public function getListingMetatagFields(): array {
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
   * Get promotion metatags graphQL query in rcs.
   *
   * @return array
   *   GraphQL query params
   */
  public function getPromotionMetatagFields(): array {
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
  public function getProcessedMetatags(): array {
    return self::$processedMetatagData;
  }

  /**
   * Set processed metatags.
   */
  public function setProcessedMetatags($data): void {
    self::$processedMetatagData = $data;
  }

  /**
   * Get Metatags for particular page type.
   *
   * @param string $page_type
   *   Page type to look for in magento.
   *
   * @return mixed
   *   Response with meta details array.
   */
  public function getRcsMetatagFromMagento(string $page_type): mixed {
    $item_key = NULL;
    $fields = $response = [];
    // Get query fields based on page type.
    switch ($page_type) {
      case 'product':
        $fields = $this->getProductMetatagFields();
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
      $response = !empty($response[$item_key]['items']) ? $response[$item_key]['items'][0] : $response[$item_key];
    }
    return $response;
  }

  /**
   * Process metatags with page type.
   *
   * @param string $page_type
   *   Page type to look for in magento.
   * @param array $data
   *   Page type to look for in magento.
   */
  public function processMetaForPageType(string $page_type, array &$data): void {
    // Get query fields based on page type.
    switch ($page_type) {
      case 'product':
        if (!empty($data['variants']) && !empty($data['variants'][0]['product']['media_gallery'])) {
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
  public function getRcsSeoMetatagAttribute(array $attachment): string {
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
   * Process metatag attachments.
   *
   * @param array $attachments
   *   An array of metatag objects to be attached to the current page.
   */
  public function processMetatagAttachments(array &$attachments): void {

    // Check if the SSR is enabled for metatag.
    if (!$this->configFactory->get('alshaya_rcs_seo.settings')->get('enable_ssr_metatag')) {
      return;
    }

    // Get page type from request i.e. product, category or promotion.
    $page_type = $this->rcsPathProcessor->getRcsPageType();
    if (empty($page_type)) {
      return;
    }

    $rcs_metatags = $this->getProcessedMetatags();
    // Get metatag details using graphQL call to magento for page type.
    if (empty($rcs_metatags[$page_type])) {
      $rcs_metatags[$page_type] = $this->getRcsMetatagFromMagento($page_type);
      // Process meta details w.r.t page type.
      $this->processMetaForPageType($page_type, $rcs_metatags[$page_type]);
      $this->setProcessedMetatags($rcs_metatags);
    }

    // We will not process if data is not available.
    if (empty($rcs_metatags[$page_type])) {
      return;
    }

    // Replace the rcs placeholders in metatags with actual data.
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
      if (strpos($variables['page']['#title'], '#rcs.category.name#') > -1) {
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
    // Replacement for category and promotion name for page title.
    if ($variables['plugin_id'] == 'page_title_block') {
      $title = &$variables['content']['#title'];
      if ($page_type === 'category' && strpos($title['#markup'], '#rcs.category.name#') > -1) {
        $title['#markup'] = str_replace('#rcs.category.name#', $rcs_metatags['name'], $title['#markup']);
      }
      elseif ($page_type === 'promotion' && strpos($title, '#rcs.promotion.name#') > -1) {
        $title = str_replace('#rcs.promotion.name#', '@rcs.promotion.name', $title);
        $title = new FormattableMarkup($title, ['@rcs.promotion.name' => $rcs_metatags['name']]);
      }
      return;
    }

    // Replacement for description.
    if ($variables['plugin_id'] == 'rcs_term_description'
      && strpos($variables['content']['#markup'], '#rcs.category.description#') > -1) {
      // Setting max-age to get refresh after 20min with CF settings.
      $variables['#cache']['max-age'] = 1200;
      $variables['content']['#markup'] =
        str_replace('#rcs.category.description#', (string) $rcs_metatags['description'], $variables['content']['#markup']);
    }

    // Replacement for promo description.
    if ($variables['plugin_id'] == 'alshaya_rcs_promotion_description'
      && strpos($variables['content']['inside']['#children'], '#rcs.promotion.description#') > -1) {
      // Setting max-age to get refresh after 20min with CF settings.
      $variables['#cache']['max-age'] = 1200;
      $variables['content']['inside']['#children'] =
        str_replace('#rcs.promotion.description#', (string) $rcs_metatags['description'], $variables['content']['inside']['#children']);
    }
  }

}
