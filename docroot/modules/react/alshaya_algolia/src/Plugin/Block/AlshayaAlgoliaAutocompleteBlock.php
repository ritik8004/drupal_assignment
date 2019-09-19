<?php

namespace Drupal\alshaya_algolia\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display 'custom link' elements.
 *
 * @Block(
 *   id = "alshaya_algolia_autocomplete_block",
 *   admin_label = @Translation("Alshaya Algolia Autocomplete")
 * )
 */
class AlshayaAlgoliaAutocompleteBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manger service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * AlshayaAlgoliaAutocompleteBlock constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $algolia_config = $this->configFactory->get('search_api.server.algolia')->get('backend_config');
    $index = $this->configFactory->get('search_api.index.acquia_search_index')->get('options');
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    return [
      '#type' => 'markup',
      '#markup' => '<div id="alshaya-algolia-autocomplete"></div>',
      '#attached' => [
        'library' => [
          'alshaya_algolia/autocomplete',
        ],
        'drupalSettings' => [
          'algoliaSearch' => [
            'application_id' => $algolia_config['application_id'],
            'api_key' => $algolia_config['api_key'],
            'indexName' => $index['algolia_index_name'] . "_{$lang}",
          ],
        ],
      ],
      '#cache' => [
        'contexts' => ['languages'],
      ],
    ];
  }

}
