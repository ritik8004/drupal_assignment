<?php

namespace Drupal\alshaya_facets_pretty_paths\Controller;

use Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyAliases;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for Pretty Paths.
 *
 * @package Drupal\alshaya_facets_pretty_paths\Controller
 */
class AlshayaFacetsPrettyPathsController extends ControllerBase {

  /**
   * Pretty Path aliases.
   *
   * @var \Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyAliases
   */
  protected $prettyAliases;

  /**
   * AlshayaFacetsPrettyPathsController constructor.
   *
   * @param \Drupal\alshaya_facets_pretty_paths\AlshayaFacetsPrettyAliases $pretty_aliases
   *   Pretty Aliases.
   */
  public function __construct(AlshayaFacetsPrettyAliases $pretty_aliases) {
    $this->prettyAliases = $pretty_aliases;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_facets_pretty_paths.pretty_aliases')
    );
  }

  /**
   * Get list of values with aliases for given facet.
   *
   * @param string $facet_alias
   *   The facet alias for which we have to return the list of values.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   Return the json response.
   */
  public function getAliases(string $facet_alias) {
    $aliases = $this->prettyAliases->getAliasesForFacet($facet_alias);
    $response = new CacheableJsonResponse($aliases);
    // Add cacheable dependeny for the term bundle cache, which is executed
    // only when the taxonomy term is inserted or updated and this cache tag is
    // queued and processed.
    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->addCacheTags(['taxonomy_term:sku_product_option']);

    $response->addCacheableDependency($cacheable_metadata);

    return $response;
  }

}
