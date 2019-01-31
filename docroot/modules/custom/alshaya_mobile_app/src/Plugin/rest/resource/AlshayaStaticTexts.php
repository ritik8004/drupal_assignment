<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\locale\StringStorageInterface;

/**
 * Provides a resource to get Alshaya static texts.
 *
 * @RestResource(
 *   id = "alshaya_static_texts",
 *   label = @Translation("Static texts"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/get-static-texts"
 *   }
 * )
 */
class AlshayaStaticTexts extends ResourceBase {

  /**
   * Static text context to look for.
   */
  const CONTEXT_STATIC_TEXTS = 'alshaya_static_text';

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
   * AlshayaStaticTexts constructor.
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
   * Responds to GET requests.
   *
   * Returns available static texts.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing key and value of static texts.
   */
  public function get() {
    $response_data = [];
    $options = ['filters' => ['context' => self::CONTEXT_STATIC_TEXTS]];
    $static_texts = $this->localeStorage->getTranslations(['language' => $this->languageManager->getCurrentLanguage()->getId()], $options);
    foreach ($static_texts as $string) {
      list(, $static_texts_key) = explode(self::CONTEXT_KEY_SEPARATOR, $string->getValues(['context'])['context']);
      // Prepare response data.
      $response_data[] = [
        'machine_name' => $static_texts_key,
        'message' => !empty($string->getString()) ? $string->getString() : $string->getValues(['source'])['source'],
      ];
    }

    // As there's not caching information vailable with StringInterface object,
    // We are not caching $response_data.
    return new ModifiedResourceResponse($response_data);
  }

}
