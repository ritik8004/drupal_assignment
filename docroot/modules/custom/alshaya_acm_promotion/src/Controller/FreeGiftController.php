<?php

namespace Drupal\alshaya_acm_promotion\Controller;

use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_acm_product\SkuImagesManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_acm_promotion\AlshayaPromotionsManager;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class FreeGiftController.
 */
class FreeGiftController extends ControllerBase {

  /**
   * Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * SKU Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Images Manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuImagesManager
   */
  protected $imagesManager;

  /**
   * Promotions Manager.
   *
   * @var \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager
   */
  protected $promotionsManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('alshaya_acm_product.sku_images_manager'),
      $container->get('alshaya_acm_promotion.manager')
    );
  }

  /**
   * PromotionController constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU Manager.
   * @param \Drupal\alshaya_acm_product\SkuImagesManager $images_manager
   *   Images Manager.
   * @param \Drupal\alshaya_acm_promotion\AlshayaPromotionsManager $promotions_manager
   *   Promotions Manager.
   */
  public function __construct(EntityRepositoryInterface $entity_repository,
                              SkuManager $sku_manager,
                              SkuImagesManager $images_manager,
                              AlshayaPromotionsManager $promotions_manager) {
    $this->entityRepository = $entity_repository;
    $this->skuManager = $sku_manager;
    $this->imagesManager = $images_manager;
    $this->promotionsManager = $promotions_manager;
  }

  /**
   * Title callback for the modal.
   */
  public function titleCallback($acq_sku) {
    $sku = $this->skuManager->loadSkuById((int) $acq_sku);

    if (!($sku instanceof SKUInterface)) {
      throw new NotFoundHttpException();
    }

    return $sku->get('name')->getString();
  }

  /**
   * Page callback for the modal.
   */
  public function viewProduct(Request $request, $acq_sku, $js) {
    $sku = $this->skuManager->loadSkuById((int) $acq_sku);

    if (!($sku instanceof SKUInterface)) {
      throw new NotFoundHttpException();
    }

    if ($js === 'ajax') {
      $view_builder = $this->entityTypeManager()->getViewBuilder($sku->getEntityTypeId());
      $build = $view_builder->view($sku, 'free_gift');

      if ($request->query->get('back')) {
        $response = new AjaxResponse();
        $response->addCommand(new HtmlCommand('#drupal-modal', $build));
        return $response;
      }
      else {
        return $build;
      }

    }

    $response = new RedirectResponse(Url::fromRoute('entity.acq_sku.canonical', ['acq_sku' => $sku->id()])->toString());
    $response->send();
    exit;
  }
}
