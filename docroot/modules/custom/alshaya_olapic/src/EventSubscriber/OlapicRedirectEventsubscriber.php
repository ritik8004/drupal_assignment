<?php

namespace Drupal\alshaya_olapic\EventSubscriber;

use Drupal\Core\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Alshaya Acm Redirect To SpcCart.
 */
class OlapicRedirectEventsubscriber implements EventSubscriberInterface {

  const OLAPIC_QUERY_PARAMETER = 'olapicForceRender';

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * OlapicRedirectEventsubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   */
  public function __construct(RouteMatchInterface $route_match,
                              CurrentPathStack $current_path,
                              AliasManagerInterface $alias_manager,
                              ConfigFactoryInterface $config_factory,
                              RequestStack $requestStack) {
    $this->routeMatch = $route_match;
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
    $this->configFactory = $config_factory;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest'];
    return $events;
  }

  /**
   * Redirects with query string to run olpaic in non prod.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Request event object.
   */
  public function onRequest(GetResponseEvent $event) {
    $development_mode = $this->configFactory->get('alshaya_olapic.settings')->get('development_mode') ?? '';
    // Add query string logic to run olapic widget for non-prod environment.
    if ($development_mode == 1) {
      $routename = $this->routeMatch->getRouteName();
      switch ($routename) {
        case "alshaya_master.home":
          $olapic_query_parameter = TRUE;
          break;

        case "entity.node.canonical":
          $current_path = $this->currentPath->getPath();
          $node = $this->routeMatch->getParameter('node');
          if ($node->bundle() == 'advanced_page') {
            $node_path = $this->aliasManager->getAliasByPath($current_path);
            if ($node_path == "/gallery") {
              $olapic_query_parameter = TRUE;
            }
          }
          elseif ($node->bundle() == 'acq_product') {
            $olapic_query_parameter = TRUE;
          }
          break;

        case "entity.taxonomy_term.canonical":
          $olapic_query_parameter = TRUE;
          break;

        default:
          $olapic_query_parameter = FALSE;
      }

      if ($olapic_query_parameter) {
        $query_parameter = $this->requestStack->getCurrentRequest()->query->all();
        if (!array_key_exists(self::OLAPIC_QUERY_PARAMETER, $query_parameter)) {
          $url = Url::fromRoute('<current>', [self::OLAPIC_QUERY_PARAMETER => ''])->toString();
          $response = new RedirectResponse($url);
          $event->setResponse($response);
        }

      }
    }
  }

}
