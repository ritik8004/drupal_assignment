<?php

namespace Drupal\alshaya_egift_card\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_egift_card\Helper\EgiftCardHelper;

/**
 * Alshaya Top Up Controller.
 */
class AlshayaTopUpController extends ControllerBase {

  /**
   * EgiftCardHelper.
   *
   * @var \Drupal\alshaya_egift_card\Helper\EgiftCardHelper
   */
  protected $egiftCardHelper;

  /**
   * The Alshaya Top Up Controller constructor.
   *
   * @param \Drupal\alshaya_egift_card\Helper\EgiftCardHelper $egiftCardHelper
   *   EgiftCardHelper.
   */
  public function __construct(EgiftCardHelper $egiftCardHelper) {
    $this->egiftCardHelper = $egiftCardHelper;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_egift_card.egift_card_helper'),
    );
  }

  /**
   * E-Gift top-up card controller.
   */
  public function topupcard() {
    $eGift_status = $this->egiftCardHelper->isEgiftCardEnabled();
    if (!$eGift_status) {
      $response = new LocalRedirectResponse(Url::fromRoute('<front>')->toString());
      $response->send();
    }
    return [
      '#theme' => 'egift_topup_page',
      '#terms_block_content' => $this->egiftCardHelper->getTermsAndConditionText(),
      '#attached' => [
        'library' => [
          'alshaya_egift_card/alshaya_egift_topup_purchase',
          'alshaya_white_label/egift-topup-page',
        ],
      ],
    ];
  }

}
