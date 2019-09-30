<?php

namespace Drupal\alshaya_spc\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'AlshayaReactMiniCartBlock' block.
 *
 * @Block(
 *   id = "alshaya_react_mini_cart",
 *   admin_label = @Translation("Alshaya React Cart Mini Cart Block"),
 * )
 */
class AlshayaReactMiniCartBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * AlshayaReactMiniCartBlock constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   Language manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ConfigFactoryInterface $config_factory,
                              RequestStack $requestStack,
                              LanguageManagerInterface $languageManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->requestStack = $requestStack;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('request_stack'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the currency configurations.
    $config = $this->configFactory->get('acq_commerce.currency');

    $build = [
      '#type' => 'markup',
      '#markup' => '<div id="spc-minicart"></div>',
      '#attached' => [
        'library' => [
          'alshaya_spc/mini_cart',
        ],
        'drupalSettings' => [
          'mini_cart' => [
            'currency_code' => $config->get('currency_code'),
            'currency_code_position' => $config->get('currency_code_position'),
            'decimal_points' => $config->get('decimal_points'),
            'base_url' => $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost(),
            'langcode' => $this->languageManager->getCurrentLanguage()->getId(),
          ],
        ],
      ],
    ];

    return $build;
  }

}
