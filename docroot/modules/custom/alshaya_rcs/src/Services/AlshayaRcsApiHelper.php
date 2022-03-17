<?php

namespace Drupal\alshaya_rcs\Services;

use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\Client;

/**
 * Class Alshaya Rcs Api Helper.
 *
 * @package Drupal\alshaya_rcs\Services
 */
class AlshayaRcsApiHelper {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs a new AlshayaRcsCategoryHelper instance.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \GuzzleHttp\Client $http_client
   *   HTTP Client.
   */
  public function __construct(LanguageManagerInterface $language_manager,
                              RequestStack $request_stack,
                              Client $http_client) {
    $this->languageManager = $language_manager;
    $this->request = $request_stack->getCurrentRequest();
    $this->httpClient = $http_client;
  }

  /**
   * Process node data migration to RCS content type.
   */
  public function invokeGraphqlEndpoint(string $query) {
    $langcode = $this->languageManager->getDefaultLanguage()->getId();
    $settings = Settings::get('alshaya_api.settings');
    // Magento URL to get the product option attributes.
    $request_url = $settings['magento_host'] . '/graphql&query=' . $query;
    // List of environments to use proxy in from Settings.
    $environments_to_use_proxy = Settings::get('environments_to_use_proxy', [
      'local',
      'dev',
      'dev2',
      'dev3',
      'qa2',
    ]);
    // Use proxy on only specific environments.
    if (in_array(Settings::get('env_name'), $environments_to_use_proxy)) {
      $request_url = $this->request->getSchemeAndHttpHost() . '/proxy/?url=' . $request_url;
    }

    return Json::decode($this->httpClient->get($request_url, [
      'headers' => [
        'store' => Settings::get('magento_lang_prefix')[$langcode],
      ],
    ])->getBody());

  }

}
