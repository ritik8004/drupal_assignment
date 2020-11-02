<?php

namespace Drupal\alshaya_search\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Alshaya Search Breadcrumb Builder.
 */
class AlshayaSearchBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * Request stock service object.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AlshayaSearchBreadcrumbBuilder constructor.
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
    $parameters = $route_match->getParameters()->all();
    if (!empty($parameters['view_id'])) {
      return $parameters['view_id'] == 'search';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));
    $breadcrumb->addCacheableDependency(['url.path', 'url.query_args']);

    $queryString = explode('&', $this->currentRequest->getQueryString());

    // If on search page but no filter.
    if (empty($queryString[0])) {
      $breadcrumb->addLink(Link::createFromRoute($this->t('Search'), $route_match->getRouteName()));
    }

    foreach ($queryString as $string) {
      $query = explode('=', $string);
      if ($query[0] == 'keywords') {
        if (empty($query[1])) {
          $linkText = $this->t('Search results');
        }
        else {
          $linkText = $this->t('Search results for "@keyword"', ['@keyword' => urldecode($query[1])]);
        }
        $breadcrumb->addLink(Link::createFromRoute($linkText, '<none>'));
        return $breadcrumb;
      }
    }
    return $breadcrumb;
  }

}
