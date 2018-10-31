<?php

namespace Drupal\alshaya_acm_customer\Commands;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaCustomerCommands.
 *
 * @package Drupal\alshaya_acm_customer\Commands
 */
class AlshayaCustomerCommands extends DrushCommands {

  /**
   * Conductor Api wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  private $apiWrapper;

  /**
   * AlshayaCustomerCommands constructor.
   *
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $apiWrapper
   *   Conductor API Wrapper.
   */
  public function __construct(APIWrapper $apiWrapper) {
    $this->apiWrapper = $apiWrapper;
  }

  /**
   * Create a user account with the specified name.
   *
   * @param string $firstname
   *   First name of the customer to add.
   * @param string $lastname
   *   Last name of the customer to add.
   * @param string $mail
   *   E-mail of the customer to add.
   * @param string $password
   *   Password of the customer account to add.
   *
   * @command alshaya_acm_customer:create-customer
   *
   * @aliases acccrt,customer-create
   *
   * @usage drush customer-create fname lname mail pass@123
   *   Create a new customer account.
   */
  public function createCustomer($firstname, $lastname, $mail, $password) {
    try {
      $customer = [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $mail,
      ];

      $customer = $this->apiWrapper->updateCustomer($customer, [
        'password' => $password,
      ]);

      $this->output->writeln($customer);
    }
    catch (\Exception $e) {
      $this->output->writeln(dt('Error: Could not create a new user account for the mail @mail.', [
        '@mail' => $mail,
      ]));

      $this->output->writeln(dt('Message: @message', [
        '@message' => $e->getMessage(),
      ]));
    }
  }

}
