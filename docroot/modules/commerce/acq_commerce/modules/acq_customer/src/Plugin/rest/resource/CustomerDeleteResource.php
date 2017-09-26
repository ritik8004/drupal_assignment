<?php

namespace Drupal\acq_customer\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Class CustomerDeleteResource.
 *
 * @package Drupal\acq_customer\Plugin
 *
 * @ingroup acq_customer
 *
 * @RestResource(
 *   id = "acq_customer_delete",
 *   label = @Translation("Acquia Commerce Customer Delete"),
 *   uri_paths = {
 *     "canonical" = "/customer/{customer_email}/delete",
 *     "https://www.drupal.org/link-relations/create" = "/customer/{customer_email}/delete"
 *   }
 * )
 */
class CustomerDeleteResource extends ResourceBase {

  /**
   * Delete.
   *
   * Handle Conductor deleting a customer.
   *
   * @param string $customer_email
   *   Customer email id.
   *
   * @return \Drupal\rest\ResourceResponse
   *   HTTP Response.
   */
  public function delete($customer_email) {
    $response = [];

    /* @var \Drupal\user\Entity\User $user */
    $user = user_load_by_mail($customer_email);
    if ($user) {
      try {
        $user->delete();
        $response['success'] = TRUE;
      }
      catch (\Exception $e) {
        $response['success'] = FALSE;
        $response['error_message'] = $e->getMessage();
      }
    }
    else {
      $response['success'] = FALSE;
      $response['error_message'] = $this->t('Customer with @email_id could not found', ['@email_id' => $customer_email]);
    }

    return (new ResourceResponse($response));
  }

}
