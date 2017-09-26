<?php

namespace Drupal\acq_customer\Plugin\rest\resource;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('acq_customer')
    );
  }

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
    /* @var \Drupal\user\Entity\User $user */
    $user = user_load_by_mail($customer_email);

    // If there is user with given email.
    if ($user) {
      try {
        $user->delete();
        $this->logger->notice('Deleted user with uid %id and email %email.', ['%id' => $user->id(), '%email' => $customer_email]);

        // DELETE responses have an empty body.
        return new ModifiedResourceResponse(NULL, 204);
      }
      catch (EntityStorageException $e) {
        $this->logger->error($e->getMessage());
        throw new HttpException(500, 'Internal Server Error', $e);
      }
    }
    else {
      $this->logger->warning('User with email %email doesn\'t exist.', ['%email' => $customer_email]);
      // @Todo: Need to determine the correct exception class.
      throw new NotFoundHttpException();
    }
  }

}
