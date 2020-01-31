<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class AlshayaSpcCustomerHelper.
 */
class AlshayaSpcCustomerHelper {

  /**
   * The api wrapper.
   *
   * @var \Drupal\alshaya_spc\Helper\AlshayaSpcApiHelper
   */
  protected $apiWrapper;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * AlshayaSpcCustomerHelper constructor.
   *
   * @param \Drupal\alshaya_spc\Helper\AlshayaSpcApiHelper $api_wrapper
   *   The api wrapper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    AlshayaSpcApiHelper $api_wrapper,
    ModuleHandlerInterface $module_handler
  ) {
    $this->apiWrapper = $api_wrapper;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Helper function to authenticate user from Magento.
   *
   * @param string $mail
   *   Mail.
   * @param string $pass
   *   Password.
   *
   * @return int|mixed|string|null
   *   User id of user if successful or null.
   *
   * @throws \Exception
   */
  public function authenticateCustomer($mail, $pass) {
    global $_alshaya_acm_custom_cart_association_processed;

    try {
      $customer = $this->apiWrapper->authenticateCustomerOnMagento($mail, $pass);

      if (!empty($customer) && !empty($customer['customer_id'])) {
        $_alshaya_acm_custom_cart_association_processed = TRUE;
        $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.utility');

        // Check if user exists in Drupal.
        if ($user = user_load_by_mail($mail)) {
          // Update the data in Drupal to match the values in Magento.
          alshaya_acm_customer_update_user_data($user, $customer);
        }
        // Create user.
        else {
          /** @var \Drupal\user\Entity\User $user */
          $user = alshaya_acm_customer_create_drupal_user($customer);
        }

        return $user->id();
      }
    }
    catch (\Exception $e) {
      // Could be admin user, do nothing except for downtime exception.
      if (acq_commerce_is_exception_api_down_exception($e)) {
        throw $e;
      }
    }

    return NULL;
  }

}
