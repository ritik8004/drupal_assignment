<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\Component\Plugin\PluginBase;

/**
 * COD payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "cashondelivery",
 *   label = @Translation("Cash on Delivery"),
 *   hasForm = false
 * )
 */
class CashOnDelivery extends PluginBase {}
