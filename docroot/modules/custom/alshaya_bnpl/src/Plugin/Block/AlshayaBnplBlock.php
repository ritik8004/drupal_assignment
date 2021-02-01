<?php

namespace Drupal\alshaya_bnpl\Plugin\Block;

use Drupal\alshaya_bnpl\Helper\AlshayaBnplAPIHelper;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Buy now pay later block.
 *
 * @Block(
 *   id = "bnpl_block",
 *   admin_label = @Translation("Buy Now Pay Later Block"),
 *   category = @Translation("Postpay")
 * )
 */
class AlshayaBnplBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Postpay API Helper.
   *
   * @var \Drupal\alshaya_bnpl\Helper\AlshayaBnplAPIHelper
   */
  protected $alshayaBnplAPIHelper;

  /**
   * Language Manager service.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\alshaya_bnpl\Helper\AlshayaBnplAPIHelper $alshayaBnplAPIHelper
   *   Postpay API Helper.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   Language Manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              AlshayaBnplAPIHelper $alshayaBnplAPIHelper,
                              LanguageManager $languageManager,
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->alshayaBnplAPIHelper = $alshayaBnplAPIHelper;
    $this->languageManager = $languageManager;
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
      $container->get('alshaya_bnpl.api_helper'),
      $container->get('language_manager'),
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $locale = $this->languageManager->getCurrentLanguage()->getId();
    $currency = $this->configFactory->get('acq_commerce.currency')->get('currency_code');
    $content = '<div class="postpay-widget"
        data-type="product"
        data-amount="9999999"
        data-currency="' . $currency . '"
        data-num-instalments="2"
        data-locale="' . $locale . '">
        </div>';
    $config = $this->alshayaBnplAPIHelper->getBnplApiConfig();
    return [
      '#markup' => $content,
      '#attached' => [
        'library' => [
          'alshaya_bnpl/postpay_sdk',
        ],
        'drupalSettings' => [
          'postpay' => [
            'merchantId' => $config['merchantId'],
            'sandbox' => $config['environment'] == 'sandbox' ? TRUE : FALSE,
            'theme' => 'light',
            'locale' => $locale,
          ],
        ],
      ],
    ];
  }

}
