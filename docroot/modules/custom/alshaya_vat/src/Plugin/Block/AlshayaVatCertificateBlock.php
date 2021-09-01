<?php

namespace Drupal\alshaya_vat\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_vat\VatCertificateManager;
use Drupal\Core\Cache\Cache;

/**
 * Provides vat certificate block to be placed in a footer region.
 *
 * @Block(
 *   id = "alshaya_vat_certificate_block",
 *   admin_label = @Translation("Alshaya Vat Certificate Block")
 * )
 */
class AlshayaVatCertificateBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The vat certificate manager.
   *
   * @var \Drupal\alshaya_vat\VatCertificateManager
   */
  protected $vatDataManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VatCertificateManager $vat_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->vatDataManager = $vat_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('alshaya_vat.vatCertificate'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $vat_data = $this->vatDataManager->getVatData();
    // Early return.
    if (empty($vat_data)) {
      return [];
    }

    return [
      '#theme' => 'alshaya_vat_certificate_block',
      '#link_text' => $vat_data['number'],
      '#vat_text' => $vat_data['text'],
      '#vat_url' => '/' . $vat_data['langcode'] . '/vat-certificate',
      '#attached' => [
        'library' => [
          'alshaya_white_label/alshaya-vat-certificate',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = Cache::mergeTags(parent::getCacheTags(), ['alshaya_vat_certificate']);

    return $tags;
  }

}
