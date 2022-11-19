<?php

namespace Drupal\alshaya_rcs_seo\Services;

use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_seo_transac\AlshayaGtmManager;
use Drupal\node\Entity\Node;

/**
 * Class Alshaya RCS Seo Gtm Manager.
 *
 * The class extends \Drupal\alshaya_seo_transac\AlshayaGtmManager to override
 * functions to make them compatible to V3.
 */
class AlshayaRcsSeoGtmManager extends AlshayaGtmManager {

  /**
   * {@inheritDoc}
   */
  public function fetchSkuAtttributes($skuId, SKUInterface $child = NULL, $parentSku = NULL) {
    $attributes = [];
    $gtm_disabled_vars = $this->configFactory->get('alshaya_seo.disabled_gtm_vars')->get('disabled_vars');

    $attributes['gtm-name'] = '#rcs.product.gtmAttributes.name#';
    $attributes['gtm-product-sku'] = '#rcs.product.sku#';
    $attributes['gtm-sku-type'] = '#rcs.product.skuType#';

    // Dimension1 & 2 correspond to size & color.
    // Should stay blank unless added to cart.
    if (!in_array('dimension1', $gtm_disabled_vars)) {
      $attributes['gtm-dimension1'] = '#rcs.product.gtmAttributes.dimension1#';
    }
    if (!in_array('dimension5', $gtm_disabled_vars)) {
      $attributes['gtm-dimension5'] = '#rcs.product.gtmAttributes.dimension5#';
    }
    if (!in_array('dimension6', $gtm_disabled_vars)) {
      $attributes['gtm-dimension6'] = '#rcs.product.gtmAttributes.dimension6#';
    }
    $attributes['gtm-dimension4'] = '#rcs.product.image#';
    $attributes['gtm-dimension3'] = '#rcs.product.gtmAttributes.dimension3#';

    $attributes['gtm-price'] = '#rcs.product.gtmAttributes.price#';
    if (!in_array('brand', $gtm_disabled_vars)) {
      $attributes['gtm-brand'] = '#rcs.product.gtmAttributes.brand#';
    }
    // @todo This is supposed to stay blank here?
    $attributes['gtm-stock'] = '';
    $attributes['gtm-category'] = '#rcs.product.gtmAttributes.category#';
    $attributes['gtm-main-sku'] = $parentSku ?: $skuId;
    // Add these temporary values so these can be used in front end to make
    // API calls.
    $attributes['gtm-temp-sku'] = $skuId;
    $attributes['gtm-temp-parentSku'] = $parentSku;

    return $attributes;
  }

  /**
   * {@inheritDoc}
   */
  public function convertHtmlAttributesToDatalayer($attributes) {
    $product_datalayer_attributes = parent::convertHtmlAttributesToDatalayer($attributes);
    $product_datalayer_attributes['gtm-temp-sku'] = $attributes['gtm-temp-sku'];
    $product_datalayer_attributes['gtm-temp-parentSku'] = $attributes['gtm-temp-parentSku'];

    return $product_datalayer_attributes;
  }

  /**
   * Helper function to prepare attributes for RCS product.
   *
   * @param \Drupal\node\Entity\Node $rcs_product
   *   Node object for which we want to get the attributes prepared.
   * @param string $view_mode
   *   View mode in which we trying to render the product.
   * @param \Drupal\acq_commerce\SKUInterface|null $child
   *   The child sku object or null.
   *
   * @return array
   *   Array of attributes to be exposed to GTM.
   */
  public function fetchProductGtmAttributes(Node $rcs_product, $view_mode, SKUInterface $child = NULL) {
    $attributes = [];
    static $gtm_container = NULL;
    $gtm_disabled_vars = $this->configFactory->get('alshaya_seo.disabled_gtm_vars')->get('disabled_vars');

    if (!isset($gtm_container)) {
      $gtm_container = $this->convertCurrentRouteToGtmPageName($this->getGtmContainer());
    }

    if ($rcs_product->hasTranslation('en')) {
      $rcs_product = $rcs_product->getTranslation('en');
    }

    $attributes['gtm-type'] = 'gtm-product-link';
    $attributes['gtm-container'] = $gtm_container;
    $attributes['gtm-view-mode'] = $view_mode;

    // Product specific attributes will be added in front end.
    $attributes['gtm-category'] = '#rcs.product.gtm_attributes.category#';
    $attributes['gtm-name'] = '#rcs.product.gtm_attributes.name#';
    $attributes['gtm-product-sku'] = '#rcs.product._self|sku#';
    $attributes['gtm-product-sku-class-identifier'] = '#rcs.product._self|sku-clean#';
    $attributes['gtm-sku-type'] = '#rcs.product._self|sku-type#';
    $attributes['gtm-main-sku'] = '#rcs.product._self|sku#';
    $attributes['gtm-magento-product-id'] = '#rcs.product.id#';
    $attributes['gtm-old-price'] = '#rcs.product._self|old-price#';

    // Dimension1 & 2 correspond to size & color.
    // Should stay blank unless added to cart.
    if (!in_array('dimension1', $gtm_disabled_vars)) {
      $attributes['gtm-dimension1'] = '#rcs.product.gtm_attributes.dimension1#';
    }
    if (!in_array('dimension5', $gtm_disabled_vars)) {
      $attributes['gtm-dimension5'] = '#rcs.product.gtm_attributes.dimension5#';
    }
    if (!in_array('dimension6', $gtm_disabled_vars)) {
      $attributes['gtm-dimension6'] = '#rcs.product.gtm_attributes.dimension6#';
    }

    if (!in_array('brand', $gtm_disabled_vars)) {
      $attributes['gtm-brand'] = '#rcs.product.gtm_attributes.brand#';
    }

    // @todo Check if image not available, it should have the value:
    // 'image not available'.
    $attributes['gtm-dimension4'] = '#rcs.product.gtm_attributes.dimension4#';

    // This contains the formatted price with proper decimals.
    $attributes['gtm-price'] = '#rcs.product._self|gtm-price#';

    // Contains the count of the media items.
    $attributes['gtm-dimension3'] = '#rcs.product.gtm_attributes.dimension3#';

    $this->moduleHandler->invokeAll('gtm_product_attributes_alter',
      [
        &$rcs_product,
        &$attributes,
      ]
    );
    return $attributes;
  }

  /**
   * Helper function to get department specific V2 attributes.
   */
  public function fetchV2DepartmentAttributes() {
    return [
      'departmentName' => '#rcs.category.departmentName#',
      'departmentId' => '#rcs.category.departmentId#',
      'listingName' => '#rcs.category.name#',
      'listingId' => '#rcs.category.id#',
      'majorCategory' => '#rcs.category.majorCategory#',
      'minorCategory' => '#rcs.category.minorCategory#',
      'subCategory' => '#rcs.category.subCategory#',
    ];
  }

  /**
   * Helper function to fetch page-specific datalayer attributes.
   */
  public function fetchPageSpecificAttributes($page_type, $current_route) {
    $page_dl_attributes = [];
    switch ($page_type) {
      case 'product listing page':
        // Call V2 attribute function if RCS Listing module is enabled.
        if ($this->moduleHandler->moduleExists('alshaya_rcs_listing')) {
          $page_dl_attributes = $this->fetchV2DepartmentAttributes();
        }
        else {
          $page_dl_attributes = parent::fetchPageSpecificAttributes($page_type, $current_route);
        }
        break;

      case 'department page':
        $department_node = $current_route['route_params']['node'];
        // Call V2 attribute function for department page if the rcs main menu
        // module is enabled.
        if ($this->moduleHandler->moduleExists('alshaya_rcs_main_menu')
          && $department_node->get('field_use_as_department_page')->getString() == 1
        ) {
          $page_dl_attributes = $this->fetchV2DepartmentAttributes();
        }
        else {
          $page_dl_attributes = parent::fetchPageSpecificAttributes($page_type, $current_route);
        }
        break;

      case 'product detail page':
        // We prepare these values directly on the front end. So we set them
        // empty here.
        $page_dl_attributes = [];
        break;

      default:
        $page_dl_attributes = parent::fetchPageSpecificAttributes($page_type, $current_route);
    }

    return $page_dl_attributes;
  }

}
