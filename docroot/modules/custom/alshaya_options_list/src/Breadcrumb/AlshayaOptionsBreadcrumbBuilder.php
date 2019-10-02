<?php

namespace Drupal\alshaya_options_list\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\alshaya_options_list\AlshayaOptionsListHelper;

/**
 * Class AlshayaOptionsBreadcrumbBuilder.
 *
 * Breadcrumbs for options list pages.
 */
class AlshayaOptionsBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * Path Validator service object.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The Title Resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * Request stock service object.
   *
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * AlshayaOptionsBreadcrumbBuilder constructor.
   *
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   Path Validator service object.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The Title Resolver.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stock service object.
   */
  public function __construct(PathValidatorInterface $path_validator,
                              TitleResolverInterface $title_resolver,
                              RequestStack $request_stack) {
    $this->pathValidator = $path_validator;
    $this->titleResolver = $title_resolver;
    $this->currentRequest = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // Breadcrumb for 'options list' pages.
    return stripos($route_match->getRouteName(), 'alshaya_options_list') === 0 ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home', [], ['context' => 'breadcrumb']), '<front>'));

    $request = $this->currentRequest;
    $url_object = $this->pathValidator->getUrlIfValid($request->getPathInfo());
    $route_name = $url_object->getRouteName();

    $title = $this->titleResolver->getTitle($request, $route_match->getRouteObject());
    $breadcrumb->addLink(Link::createFromRoute($title, $route_name));

    // This breadcrumb builder is based on a route parameter 'title',
    // hence it depends on the 'route' cache context.
    $breadcrumb->addCacheContexts(['route']);
    $breadcrumb->addCacheTags([AlshayaOptionsListHelper::OPTIONS_PAGE_CACHETAG]);

    return $breadcrumb;
  }

}
