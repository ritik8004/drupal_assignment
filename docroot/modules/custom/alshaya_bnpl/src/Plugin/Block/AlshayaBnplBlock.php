<?php

namespace Drupal\alshaya_bnpl\Plugin\Block;

use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_bnpl\Helper\AlshayaBnplAPIHelper;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
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
   * The current route matcher service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Sku Manager service.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   Current route matcher service.
   * @param \Drupal\alshaya_bnpl\Helper\AlshayaBnplAPIHelper $alshayaBnplAPIHelper
   *   Postpay API Helper.
   * @param \Drupal\Core\Language\LanguageManager $languageManager
   *   Language Manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\alshaya_acm_product\SkuManager $skuManager
   *   Sku Manager service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              CurrentRouteMatch $currentRouteMatch,
                              AlshayaBnplAPIHelper $alshayaBnplAPIHelper,
                              LanguageManager $languageManager,
                              ConfigFactoryInterface $config_factory,
                              SkuManager $skuManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouteMatch = $currentRouteMatch;
    $this->alshayaBnplAPIHelper = $alshayaBnplAPIHelper;
    $this->languageManager = $languageManager;
    $this->configFactory = $config_factory;
    $this->skuManager = $skuManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('alshaya_bnpl.api_helper'),
      $container->get('language_manager'),
      $container->get('config.factory'),
      $container->get('alshaya_acm_product.skumanager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $locale = $this->languageManager->getCurrentLanguage()->getId();
    $currency = $this->configFactory->get('acq_commerce.currency')->get('currency_code');

    switch ('product detail page') {
      case 'product detail page':
        $current_route = $this->currentRouteMatch->getParameters()->all();
        $node = $current_route['node'];
        if ($node->hasTranslation('en')) {
          $node = $node->getTranslation('en');
        }
        $product_sku = $this->skuManager->getSkuForNode($node);
        $sku_entity = SKU::loadFromSku($product_sku);

        if (empty($sku_entity)) {
          return [];
        }

        if ($sku_entity->hasTranslation('en')) {
          $sku_entity = $sku_entity->getTranslation('en');
        }
        $prices = $this->skuManager->getMinPrices($sku_entity);
        $final_price = $prices['final_price'] * 100;

        break;

      case 'cart page':
        // Define the cart page variables here.
        break;

      case 'checkout payment page':
        // Define the checkout payment page variables here.
        break;
    }

    // Change installment number based on the requirement here.
    $content = '<div class="postpay-widget"
        data-type="product"
        data-amount="' . $final_price . '"
        data-currency="' . $currency . '"
        data-num-instalments="2"
        data-locale="' . $locale . '">
        </div>';

    // Fetch the Postpay details from the MDC.
    $config = $this->alshayaBnplAPIHelper->getBnplApiConfig();
    return [
      '#markup' => $content,
      '#attached' => [
        'library' => [
          'alshaya_bnpl/postpay_sdk',
          'alshaya_bnpl/postpay_pdp',
        ],
        'drupalSettings' => [
          'postpay' => [
            'merchantId' => $config['merchant_id'],
            'sandbox' => $config['environment'] == 'sandbox' ? TRUE : FALSE,
            'theme' => 'light',
            'locale' => $locale,
          ],
        ],
      ],
    ];
  }

}
