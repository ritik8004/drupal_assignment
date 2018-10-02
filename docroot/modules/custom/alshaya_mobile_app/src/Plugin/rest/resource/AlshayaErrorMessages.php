<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\locale\StringStorageInterface;

/**
 * Provides a resource to get alshaya error messages.
 *
 * @RestResource(
 *   id = "alshaya_error_messages",
 *   label = @Translation("Alshaya Error Messages"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/alshaya_error_messages"
 *   }
 * )
 */
class AlshayaErrorMessages extends ResourceBase {

  /**
   * Error message context to look for.
   */
  const CONTEXT_ERROR_MESSAGE = 'alshaya_error_message';

  /**
   * Context and key separator used in table.
   */
  const CONTEXT_KEY_SEPARATOR = '|';

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Localized strings storage..
   *
   * @var \Drupal\locale\StringStorageInterface
   */
  protected $localeStorage;

  /**
   * AlshayaErrorMessages constructor.
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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\locale\StringStorageInterface $locale_storage
   *   Localized strings storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, LanguageManagerInterface $language_manager, StringStorageInterface $locale_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->localeStorage = $locale_storage;
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
      $container->get('language_manager'),
      $container->get('locale.storage')
    );
  }

  /**
   * Get the data fields to look into based on current language.
   *
   * @return Drupal\locale\StringInterface[]
   *   Return the array of StringInterface objects.
   */
  protected function getErrorStrings() {
    $options = ['filters' => ['context' => self::CONTEXT_ERROR_MESSAGE]];
    if ($this->languageManager->getCurrentLanguage()->getId() == $this->languageManager->getDefaultLanguage()->getId()) {
      return $this->localeStorage->getStrings([], $options);
    }
    else {
      return $this->localeStorage->getTranslations([], $options);
    }
  }

  /**
   * Responds to GET requests.
   *
   * Returns available error messages.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing key and value of error messages.
   */
  public function get() {
    $response_data = [];
    $error_messages = $this->getErrorStrings();
    foreach ($error_messages as $string) {
      list(, $error_message_key) = explode(self::CONTEXT_KEY_SEPARATOR, $string->getValues(['context'])['context']);
      // Prepare response data.
      $response_data[] = [
        'machine_name' => $error_message_key,
        'message' => $string->getString(),
      ];
    }

    // As there's not caching information vailable with StringInterface object,
    // We are not caching $response_data.
    return new ModifiedResourceResponse($response_data);
  }

}
