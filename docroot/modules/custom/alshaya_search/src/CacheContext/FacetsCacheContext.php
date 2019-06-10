<?php

namespace Drupal\alshaya_search\CacheContext;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class FacetsCacheContext.
 *
 * @package Drupal\facets
 */
class FacetsCacheContext implements CacheContextInterface {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new FacetsCacheContext object.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Facets');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   */
  public function getContext() {
    $request = $this->requestStack->getCurrentRequest();
    $facets = $request->request->get('f');
    $facetContextValue = '';
    if (is_array($facets) && count($facets)) {
      $facetContextValue = implode('|', $facets);
    }
    return $facetContextValue;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
