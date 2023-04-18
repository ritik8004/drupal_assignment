<?php

namespace Drupal\alshaya_algolia_react\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drush\Commands\DrushCommands;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class SwipeImage Commands.
 *
 * Enable/Disable Swipe image for PLP/SRP.
 *
 * @package Drupal\alshaya_algolia_react\Commands
 */
class SwipeImageCommands extends DrushCommands {

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

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
    parent::__construct();
    $this->logger = $loggerChannelFactory->get('alshaya_algolia_react');
    $this->configFactory = $config_factory;
  }

  /**
   * Swipe Image enable for plp/srp.
   *
   * @command alshaya_swipe_image
   *
   * @option enable
   *   Status of block. ('enable' to enable the swipe image for plp/srp.)
   * @option disable
   *   Status of block. ('disable' to enable the swipe image for plp/srp.)
   *
   * @aliases alshaya-swipe-image
   *
   * @usage drush alshaya-swipe-image --enable  --disable
   *   Disable algolia for plp and activate db.
   */
  public function toggleSwipeImage($options = [
    'enable' => FALSE,
    'disable' => FALSE,
  ]) {
    // Get enable_swipe_image_mobile.
    $swipe_image_settings = $this->configFactory->get('alshaya_algolia_react.swipe_image');

    // Enabled the swipe image features.
    if ($options['enable']) {
      $config = $this->configFactory->getEditable('alshaya_algolia_react.swipe_image');
      $config->set('enable_swipe_image_mobile', TRUE);
      $config->set('no_of_image_scroll', 6);
      $config->set('slide_effect_fade', 'slide');
      $config->set('image_slide_timing', 2);

      if (!$swipe_image_settings->get('enable_swipe_image_mobile') &&  $config->save()) {
        $this->logger->success('Swipe images feature enabled successfully.');
      }
      else {
        $this->logger->warning('Swipe images feature already enabled.');
      }
    }

    // Disabled the Swipe Image feature.
    if ($options['disable']) {
      if ($swipe_image_settings->get('enable_swipe_image_mobile') && $this->configFactory->getEditable('alshaya_algolia_react.swipe_image')->delete()) {
        $this->logger->success('Swipe images feature disabled successfully.');
      }
      else {
        $this->logger->warning('Swipe images feature already disabled.');
      }
    }
  }

}
