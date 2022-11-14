<?php

namespace Drupal\alshaya_addressbook_react\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\alshaya_addressbook\AlshayaAddressBookManager;
use Drupal\alshaya_addressbook\AddressBookAreasTermsHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_addressbook\AlshayaAddressBookManagerInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get address info.
 *
 * @RestResource(
 *   id = "alshaya_addressbook_resource",
 *   label = @Translation("Address book information"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/get-addressbook-info"
 *   }
 * )
 */
class AddressbookResource extends ResourceBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Address Book Manager service object.
   *
   * @var \Drupal\alshaya_addressbook\AlshayaAddressBookManager
   */
  protected $addressBookManager;

  /**
   * AddressBook Areas Terms helper service.
   *
   * @var \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper
   */
  protected $areasTermsHelper;

  /**
   * Config Factory service object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AlshayaRcsCategoryResource constructor.
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
   * @param \Drupal\alshaya_addressbook\AlshayaAddressBookManager $address_book_manager
   *   AddressBook Manager service object.
   * @param \Drupal\alshaya_addressbook\AddressBookAreasTermsHelper $areas_terms_helper
   *   AddressBook Areas Terms helper service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              LanguageManagerInterface $language_manager,
                              AlshayaAddressBookManager $address_book_manager,
                              AddressBookAreasTermsHelper $areas_terms_helper,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->addressBookManager = $address_book_manager;
    $this->areasTermsHelper = $areas_terms_helper;
    $this->configFactory = $config_factory;
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
      $container->get('logger.factory')->get('alshaya_addressbook_react'),
      $container->get('language_manager'),
      $container->get('alshaya_addressbook.manager'),
      $container->get('alshaya_addressbook.area_terms_helper'),
      $container->get('config.factory'),
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    $response_data = [
      'address_fields' => _alshaya_spc_get_address_fields(),
      'area_parents' => $this->areasTermsHelper->getAllGovernates(TRUE),
      'area_options' => $this->areasTermsHelper->getAllAreas(TRUE),
      'area_parents_options' => [],
    ];

    // Get the mapping of area parents with the area options.
    foreach ($response_data['area_parents'] ?? [] as $key => $item) {
      $response_data['area_parents_options'][$key] = $this->areasTermsHelper->getAllAreasWithParent($key, TRUE);
    }

    $response = new ResourceResponse($response_data);
    $this->addRequiredCacheableDependency($response);
    return $response;
  }

  /**
   * Adding required dependency to response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   Response object.
   */
  protected function addRequiredCacheableDependency(ResourceResponse $response) {
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => array_merge(
          $this->configFactory->get('alshaya_addressbook.settings')->getCacheTags() ?? [],
          ['taxonomy_term_list:' . AlshayaAddressBookManagerInterface::AREA_VOCAB],
        ),
      ],
    ]));
  }

}
