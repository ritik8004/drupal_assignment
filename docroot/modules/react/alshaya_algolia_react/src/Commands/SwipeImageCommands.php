<?php

namespace Drupal\alshaya_algolia_react\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drush\Commands\DrushCommands;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class SwipeImageCommands.
 *
 * Enable/Disable Swipe image for PLP/SRP.
 *
 * @package Drupal\alshaya_algolia_react\Commands
 */
class SwipeImageCommands extends DrushCommands {

  /**
   * The drupalLogger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $drupalLogger;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * SwipeImageCommands constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Factory.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerChannelFactory, ConfigFactoryInterface $config_factory) {
    $this->drupalLogger = $loggerChannelFactory->get('SwipeImageCommands');
    $this->configFactory = $config_factory;
  }

  /**
   * Swipe Image enable for plp/srp.
   *
   * @command alshaya_swipe_image_enable
   *
   * @option enable
   *   Status of swipe image. (enable the swipe image for plp/srp.)
   *
   * @aliases alshaya-swipe-image-enable
   *
   * @usage drush alshaya-swipe-image-enable
   *   Enable the Swipe image feature in PLP/SRP.
   */
  public function enable() {
    // Get image_thumb_gallery.
    $check_enable_feature = $this->configFactory->get('alshaya_acm_product.display_settings');

    // Check this feature enable.
    if (!$check_enable_feature->get('image_thumb_gallery')) {
      // Set Swipe Image config.
      $configSwipeImage = $this->configFactory->getEditable('alshaya_algolia_react.swipe_image');
      $configSwipeImage->set('enable_swipe_image_mobile', TRUE);
      $configSwipeImage->set('slide_effect', 'fade');
      $configSwipeImage->set('show_pdp_images_on_listing_pages', TRUE);
      $configSwipeImage->save();

      // Set Display Setting config.
      $configDisplaySetting = $this->configFactory->getEditable('alshaya_acm_product.display_settings');
      $configDisplaySetting->set('image_thumb_gallery', TRUE);
      $configDisplaySetting->set('gallery_show_hover_image', FALSE);
      $configDisplaySetting->save();

      $this->drupalLogger->notice('Swipe images feature enabled successfully.');
    }
    else {
      $this->drupalLogger->warning('Swipe images feature is already enabled.');
    }
  }

  /**
   * Swipe Image disable for plp/srp.
   *
   * @command alshaya_swipe_image_disable
   *
   * @option disable
   *   Status of swipe image. (disable the swipe image for plp/srp.)
   *
   * @aliases alshaya-swipe-image-disable
   *
   * @usage drush alshaya-swipe-image-disable
   *   Disable the Swipe image feature in PLP/SRP.
   */
  public function disable() {
    // Get image_thumb_gallery.
    $check_enable_feature = $this->configFactory->get('alshaya_acm_product.display_settings');

    // Check this feature disable.
    if ($check_enable_feature->get('image_thumb_gallery')) {
      // Set Swipe Image config.
      $configSwipeImage = $this->configFactory->getEditable('alshaya_algolia_react.swipe_image');
      $configSwipeImage->set('enable_swipe_image_mobile', FALSE);
      $configSwipeImage->set('slide_effect', 'slide');
      $configSwipeImage->set('show_pdp_images_on_listing_pages', FALSE);
      $configSwipeImage->save();

      // Set Display Setting config.
      $configDisplaySetting = $this->configFactory->getEditable('alshaya_acm_product.display_settings');
      $configDisplaySetting->set('image_thumb_gallery', FALSE);
      $configDisplaySetting->set('gallery_show_hover_image', TRUE);
      $configDisplaySetting->save();

      $this->drupalLogger->notice('Swipe images feature disabled successfully.');

    }
    else {
      $this->drupalLogger->warning('Swipe images feature is already disabled.');
    }
  }

}
