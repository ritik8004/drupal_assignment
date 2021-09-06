<?php

namespace Drupal\alshaya_vat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\alshaya_vat\VatCertificateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Vat certificate controller to display certificate to users.
 */
class VatCertificateController extends ControllerBase {

  /**
   * The vat certificate manager.
   *
   * @var \Drupal\alshaya_vat\VatCertificateManager
   */
  protected $vatDataManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(VatCertificateManager $vat_manager) {
    $this->vatDataManager = $vat_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_vat.vatCertificate')
    );
  }

  /**
   * A vat certificate page.
   */
  public function vatCertificatePage() {
    $vat_data = $this->vatDataManager->getVatData();
    // Early return.
    if (empty($vat_data)) {
      return [];
    }

    return [
      '#theme' => 'alshaya_vat_certificate_page',
      '#url' => $vat_data['url'],
      '#cache' => [
        'tags' => ['alshaya_vat_certificate'],
      ],
    ];

  }

}
