<?php

namespace Drupal\alshaya_acm_product;

use Drupal\Core\Config\ConfigFactory;

/**
 * Class containing general helper methods to enable same day/express delivery.
 */
class DeliveryOptionsHelper {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Delivery Options Helper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Config factory manager.
   */
  public function __construct(ConfigFactory $configFactory) {
    $this->configFactory = $configFactory;

    $this->deliveryOptionsSettings = $this->configFactory->get('alshaya_spc.express_delivery');
  }

  /**
   * Checks if Same Day or Express Delivery feature enabled.
   *
   * @return bool
   *   TRUE if either same day or express delivery is enabled.
   */
  public function ifSddEdFeatureEnabled() {
    if ($this->deliveryOptionsSettings->get('same_day_delivery_status') || $this->deliveryOptionsSettings->get('express_delivery_status')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Gets same day delivery feature status.
   *
   * @return bool
   *   TRUE if same day delivery is enabled.
   */
  public function getSameDayDeliveryStatus() {
    return $this->deliveryOptionsSettings->get('same_day_delivery_status');
  }

  /**
   * Gets express delivery feature status.
   *
   * @return bool
   *   TRUE if express day delivery is enabled.
   */
  public function getExpressDeliveryStatus() {
    return $this->deliveryOptionsSettings->get('express_delivery_status');
  }

}
