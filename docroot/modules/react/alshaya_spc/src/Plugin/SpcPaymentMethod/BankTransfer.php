<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\Component\Plugin\PluginBase;

/**
 * Bank transfer payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "banktransfer",
 *   label = @Translation("Bank Transfer"),
 *   hasForm = false
 * )
 */
class BankTransfer extends PluginBase {}
