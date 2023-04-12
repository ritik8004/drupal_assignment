<?php

namespace Drupal\alshaya_rcs_seo\Services;

use Drupal\alshaya_api\AlshayGraphqlApiWrapper;
use Drupal\alshaya_rcs_listing\Services\AlshayaRcsListingHelper;
use Drupal\alshaya_rcs_product\Services\AlshayaRcsProductHelper;
use Drupal\alshaya_rcs_promotion\Services\AlshayaRcsPromotionHelper;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Alshaya RCS Metatag Manager.
 */
class AlshayaRcsMetatagHelper {

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
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack service.
   */
  public function __construct(
    AlshayaRcsProductHelper $rcs_product_helper,
    AlshayaRcsListingHelper $rcs_listing_helper,
    AlshayaRcsPromotionHelper $rcs_promotion_helper,
    AlshayGraphqlApiWrapper $graphql_api_wrapper,
    RequestStack $requestStack,
  ) {
    $this->rcsProductHelper = $rcs_product_helper;
    $this->rcsListingHelper = $rcs_listing_helper;
    $this->rcsPromotiontHelper = $rcs_promotion_helper;
    $this->graphqlApiWrapper = $graphql_api_wrapper;
    $this->requestStack = $requestStack;
  }

  /**
   * Get overriden metatags in rcs.
   *
   * @param array $attachments
   *   An array of metatag objects to be attached to the current page.
   * @param string $url
   *   Metatag url.
   */
  public function getCanonicalMetatags(array &$attachments, $url) {
    foreach ($attachments['#attached']['html_head'] as &$tag) {
      if ($tag[1] === 'canonical_url') {
        $tag[0]['#attributes']['href'] = $url;
      }
      elseif ($tag[1] === 'twitter_cards_page_url') {
        $tag[0]['#attributes']['content'] = $url;
      }
    }
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

}
