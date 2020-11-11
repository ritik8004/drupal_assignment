<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_options_list\AlshayaOptionsListHelper;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to get options values.
 *
 * @RestResource(
 *   id = "options_list",
 *   label = @Translation("Options List"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/options-list/{page_url}"
 *   }
 * )
 */
class OptionsListResource extends ResourceBase {

  /**
   * Alshaya Options List Service.
   *
   * @var Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ProductResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshaya_options_service
   *   Alshaya options service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AlshayaOptionsListHelper $alshaya_options_service,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->alshayaOptionsService = $alshaya_options_service;
    $this->moduleHandler = $module_handler;
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
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('alshaya_options_list.alshaya_options_service'),
      $container->get('module_handler')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns response data based on configured attribute code.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing options term data.
   */
  public function get(string $page_url) {
    if (!$this->alshayaOptionsService->optionsPageEnabled()) {
      throw new NotFoundHttpException();
    }

    $response_data = $this->alshayaOptionsService->getOptionsList($page_url);

    $this->moduleHandler->alter('options_list_resource_response', $response_data);

    return new ModifiedResourceResponse($response_data);
  }

}
