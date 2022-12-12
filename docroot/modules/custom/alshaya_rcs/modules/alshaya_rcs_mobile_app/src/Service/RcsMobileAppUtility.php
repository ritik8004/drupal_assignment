<?php

namespace Drupal\alshaya_rcs_mobile_app\Service;

use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\Component\Utility\UrlHelper;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Service class for RcsMobileAppUtility.
 */
class RcsMobileAppUtility extends MobileAppUtility {

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
  public function getDeeplinkForResource($alias) {
    // Check if its mdc url.
    if ($this->checkMdcUrl($alias)) {
      return [
        'deeplink' => '',
        'source' => 'magento',
      ];
    }

    $url = parent::getDeeplinkForResource($alias);
    return [
      'deeplink' => $url,
      'source' => 'drupal',
    ];
  }

}
