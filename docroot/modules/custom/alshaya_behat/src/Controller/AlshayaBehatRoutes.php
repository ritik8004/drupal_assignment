<?php

namespace Drupal\alshaya_behat\Controller;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_behat\Service\AlshayaBehatHelper;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Site\Settings;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Alshaya Behat controller.
 */
class AlshayaBehatRoutes extends ControllerBase {

  /**
   * Request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Alshaya behat helper.
   *
   * @var \Drupal\alshaya_behat\Service\AlshayaBehatHelper
   */
  protected $alshayaBehat;

  /**
   * The api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  protected $apiWrapper;

  /**
   * The api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $alshayaApi;

  /**
   * The sku manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): AlshayaBehatRoutes|static {
    return new static(
      $container->get('request_stack'),
      $container->get('alshaya_behat.helper'),
      $container->get('acq_commerce.api'),
      $container->get('alshaya_api.api'),
      $container->get('alshaya_acm_product.skumanager')
    );
  }

  /**
   * Constructor for AlshayaBehatRoutes.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\alshaya_behat\Service\AlshayaBehatHelper $alshaya_behat
   *   Alshaya behat.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   The acm api wrapper.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $alshaya_api
   *   The mdc api wrapper.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   The sku manager service.
   */
  public function __construct(
    RequestStack $request_stack,
    AlshayaBehatHelper $alshaya_behat,
    APIWrapper $api_wrapper,
    AlshayaApiWrapper $alshaya_api,
    SkuManager $sku_manager
  ) {
    $this->request = $request_stack->getCurrentRequest();
    $this->alshayaBehat = $alshaya_behat;
    $this->apiWrapper = $api_wrapper;
    $this->alshayaApi = $alshaya_api;
    $this->skuManager = $sku_manager;
  }

  /**
   * Access checker for behat requests.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function checkAccess(): AccessResult {
    $behat_key_in_settings = Settings::get('behat_secret_key');
    if (empty($behat_key_in_settings)) {
      return AccessResult::forbidden('Secret key not provided in settings');
    }
    $behat_key_in_url = $this->request->query->get('behat');
    return AccessResult::allowedIf($behat_key_in_settings === $behat_key_in_url);
  }

  /**
   * Provides the first in stock product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects to node page if found else redirects to 404 page.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  public function firstInStockProduct(): RedirectResponse {
    $node = $this->alshayaBehat->getWorkingProduct();
    if ($node instanceof NodeInterface) {
      // Redirect to the node page.
      return new RedirectResponse($node->toUrl()->toString());
    }

    // If no SKU is found which is in stock, then redirect to 400 page.
    throw new BadRequestHttpException('No in-stock products found.');
  }

  /**
   * Provides the first OOS product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects to node page if found else redirects to 404 page.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  public function firstOosProduct(): RedirectResponse {
    $node = $this->alshayaBehat->getWorkingProduct(TRUE);
    if ($node instanceof NodeInterface) {
      // Redirect to the node page.
      return new RedirectResponse($node->toUrl()->toString());
    }

    // If no SKU is found which is OOS, then redirect to 400 page.
    throw new BadRequestHttpException('No OOS products found.');
  }

  /**
   * Provides the first PLP with in stock products.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects to PLP page if found else redirects to 404 page.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  public function firstPlpWithInStockProduct(): RedirectResponse {
    $term = $this->alshayaBehat->getWorkingCategory();
    if ($term instanceof EntityInterface) {
      // Redirect to the category page.
      return new RedirectResponse($term->toUrl()->toString());
    }

    // If no SKU is found which is in stock or category not available
    // then redirect to 400 page.
    throw new BadRequestHttpException('No PLP with in stock products found.');
  }

  /**
   * Provides the in-stock Product having a promotion.
   *
   * @param string $type
   *   Promotion types like groupn, groupn_disc etc.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects to PDP page if found else redirects to 404 page.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  public function inStockProductWithPromo(string $type): RedirectResponse {
    // ACM API to fetch all promotion details.
    $promotions = $this->apiWrapper->getPromotions('cart');
    $promotions = is_array($promotions) ? $promotions : [];
    $promo_types = array_column($promotions, 'action');

    // If no promotion available of given type.
    if (empty($promotions) || !in_array($type, $promo_types)) {
      throw new BadRequestHttpException('No in-stock products found with given promotion type.');
    }

    // List of products having the promotion associated with.
    $products = $promotions[array_search($type, $promo_types)]['products'] ?? [];
    foreach ($products as $item) {
      if (!empty($item['product_sku'])) {
        // MDC API to fetch SKU details.
        $product_details = $this->alshayaApi->getSku($item['product_sku']);
        if (!empty($product_details)
          && !empty($product_details['name'])
          && !empty($product_details['custom_attributes'])
          && $product_details['status'] === 1
          && $product_details['extension_attributes']['stock_item']['is_in_stock'] === TRUE
        ) {
          // @todo Build product url from MDC API for V3 using GraphQL, independent of drupal DB.
          $node = $this->skuManager->getDisplayNode($item['product_sku'], FALSE);
          if ($node instanceof NodeInterface && $this->alshayaBehat->isEntityPageLoading($node->toUrl()->toString())) {
            break;
          }
          else {
            $node = NULL;
            throw new BadRequestHttpException('Available product not found in Drupal Application. Probable sync issue.');
          }
        }
      }
    }
    if (!empty($node)) {
      // Redirect to the PDP.
      return new RedirectResponse($node->toUrl()->toString());
    }

    throw new BadRequestHttpException('No in-stock products found with given promotion type.');
  }

}
