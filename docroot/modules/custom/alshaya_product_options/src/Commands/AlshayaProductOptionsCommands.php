<?php

namespace Drupal\alshaya_product_options\Commands;

use Drupal\alshaya_product_options\ProductOptionsHelper;
use Drupal\Core\Language\LanguageManagerInterface;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaProductOptionsCommands.
 *
 * @package Drupal\alshaya_product_options\Commands
 */
class AlshayaProductOptionsCommands extends DrushCommands {

  /**
   * Product Options Helper.
   *
   * @var \Drupal\alshaya_product_options\ProductOptionsHelper
   */
  private $productOptionshelper;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * AlshayaProductOptionsCommands constructor.
   *
   * @param \Drupal\alshaya_product_options\ProductOptionsHelper $productOptionsHelper
   *   Product Options Helper.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language helper.
   */
  public function __construct(ProductOptionsHelper $productOptionsHelper,
                              LanguageManagerInterface $languageManager) {
    $this->productOptionshelper = $productOptionsHelper;
    $this->languageManager = $languageManager;
  }

  /**
   * Sync options for all product attributes.
   *
   * @command alshaya_product_options:sync-options
   *
   * @aliases aacspo,sync-options,alshaya-sync-commerce-product-options
   *
   * @usage drush sync-options
   *   Sync options for all product attributes.
   */
  public function syncProductOptions() {
    $this->output->writeln('Alshaya - Synchronizing all commerce product options, please wait...');
    $this->productOptionshelper->synchronizeProductOptions();
    $this->output->writeln('Sync completed.');
  }

  /**
   * Replace original command provided by acq_sku.
   *
   * @hook replace-command acq_sku:sync-product-options
   */
  public function syncProductOptionsReplaceCommand() {
    $this->syncProductOptions();
  }

  /**
   * Sync options for particular product attribute.
   *
   * @param string $attribute_code
   *   Attribute code to sync options for.
   *
   * @command alshaya_product_options:sync-option
   *
   * @aliases sync-option,alshaya-sync-commerce-product-option
   *
   * @usage drush sync-option actual_color_label_code
   *   Sync options for particular product attribute.
   */
  public function syncOptionForAttribute($attribute_code) {
    $this->output->writeln(dt('Alshaya - Synchronizing commerce product options for @attribute_code, please wait...', [
      '@attribute_code' => $attribute_code,
    ]));

    $languages = $this->languageManager->getLanguages();

    foreach ($languages as $langcode => $language) {
      $this->productOptionshelper->syncProductOption($attribute_code, $langcode);
    }

    $this->output->writeln('Sync completed.');
  }

}
