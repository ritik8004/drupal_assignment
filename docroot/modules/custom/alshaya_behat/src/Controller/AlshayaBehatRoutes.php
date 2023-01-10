<?php

namespace Drupal\alshaya_behat\Controller;

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
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): AlshayaBehatRoutes|static {
    return new static(
      $container->get('request_stack'),
      $container->get('alshaya_behat.helper')
    );
  }

  /**
   * Constructor for AlshayaBehatRoutes.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\alshaya_behat\Service\AlshayaBehatHelper $alshaya_behat
   *   Alshaya behat.
   */
  public function __construct(
    RequestStack $request_stack,
    AlshayaBehatHelper $alshaya_behat
  ) {
    $this->request = $request_stack->getCurrentRequest();
    $this->alshayaBehat = $alshaya_behat;
  }

  /**
   * Access checker for behat requests.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function checkAccess(): AccessResult {
    return AccessResult::allowedIf(Settings::get('is_behat_request'));
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
    $result = $this->alshayaBehat->getWorkingProductWithPromo($type);
    if ($result instanceof NodeInterface) {
      // Redirect to the node page.
      return new RedirectResponse($result->toUrl()->toString());
    }

    // If no in-stock product found with promotion, then redirect to 400 page.
    throw new BadRequestHttpException(is_string($result) ? $result : 'No in-stock products found with given promotion type.');
  }

}
