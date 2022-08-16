<?php

namespace Drupal\alshaya_feed\Commands;

use Drush\Commands\DrushCommands;
use Drupal\alshaya_feed\AlshayaProductDeltaFeedHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareInterface;
use Consolidation\SiteAlias\SiteAliasManagerAwareTrait;
use Drupal\acq_sku\Entity\SKU;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Alshaya product delta feed command.
 *
 * @package Drupal\alshaya_feed\Commands
 */
class AlshayaProductDeltaFeedCommands extends DrushCommands implements SiteAliasManagerAwareInterface {

  use SiteAliasManagerAwareTrait;

  /**
   * Product Delta Feed Helper.
   *
   * @var Drupal\alshaya_feed\AlshayaProductDeltaFeedHelper
   */
  protected $productDeltaFeedHelper;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger Channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $drupalLogger;

  /**
   * AlshayaProductDeltaFeedCommands constructor.
   *
   * @param \Drupal\alshaya_feed\AlshayaProductDeltaFeedHelper $product_delta_feed_helper
   *   Product Feed Helper.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Channel Factory service.
   */
  public function __construct(
    AlshayaProductDeltaFeedHelper $product_delta_feed_helper,
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactoryInterface $loggerChannelFactory
  ) {
    $this->productDeltaFeedHelper = $product_delta_feed_helper;
    $this->configFactory = $config_factory;
    $this->drupalLogger = $loggerChannelFactory->get('alshaya_feed');
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
   * @option sku
   *   The sku to delete.
   *
   * @usage drush manage-oos-product --action=read
   *   Displays list of OOS product SKUs.
   * @usage drush manage-oos-product --action=delete sku=123
   *   Deletes the given sku from list of OOS product SKUs.
   */
  public function manageOosProduct(array $options = [
    'action' => 'read',
    'sku' => '',
    'dry-run' => FALSE,
  ]) {
    $action = $options['action'];

    // Based on action, display oos skus or delete.
    if ($action === 'read') {
      $oos_skus = $this->productDeltaFeedHelper->getOosProductSkus();

      foreach ($oos_skus as $sku) {
        $this->io()->writeln($sku);
      }
      return;
    }

    // For delete action.
    if ($action === 'delete') {
      // Return if given sku to delete is empty.
      if (empty($options['sku'])) {
        $this->io()->error('SKU is empty for action delete.');
        return;
      }

      $dry_run = (bool) $options['dry-run'];

      if (!$dry_run) {
        $this->productDeltaFeedHelper->deleteOosProductSku($options['sku']);
      }
    }
  }

  /**
   * Checks if given SKU is OOS.
   *
   * @param array $options
   *   (optional) An array of options.
   *
   * @command alshaya_feed:verify_oos_sku
   *
   * @aliases verify-oos-sku
   *
   * @option skus
   *   The sku to verify.
   *
   * @usage drush verify-oos-sku --sku=123
   *   Verifies the given sku is still OOS or not.
   */
  public function verifyOosSku(array $options = ['sku' => '']) {
    $sku_to_verify = $options['sku'];

    // Return if sku is empty.
    if (empty($sku_to_verify)) {
      $this->io()->error('SKU is empty.');
      return;
    }

    // Try to load sku to verify if it is OOS or not.
    $entity = SKU::loadFromSku($sku_to_verify);

    if (!($entity instanceof SKU)) {
      $this->io()->writeln($sku_to_verify);
    }
  }

  /**
   * Delete OOS products from DY product delta feed.
   *
   * @param array $options
   *   (optional) An array of options.
   *
   * @command alshaya_feed:delete_oos_product_from_dy_delta_feed
   *
   * @aliases delete-oos-product-from-dy-delta-feed
   *
   * @usage drush delete-oos-product-from-dy-delta-feed
   *   Deletes oos product across markets of a brand from DY product delta feed.
   */
  public function deleteOosProductFromDyProductDeltaFeed(array $options = ['dry-run' => FALSE]) {
    if (!$this->configFactory->get('alshaya_brand.settings')->get('brand_main_site')) {
      $this->drupalLogger->notice('Skipping DY product delta feed cleanup as not main site of the brand.');
      return;
    }

    $dry_run = (bool) $options['dry-run'];
    $domains = $this->getBrandDomains();

    if (empty($domains)) {
      $this->drupalLogger->error('Failed to fetch domains for DY product delta feed cleanup.');
      return;
    }

    // Get the list of oos product skus for each domain.
    $command = 'drush -l %s manage-oos-product';
    $oos_products = $this->getOosProductSkus($domains, $command);

    $oos_products_merged = array_unique(call_user_func_array('array_merge', $oos_products));

    if (empty($oos_products_merged)) {
      $this->drupalLogger->notice('No OOS products found across markets.');
      return;
    }

    // Process OOS SKUs one at a time.
    foreach ($oos_products_merged as $sku) {
      $this->processOosProductSku($sku, $dry_run, $domains);
    }
  }

  /**
   * Helper function to verify if SKU is OOS on every market and delete the SKU.
   */
  private function processOosProductSku($sku, $dry_run, $domains) {
    $is_sku_oos = NULL;
    foreach ($domains as $domain) {
      $current_domain = $domain[1];

      // Verify if SKU is still OOS or not.
      $command = sprintf('drush -l %s verify-oos-sku --sku=%s', $current_domain, $sku);
      $get_oos_products = $this->processManager()->process($command);
      $get_oos_products->mustRun();
      $data = $get_oos_products->getOutput();
      $is_sku_oos = (!empty($data) && (trim($data) === $sku));

      // Skip the SKU if its not OOS even on one domain.
      if (!$is_sku_oos) {
        $this->drupalLogger->notice('Skipping SKU ' . $sku . ' as it is available on ' . $current_domain);
        break;
      }

      $this->io()->writeln('Verified that SKU ' . $sku . ' is OOS on ' . $current_domain);
    }

    // Delete the SKU from DY delta feed and
    // from list of OOS SKUs on each domain.
    if ($is_sku_oos) {
      $this->drupalLogger->notice('Deleting OOS SKU ' . $sku . ' from all domains.');
      if (!$dry_run) {
        $this->productDeltaFeedHelper->deleteFromFeed($sku);
      }
      // Remove sku from oos sku list for each market/domain.
      $this->removeOosProductSkus($domains, $dry_run, $sku);
    }
  }

  /**
   * Helper function to get OOS product skus.
   *
   * @return array
   *   List of OOS product skus.
   */
  private function getOosProductSkus($domains, $command) {
    $oos_products = [];

    foreach ($domains as $domain) {
      $current_domain = $domain[1];
      $oos_products[$current_domain] = [];
      $generated_command = sprintf($command, $current_domain);
      $get_oos_products = $this->processManager()->process($generated_command);
      $get_oos_products->mustRun();
      $data = $get_oos_products->getOutput();
      $data = !empty($data) ? array_filter(explode(PHP_EOL, $data)) : [];
      $oos_products[$current_domain] = $data;
    }

    return $oos_products;
  }

  /**
   * Helper function to remove OOS product skus from table.
   */
  private function removeOosProductSkus($domains, $dry_run, $sku) {
    foreach ($domains as $domain) {
      $current_domain = $domain[1];
      $command = sprintf('drush -l %s manage-oos-product --action=%s --sku=%s', $current_domain, 'delete', $sku);

      if ($dry_run) {
        $command .= ' --dry-run';
      }

      $delete_oos_products = $this->processManager()->process($command);
      $delete_oos_products->mustRun();
    }
    $this->drupalLogger->notice('Cleaned up OOS SKU ' . $sku . ' on all domains.');
  }

  /**
   * Helper function to get all domains of a brand.
   *
   * @todo Move this to some helper so that same method can be
   * used here and in AlshayaBrandAssetsCommands.
   *
   * @return array
   *   List of domains.
   */
  private function getBrandDomains() {
    // phpcs:ignore
    global $acsf_site_code;

    $selfRecord = $this->siteAliasManager()->getSelf();

    /** @var \Consolidation\SiteProcess\SiteProcess $atl */
    $atl = $this->processManager()->drush($selfRecord, 'acsf-tools-list', [], ['fields' => 'domains']);
    $atl->mustRun();
    $data = $atl->getOutput();
    $data = explode(PHP_EOL, $data);

    $yaml_data = '';
    $start_reading = FALSE;
    foreach ($data as $line) {
      if ((strpos($line, (string) $acsf_site_code) > -1) && (!str_starts_with($line, ' '))) {
        $start_reading = TRUE;
        $yaml_data .= $line . ':' . PHP_EOL;
        continue;
      }
      if ($start_reading) {
        if (!str_starts_with($line, ' ')) {
          break;
        }
        if (strpos($line, 'domains') > -1) {
          continue;
        }
        $yaml_data .= $line . PHP_EOL;
      }
    }

    $domains = Yaml::parse($yaml_data);

    return $domains;
  }

}
