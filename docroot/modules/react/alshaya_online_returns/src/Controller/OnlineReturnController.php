<?php

namespace Drupal\alshaya_online_returns\Controller;

use Drupal\user\UserInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\block\BlockViewBuilder;
use Drupal\Core\Render\Renderer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\alshaya_online_returns\Helper\OnlineReturnsHelper;
use Drupal\alshaya_online_returns\Helper\OnlineReturnsApiHelper;
use Drupal\address\Repository\CountryRepository;
use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\alshaya_seo_transac\AlshayaGtmManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Online controller to prepare data for return pages.
 */
class OnlineReturnController extends ControllerBase {

  /**
   * Alshaya Online Returns Helper.
   *
   * @var \Drupal\alshaya_online_returns\Helper\OnlineReturnsHelper
   */
  protected $onlineReturnsHelper;

  /**
   * Alshaya Online Returns API Helper.
   *
   * @var \Drupal\alshaya_online_returns\Helper\OnlineReturnsApiHelper
   */
  protected $onlineReturnsApiHelper;

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Address Country Repository service object.
   *
   * @var \Drupal\address\Repository\CountryRepository
   */
  protected $addressCountryRepository;

  /**
   * Renderer service object.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Alshaya GTM Manager.
   *
   * @var \Drupal\alshaya_seo_transac\AlshayaGtmManager
   */
  protected $gtmManager;

  /**
   * Orders Manager service.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * ReturnRequestController constructor.
   *
   * @param \Drupal\alshaya_online_returns\Helper\OnlineReturnsHelper $online_returns_helper
   *   Alshaya online returns helper.
   * @param \Drupal\alshaya_online_returns\Helper\OnlineReturnsApiHelper $online_returns_api_helper
   *   Alshaya online returns helper.
   * @param \Drupal\address\Repository\CountryRepository $address_country_repository
   *   Address Country Repository service object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Renderer service object.
   * @param \Drupal\alshaya_seo_transac\AlshayaGtmManager $gtm_manager
   *   Gtm Manager object.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders Manager service.
   */
  public function __construct(OnlineReturnsHelper $online_returns_helper,
                              OnlineReturnsApiHelper $online_returns_api_helper,
                              CountryRepository $address_country_repository,
                              AlshayaApiWrapper $api_wrapper,
                              Renderer $renderer,
                              AlshayaGtmManager $gtm_manager,
                              OrdersManager $orders_manager) {
    $this->onlineReturnsHelper = $online_returns_helper;
    $this->onlineReturnsApiHelper = $online_returns_api_helper;
    $this->addressCountryRepository = $address_country_repository;
    $this->apiWrapper = $api_wrapper;
    $this->renderer = $renderer;
    $this->gtmManager = $gtm_manager;
    $this->ordersManager = $orders_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_online_returns.online_returns_helper'),
      $container->get('alshaya_online_returns.online_returns_api_helper'),
      $container->get('address.country_repository'),
      $container->get('alshaya_api.api'),
      $container->get('renderer'),
      $container->get('alshaya_seo_transac.gtm_manager'),
      $container->get('alshaya_acm_customer.orders_manager')
    );
  }

  /**
   * Controller function for return confirmation.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders detail page is being viewed.
   * @param string $order_id
   *   Order id to view the detail for.
   *
   * @return array|null
   *   Build array.
   */
  public function returnRequest(UserInterface $user, $order_id) {
    // Do not proceed if Online returns is not enabled.
    if (!$this->onlineReturnsHelper->isOnlineReturnsEnabled()) {
      return $this->getCacheableRedirectResponse($user, $order_id)->send();
    }

    $orderDetails = $this->getOrderReturnDetails($user, $order_id);
    if (!$this->onlineReturnsHelper->validateReturnRequest($orderDetails)) {
      return $this->getCacheableRedirectResponse($user, $order_id)->send();
    }

    $build = [];

    $build['#cache']['tags'] = $this->onlineReturnsHelper->getCacheTags();

    // Get return configurations.
    $returnConfig = $this->onlineReturnsApiHelper->getReturnsApiConfig(
      $this->languageManager()->getCurrentLanguage()->getId(),
    );

    // Adding address fields configuration to display user address details.
    $build['#attached']['drupalSettings']['address_fields'] = _alshaya_spc_get_address_fields();

    // Attach library for return page react component.
    $build['#markup'] = '<div id="alshaya-online-return-request"></div>';
    $build['#attached']['library'][] = 'alshaya_online_returns/alshaya_return_requests';
    $build['#attached']['library'][] = 'alshaya_white_label/online-returns';
    $build['#attached']['library'][] = 'alshaya_seo_transac/gtm_online_returns';
    $build['#attached']['drupalSettings']['returnInfo'] = [
      'orderDetails' => $orderDetails,
      'returnConfig' => $returnConfig,
      'helperBlock' => $this->getHelperBlock(),
    ];
    return $build;
  }

  /**
   * Controller function for return confirmation.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the return request page is being viewed.
   * @param string $order_id
   *   Order id to view the detail for.
   *
   * @return array|null
   *   Build array.
   */
  public function returnConfirmation(UserInterface $user, $order_id) {
    $build = [];

    $build['#cache']['tags'] = $this->onlineReturnsHelper->getCacheTags();

    // Do not proceed if Online returns is not enabled.
    if (!$this->onlineReturnsHelper->isOnlineReturnsEnabled()) {
      throw new \Exception('Online Returns feature not enabled.');
    }

    // Have separate cache entry for each return id query parameter.
    $build['#cache']['contexts'][] = 'url.query_args:returnId';

    $orderDetails = $this->getOrderReturnDetails($user, $order_id);

    // Get config for return confirmations page.
    // This will include what's next section of the page.
    $returnConfig = $this->config('alshaya_online_returns.return_confirmation');

    $build['#cache']['tags'] = array_merge(
      $build['#cache']['tags'] ?? [],
      $returnConfig->getCacheTags()
    );

    // Adding address fields configuration to display user address details.
    $build['#attached']['drupalSettings']['address_fields'] = _alshaya_spc_get_address_fields();

    // Get return configurations.
    $returnConfiguration = $this->onlineReturnsApiHelper->getReturnsApiConfig(
      $this->languageManager()->getCurrentLanguage()->getId(),
    );

    // Attach library for return page react component.
    $build['#markup'] = '<div id="alshaya-return-confirmation"></div>';
    $build['#attached']['library'][] = 'alshaya_online_returns/alshaya_return_confirmation';
    $build['#attached']['library'][] = 'alshaya_white_label/online-returns';
    $build['#attached']['library'][] = 'alshaya_seo_transac/gtm_online_returns';
    $build['#attached']['drupalSettings']['returnInfo'] = [
      'orderDetails' => $orderDetails,
      'returnConfirmationStrings' => $returnConfig->get('rows'),
      'dateFormat' => $returnConfig->get('return_date_format'),
      'timeZone' => date_default_timezone_get(),
      'returnConfig' => $returnConfiguration,
      'helperBlock' => $this->getHelperBlock(),
    ];
    return $build;
  }

  /**
   * Controller function for process order details information.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the return confirmation page is being viewed.
   * @param string $order_id
   *   Order id to view the detail for.
   *
   * @return array|null
   *   Build array.
   */
  public function getOrderReturnDetails(UserInterface $user, $order_id) {
    $this->moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    // Get all the orders of logged in user.
    $customer_id = (int) $user->get('acq_customer_id')->getString();

    $order = $this->ordersManager->getOrderByIncrementId($order_id);

    if (empty($order)) {
      throw new NotFoundHttpException();
    }
    $orderDetails = alshaya_acm_customer_build_order_detail($order);

    // Allow other modules to update order details build.
    $this->moduleHandler()->alter('alshaya_online_returns_order_details_build', $order, $orderDetails);
    // Sort payment details by weight.
    $weight = array_column($orderDetails['#order_details']['paymentDetails'], 'weight');
    array_multisort($weight, SORT_ASC, $orderDetails['#order_details']['paymentDetails']);

    $orderDetails['#order'] = array_merge(
      $orderDetails['#order'] ?? [],
      $this->onlineReturnsHelper->prepareOrderData($order)
    );

    $orderDetails['#products'] = $this->onlineReturnsHelper->prepareProductsData($orderDetails["#products"]);
    $orderDetails['#gtm_info'] = $this->gtmManager->fetchCompletedOrderAttributes($order);

    // Adding country label to display country along with address.
    $country_list = $this->addressCountryRepository->getList();
    $country_label = _alshaya_custom_get_site_level_country_code();
    if (isset($country_label) && !empty($country_list)) {
      $orderDetails['#order_details']['delivery_address_raw']['country_label'] = $country_list[$country_label];
    }
    return $orderDetails;
  }

  /**
   * Controller function for return label download.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the return request page is being viewed.
   * @param string $order_id
   *   Order id to view the detail for.
   * @param string $return_id
   *   Entity id for the return request.
   *
   * @return mixed
   *   The return label or exception.
   */
  public function getReturnPrintLabel(UserInterface $user, $order_id, $return_id) {
    // Decode the return id.
    $decoded_return_id = json_decode(base64_decode($return_id), TRUE)['return_id'] ?? NULL;
    if ($decoded_return_id) {
      // Get the return request first.
      $endpoint = "rma/returns/$decoded_return_id";
      $request_options = [
        'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('return_get'),
      ];

      // Request from magento to get return items.
      $return_item_response = $this->apiWrapper->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

      if ($return_item_response) {
        // Decode the response and get the increment return id.
        $decoded_return_item = json_decode($return_item_response, TRUE);

        // Do the validation before getting return item label PDF.
        if ($this->isValidReturnRequest($user, $decoded_return_item)) {
          $incremented_return_id = $decoded_return_item['increment_id'];

          $endpoint = "awb/$incremented_return_id";
          // Request from magento to get return print label.
          $return_print_response = $this->apiWrapper->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

          // If json_decode is not successful, means we have actual file
          // response. Otherwise we have error message which can be decoded by
          // json.
          if (!json_decode($return_print_response, NULL)) {
            $response = new Response($return_print_response);
            $disposition = $response->headers->makeDisposition(
              ResponseHeaderBag::DISPOSITION_ATTACHMENT,
              'ReturnLabel-' . $incremented_return_id . '.pdf'
            );
            $response->headers->set('Content-Disposition', $disposition);
            return $response;
          }
        }
      }
    }

    // Return print label not found.
    throw new NotFoundHttpException();
  }

  /**
   * Function to check if a valid return request is raised for the print label.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the return request page is being viewed.
   * @param array $returnItem
   *   The array containing return item info.
   */
  protected function isValidReturnRequest(UserInterface $user, array $returnItem) {
    // Return from here if `awb_path` is empty.
    if (empty($returnItem['extension_attributes']['awb_path'])) {
      return FALSE;
    }

    // Return from here if `is_picked` is empty or the return is already picked.
    if (!empty($returnItem['extension_attributes']['is_picked'])) {
      return FALSE;
    }

    // Return from here if `is_closed` is empty or the return is already closed.
    if (!empty($returnItem['extension_attributes']['is_closed'])) {
      return FALSE;
    }

    // Validated if the return request is of the valid user.
    $customer_id = $returnItem['customer_id'] ?? '';
    if ($customer_id != $user->get('acq_customer_id')->getString()) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Function returns the html of the account helper block.
   */
  protected function getHelperBlock() {
    $helper_block = BlockViewBuilder::lazyBuilder('myaccountneedhelp', 'full');
    // Validate if the block exists.
    if ($helper_block) {
      return $this->renderer->renderPlain($helper_block)->__toString();
    }

    return NULL;
  }

  /**
   * Wrapper function to return the cacheable redirect response.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders detail page is being viewed.
   * @param string $order_id
   *   Order id to view the detail for.
   */
  protected function getCacheableRedirectResponse(UserInterface $user, $order_id) {
    $cacheable_response = new CacheableRedirectResponse(Url::fromRoute('alshaya_acm_customer.orders_detail', [
      'user' => $user->id(),
      'order_id' => $order_id,
    ])->toString());

    // Make response cacheable.
    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->setCacheTags($this->onlineReturnsHelper->getCacheTags());
    $cacheable_response->addCacheableDependency($cacheable_metadata);

    return $cacheable_response;
  }

}
