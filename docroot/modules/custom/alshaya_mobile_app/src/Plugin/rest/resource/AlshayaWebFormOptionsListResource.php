<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to get dropdown/options list configured in webforms.
 *
 * @RestResource(
 *   id = "webform_options_list",
 *   label = @Translation("Webform options list"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/webform-options-list/{form_id}"
 *   }
 * )
 */
class AlshayaWebFormOptionsListResource extends ResourceBase {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaWebFormOptionsListResource constructor.
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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
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
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param string $form_id
   *   Form id.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing available options from webform fields..
   */
  public function get(string $form_id) {
    $config = $this->configFactory->get('webform.webform.' . $form_id);

    if (empty($config->get('elements'))) {
      throw (new NotFoundHttpException());
    }

    $data = $this->getWebformOptionsList($config);
    $response = new ResourceResponse($data);

    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheTags($config->getCacheTags());
    $cacheableMetadata->addCacheContexts($config->getCacheContexts());
    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

  /**
   * Get the list of field options based on avaiable form id.
   *
   * @param object $config
   *   Config object.
   *
   * @return array
   *   Returns available options from webform fields.
   */
  private function getWebformOptionsList($config) {
    $data = [];
    if ($config->get('id') === 'alshaya_contact') {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      if ($langcode === 'ar') {
        $config = $this->languageManager->getLanguageConfigOverride($langcode, 'webform.webform.' . $config->get('id'));
      }
      $field_elements = Yaml::parse($config->get('elements'));

      foreach ($field_elements as $key => $value) {
        if (!empty($value['#options'])) {
          if (in_array($key, ['reason1', 'reason2', 'reason3', 'reason4'])) {
            $data['reasons'][$key] = $value['#options'];
          }
          else {
            $data[$key] = $value['#options'];
          }
        }
      }
    }

    return $data;
  }

}
