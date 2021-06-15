<?php

namespace Drupal\alshaya_feed\Commands;

use Drush\Commands\DrushCommands;
use Drupal\alshaya_feed\AlshayaProductDeltaFeedHelper;

/**
 * Alshaya product delta feed command.
 *
 * @package Drupal\alshaya_feed\Commands
 */
class AlshayaProductDeltaFeedCommands extends DrushCommands {

  /**
   * Product Delta Feed Helper.
   *
   * @var Drupal\alshaya_feed\AlshayaProductDeltaFeedHelper
   */
  protected $productDeltaFeedHelper;

  /**
   * AlshayaProductDeltaFeedCommands constructor.
   *
   * @param \Drupal\alshaya_feed\AlshayaProductDeltaFeedHelper $product_delta_feed_helper
   *   Product Feed Helper.
   */
  public function __construct(
    AlshayaProductDeltaFeedHelper $product_delta_feed_helper
  ) {
    $this->dyProductDeltaFeedApiWrapper = $product_delta_feed_helper;
  }

  /**
   * Read/Delete OOS products from table.
   *
   * @param array $options
   *   (optional) An array of options.
   *
   * @command alshaya_feed:manage_oos_product
   *
   * @aliases manage-oos-product
   *
   * @option action
   *   The action to perform - read or delete.
   * @option skus
   *   The list of skus to delete.
   *
   * @usage drush manage-oos-product --action=read
   *   Displays list of OOS product SKUs.
   * @usage drush manage-oos-product --action=delete skus=123,234
   *   Deletes the given sku from list of OOS product SKUs.
   */
  public function manageOosProduct(array $options = [
    'action' => 'read',
    'skus' => '',
    'dry-run' => FALSE,
  ]) {
    $action = $options['action'];
    $skus_to_delete = $options['skus'] ? explode(',', $options['skus']) : '';

    // Return if action is delete and skus is empty.
    if ($action === 'delete' && empty($skus_to_delete)) {
      $this->io()->writeln('SKU list empty for action delete.');
      return;
    }

    $dry_run = (bool) $options['dry-run'];
    $oos_skus = $this->dyProductDeltaFeedApiWrapper->getOosProductSkus();

    // Based on action, display oos skus or delete.
    foreach ($oos_skus as $sku) {
      if ($action === 'read') {
        $this->io()->writeln($sku);
        continue;
      }

      if ($action === 'delete' && in_array($sku, $skus_to_delete)) {
        $this->io()->writeln('Delete SKU ' . $sku . ' from table.');

        if (!$dry_run) {
          $this->dyProductDeltaFeedApiWrapper->deleteOosProductSku($sku);
        }
      }
    }
  }

}
