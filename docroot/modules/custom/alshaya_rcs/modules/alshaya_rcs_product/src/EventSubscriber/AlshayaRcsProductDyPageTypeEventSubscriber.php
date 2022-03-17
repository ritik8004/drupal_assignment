<?php

namespace Drupal\alshaya_rcs_product\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\alshaya_rcs\Services\AlshayaRcsApiHelper;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class RCS Product DyPage Type Event Subscriber.
 *
 * @package Drupal\alshaya_rcs_product\EventSubscriber
 */
class AlshayaRcsProductDyPageTypeEventSubscriber implements EventSubscriberInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * RCS Api helper.
   *
   * @var \Drupal\alshaya_rcs\Services\AlshayaRcsApiHelper
   */
  protected $apiHelper;

  /**
   * ProductDyPageTypeEventSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route Match Object.
   * @param \Drupal\alshaya_rcs\Services\AlshayaRcsApiHelper $api_helper
   *   The RCS API helper object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    AlshayaRcsApiHelper $api_helper,
    LanguageManagerInterface $language_manager,
    RequestStack $request_stack
  ) {
    $this->routeMatch = $route_match;
    $this->apiHelper = $api_helper;
    $this->languageManager = $language_manager;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['dy.set.context'][] = ['setContextProduct', 250];
    return $events;
  }

  /**
   * Set PRODUCT Context for Dynamic yield script.
   *
   * @param \Drupal\dynamic_yield\Event $event
   *   Dispatched Event.
   */
  public function setContextProduct(Event $event) {
    if ($this->routeMatch->getRouteName() !== 'entity.node.canonical') {
      return;
    }
    if (($node = $this->routeMatch->getParameter('node')) && $node instanceof NodeInterface) {
      if ($node->bundle() == 'rcs_product') {
        $event->setDyContext('PRODUCT');
        // Get current product page url.
        $product_url = str_replace('.html', '', $this->request->getRequestUri());
        // Remove the langcode prefix.
        $langcode = $this->languageManager->getCurrentLanguage()->getId();
        $product_url = str_replace("/$langcode/", '', $product_url);
        // Prepare the graphql query with product url.
        $query = '{products(filter:{url_key:{eq:"' . $product_url . '"}}){items{type_id sku ... on ConfigurableProduct{variants{product{sku}}}}}}';

        // Invoke the graphql endpoint.
        $response = $this->apiHelper->invokeGraphqlEndpoint($query);
        // Extract the product options if available.
        if ($response
          && $response['data']['products']
          && count($response['data']['products']['items']) > 0) {
          $product = $response['data']['products']['items'][0];
          // For configurable products, use the first available SKU.
          // @see ProductDyPageTypeEventSubscriber
          if ($product['type_id'] == 'configurable' && count($product['variants']) > 0) {
            $event->setDyContextData([$product['variants'][0]['product']['sku']]);
          }
          else {
            $event->setDyContextData([$product['sku']]);
          }
        }
      }
    }
  }

}
