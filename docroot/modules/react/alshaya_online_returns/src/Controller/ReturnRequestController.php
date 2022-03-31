<?php

namespace Drupal\alshaya_online_returns\Controller;

use Drupal\user\UserInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_online_returns\Helper\OnlineReturnsHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_commerce\SKUInterface;
use Drupal\alshaya_online_returns\Helper\OnlineReturnsApiHelper;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_egift_card\Helper\EgiftCardHelper;
use Drupal\address\Repository\CountryRepository;

/**
 * Return request controller to prepare data for return request page.
 */
class ReturnRequestController extends ControllerBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Alshaya Online Returns Helper.
   *
   * @var \Drupal\alshaya_online_returns\Helper\OnlineReturnsHelper
   */
  protected $onlineReturnsHelper;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Alshaya Online Returns API Helper.
   *
   * @var \Drupal\alshaya_online_returns\Helper\OnlineReturnsApiHelper
   */
  protected $onlineReturnsApiHelper;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Egiftcard Helper service object.
   *
   * @var \Drupal\alshaya_egift_card\Helper\EgiftCardHelper
   */
  protected $egiftCardHelper;

  /**
   * Address Country Repository service object.
   *
   * @var \Drupal\address\Repository\CountryRepository
   */
  protected $addressCountryRepository;

  /**
   * ReturnRequestController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\alshaya_online_returns\Helper\OnlineReturnsHelper $online_returns_helper
   *   Alshaya online returns helper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\alshaya_online_returns\Helper\OnlineReturnsApiHelper $online_returns_api_helper
   *   Alshaya online returns helper.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\alshaya_egift_card\Helper\EgiftCardHelper $egiftCardHelper
   *   The egift card helper service.
   * @param \Drupal\address\Repository\CountryRepository $address_country_repository
   *   Address Country Repository service object.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              ModuleHandlerInterface $module_handler,
                              OnlineReturnsHelper $online_returns_helper,
                              EntityTypeManagerInterface $entity_type_manager,
                              OnlineReturnsApiHelper $online_returns_api_helper,
                              LanguageManagerInterface $language_manager,
                              EgiftCardHelper $egiftCardHelper,
                              CountryRepository $address_country_repository) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->onlineReturnsHelper = $online_returns_helper;
    $this->entityTypeManager = $entity_type_manager;
    $this->onlineReturnsApiHelper = $online_returns_api_helper;
    $this->languageManager = $language_manager;
    $this->egiftCardHelper = $egiftCardHelper;
    $this->addressCountryRepository = $address_country_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('alshaya_online_returns.online_returns_helper'),
      $container->get('entity_type.manager'),
      $container->get('alshaya_online_returns.online_returns_api_helper'),
      $container->get('language_manager'),
      $container->get('alshaya_egift_card.egift_card_helper'),
      $container->get('address.country_repository'),
    );
  }

  /**
   * Controller function for return request.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders detail page is being viewed.
   * @param string $order_id
   *   Order id to view the detail for.
   *
   * @return array|null
   *   Build array.
   */
  public function orderReturn(UserInterface $user, $order_id) {
    $config['enabled'] = $this->onlineReturnsHelper->isOnlineReturnsEnabled();
    $build['#cache']['tags'] = array_merge(
      $build['#cache']['tags'] ?? [],
      $this->configFactory->get('alshaya_online_returns.settings')->getCacheTags()
    );

    // Do not proceed if Online returns is not enabled.
    if ($config['enabled'] !== TRUE) {
      throw new \Exception('Online Returns feature not enabled.');
    }

    $this->moduleHandler->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    // Get all the orders of logged in user.
    $customer_id = (int) $user->get('acq_customer_id')->getString();
    $orders = alshaya_acm_customer_get_user_orders($customer_id);

    if (empty($orders)) {
      throw new NotFoundHttpException();
    }

    // Get the current order detail to build return page.
    $order_index = array_search($order_id, array_column($orders, 'increment_id'));

    if ($order_index === FALSE) {
      throw new NotFoundHttpException();
    }

    $order = $orders[$order_index];
    $orderDetails = alshaya_acm_customer_build_order_detail($order);

    // Check if egift feature is enabled.
    // Check if complete payment done via egift.
    // For multiple payment and if some amount is paid via egift
    // then also include eGift Card payment in payment details.
    if ($this->egiftCardHelper->isEgiftCardEnabled()) {
      if ($orderDetails['#order_details']['payment_method'] === 'hps_payment'
        || ($orderDetails['#order_details']['payment_method_code'] !== 'hps_payment'
        && isset($order['extension']['hps_redeemed_amount'])
        && $order['extension']['hps_redeemed_amount'] > 0 && isset($order['extension']['hps_redemption_card_number']))) {
        $egift_data = [
          'card_type' => $this->t('eGift Card', [], ['context' => 'egift']),
          'card_number' => substr($order['extension']['hps_redemption_card_number'], -4),
          'card_scheme' => 'egift',
        ];
        $orderDetails['#order_details']['paymentDetails']['egift'] = $egift_data;
      }
    }

    // Get display image for each product.
    // Connverting image url to renderable drupal url.
    foreach ($orderDetails['#products'] as $key => $item) {
      if (!empty($item['image'])) {
        if ($item['image']['#theme'] == 'image_style') {
          $image_style = $this->entityTypeManager->getStorage('image_style');
          $data = [
            'url' => $image_style->load($item['image']['#style_name'])->buildUrl($item['image']['#uri']),
            'title' => $item['image']['#title'],
            'alt' => $item['image']['#alt'],
          ];
        }
        elseif ($item['image']['#theme'] == 'image') {
          $data = [
            'url' => $item['image']['#attributes']['src'],
            'title' => $item['image']['#attributes']['title'],
            'alt' => $item['image']['#attributes']['alt'],
          ];
        }
        $orderDetails['#products'][$key]['image_data'] = $data;
      }
      $sku = SKU::loadFromSku($item['sku']);
      if ($sku instanceof SKUInterface) {
        $orderDetails['#products'][$key]['is_returnable'] = $this->onlineReturnsHelper->isSkuReturnable($sku);
      }
    }

    // Get return configurations.
    $returnConfig = $this->onlineReturnsApiHelper->getReturnsApiConfig(
      $this->languageManager->getCurrentLanguage()->getId(),
    );

    // Adding address fields configuration to display user address details.
    $build['#attached']['drupalSettings']['address_fields'] = _alshaya_spc_get_address_fields();

    // Adding country label to display country along with address.
    $country_list = $this->addressCountryRepository->getList();
    $country_label = _alshaya_custom_get_site_level_country_code();
    if (isset($country_label) && !empty($country_list)) {
      $orderDetails['#order_details']['delivery_address_raw']['country_label'] = $country_list[$country_label];
    }

    // Attach library for return page react component.
    $build['#markup'] = '<div id="alshaya-online-return-request"></div>';
    $build['#attached']['library'][] = 'alshaya_online_returns/alshaya_return_requests';
    $build['#attached']['drupalSettings']['returnRequest'] = [
      'orderDetails' => $orderDetails,
      'returnConfig' => $returnConfig,
    ];
    return $build;
  }

}
