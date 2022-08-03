<?php

namespace Drupal\acq_customer\Plugin\rest\resource;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;

/**
 * Class Customer Delete Resource.
 *
 * @package Drupal\acq_customer\Plugin
 *
 * @ingroup acq_customer
 *
 * @RestResource(
 *   id = "acq_customer_delete",
 *   label = @Translation("Acquia Commerce Customer Delete"),
 *   uri_paths = {
 *     "create" = "/customer/delete"
 *   }
 * )
 */
class CustomerDeleteResource extends ResourceBase {

  /**
   * Post.
   *
   * Handle Conductor deleting a customer.
   *
   * @param array $data
   *   Post data.
   *
   * @return \Drupal\rest\ResourceResponse
   *   HTTP Response.
   */
  public function post(array $data) {
    $response = [];
    // If 'email' key is not available.
    if (!$data['email']) {
      $this->logger->error('Invalid data to delete customer.');
      $response['success'] = (bool) (FALSE);
      return (new ResourceResponse($response));
    }

    $email = $data['email'];

    /** @var \Drupal\user\Entity\User $user */
    $user = user_load_by_mail($email);

    // If there is user with given email.
    if ($user) {
      try {
        $user->delete();
        $this->logger->notice('Deleted user with uid %id and email %email.', [
          '%id' => $user->id(),
          '%email' => $email,
        ]);
      }
      catch (EntityStorageException $e) {
        $this->logger->error($e->getMessage());
      }
    }
    else {
      $this->logger->warning('User with email %email doesn\'t exist.', ['%email' => $email]);
    }

    // For exception or missing user we have added entries in logs.
    // We don't want ACM to try again for this.
    $response['success'] = TRUE;
    return (new ResourceResponse($response));
  }

}
