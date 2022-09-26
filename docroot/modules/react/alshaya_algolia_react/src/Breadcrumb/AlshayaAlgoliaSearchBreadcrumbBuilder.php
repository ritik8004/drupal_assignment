<?php

namespace Drupal\alshaya_algolia_react\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Class Alshaya Algolia Search Breadcrumb Builder.
 */
class AlshayaAlgoliaSearchBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * Request stock service object.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AlshayaAlgoliaSearchBreadcrumbBuilder constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stock service object.
   */
  public function __construct(RequestStack $request_stack) {
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return $route_match->getRouteName() === 'alshaya_algolia_react.search';
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute($this->t('Search results'), '<none>'));

    // Create a cacheable object and use the url path as cache context.
    $cacheability = new CacheableMetadata();
    $cacheability->addCacheContexts(['url.path:context']);

    // Add url path cacheable context for the breadcrumb.
    $breadcrumb->addCacheableDependency($cacheability);
    return $breadcrumb;
  }

}
