<?php

namespace Drupal\alshaya_promo_panel\Commands;

use Drupal\block\BlockInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Class Alshaya Promo Panel Commands.
 *
 * @package Drupal\alshaya_promo_panel\Commands
 */
class AlshayaPromoPanelCommands extends DrushCommands {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * AlshayaPromoPanelCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
  }

  /**
   * Config promo panel block and path.
   *
   * @param string $blocks
   *   Block with url value. i.e. alshaya_promo_panel:/todays-offer.
   *
   * @throws \Exception
   *
   * @command alshaya_promo_panel:configure
   *
   * @aliases apps,alshaya-promo-panel-set
   *
   * @usage drush apps alshaya_promo_panel:/todays-offer
   *   Single input to set block and url for mobile.
   * @usage drush apps alshaya_promo_panel:/todays-offer,alshaya_custom:/about
   *   Comma separated input to set multiple blocks.
   */
  public function configPromoPanel($blocks) {
    if (empty($blocks)) {
      throw new \Exception(dt('blocks is required argument. i.e. drush apps alshaya_promo_panel:/todays-offer,alshaya_custom:/about'));
    }

    // Clean input and parse comma-separated input items into array.
    $block_names = explode(',', $blocks);
    $block_storage = $this->entityTypeManager->getStorage('block');
    $promo_panel_blocks = [];
    array_map(function ($block_name) use (&$promo_panel_blocks, $block_storage) {
      [$block_machine_name, $mobile_path] = explode(':', $block_name);
      $block_results = $block_storage->loadByProperties(['id' => $block_machine_name]);
      $block = reset($block_results);
      if ($block instanceof BlockInterface) {
        $promo_panel_blocks[$block_machine_name] = [
          'mobile_path' => $mobile_path,
          'plugin_id' => $block->getPluginId(),
        ];
      }
    }, $block_names);

    $config = $this->configFactory->getEditable('alshaya_promo_panel.settings');
    $config->set('promo_panel_blocks', $promo_panel_blocks);
    $config->save();

    // Enable the blocks if disabled.
    $storage = $this->entityTypeManager->getStorage('block');
    $query = $storage->getQuery()->condition('id', array_keys($promo_panel_blocks), 'IN');
    $blocks = $storage->loadMultiple($query->execute());
    foreach ($blocks as $block) {
      if ($block->status()) {
        continue;
      }
      $block->setStatus(TRUE);
      $block->save();
    }

    $this->output->writeln(dt('Successfully updated promo panel config.'));
  }

}
