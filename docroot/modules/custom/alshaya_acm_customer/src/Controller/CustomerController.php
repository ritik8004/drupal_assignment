<?php

namespace Drupal\alshaya_acm_customer\Controller;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\alshaya_acm_customer\OrdersManager;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Customer controller to add/override pages for customer.
 */
class CustomerController extends ControllerBase {

  /**
   * Renderer service object.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Current request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Api wrapper.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Current time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $currentTime;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Orders manager service object.
   *
   * @var \Drupal\alshaya_acm_customer\OrdersManager
   */
  protected $ordersManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('renderer'),
      $container->get('alshaya_api.api'),
      $container->get('datetime.time'),
      $container->get('date.formatter'),
      $container->get('alshaya_acm_customer.orders_manager')
    );
  }

  /**
   * CustomerController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Renderer service object.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   * @param \Drupal\Component\Datetime\TimeInterface $current_time
   *   Current time service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Date formatter service.
   * @param \Drupal\alshaya_acm_customer\OrdersManager $orders_manager
   *   Orders manager service object.
   */
  public function __construct(Request $current_request,
                              Renderer $renderer,
                              AlshayaApiWrapper $api_wrapper,
                              TimeInterface $current_time,
                              DateFormatterInterface $date_formatter,
                              OrdersManager $orders_manager) {
    $this->currentRequest = $current_request;
    $this->renderer = $renderer;
    $this->apiWrapper = $api_wrapper;
    $this->currentTime = $current_time;
    $this->dateFormatter = $date_formatter;
    $this->ordersManager = $orders_manager;

  }

  /**
   * Returns the build to the orders list page.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders list page is being viewed.
   *
   * @return array
   *   Build array.
   */
  public function listOrders(UserInterface $user) {
    if (!alshaya_acm_customer_is_customer($user)) {
      throw new NotFoundHttpException();
    }

    $this->moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $build = [];

    // Initialising order details array to array.
    $orderDetails = [];
    $nextPageButton = [];
    $noOrdersFoundMessage = ['#markup' => ''];
    $help_block = NULL;

    // Get items to show per page from config.
    $itemsPerPage = $this->config('alshaya_acm_customer.orders_config')->get('items_per_page');

    // Build account details array.
    $account = [];
    $account['first_name'] = $user->get('field_first_name')->getString();
    $account['last_name'] = $user->get('field_last_name')->getString();

    // Get the search form.
    $searchForm = $this->formBuilder()->getForm('Drupal\alshaya_acm_customer\Form\OrderSearchForm');
    $searchForm['form_id']['#printed'] = TRUE;
    $searchForm['form_build_id']['#printed'] = TRUE;

    // Get current page number.
    $currentPageNumber = (int) $this->currentRequest->query->get('page');

    // Get the offset to start displaying orders from.
    $offset = $currentPageNumber * $itemsPerPage;

    try {
      // Get the orders to display for current user and filter applied.
      $customer_id = (int) $user->get('acq_customer_id')->getString();
      $page_size = $offset + $itemsPerPage;
      // If user is something, then set the page_size as 0 to get all the
      // products.
      if ($this->currentRequest->query->get('search')
        || $this->currentRequest->query->get('filter')) {
        $page_size = 0;
        $offset = 0;
      }
      $orders = $this->ordersManager->getOrders($customer_id, $page_size, 'search', 'filter');

      if (empty($orders)) {
        // @todo Check the empty result message.
        if ($this->currentRequest->query->get('search')) {
          $noOrdersFoundMessage['#markup'] = '<div class="no--orders">' . $this->t('Your search yielded no results, please try different text in search.') . '</div>';
        }
        else {
          // Below message is taken from https://zpl.io/Oqv1o mockup.
          $noOrdersFoundMessage['#markup'] = '<div class="no--orders">' . $this->t('You haven’t ordered anything recently.') . '</div>';
        }
      }
      else {
        $order_count = $this->ordersManager->getOrdersCount($customer_id);
        // Calculate the order count based on the filter value.
        if ($this->currentRequest->query->get('search')
        || $this->currentRequest->query->get('filter')) {
          $order_count = count($orders);
          $itemsPerPage = $order_count;
        }
        // Get the orders to display for current page.
        $ordersPaged = array_slice($orders, $offset, $itemsPerPage, TRUE);
        if ($order_count > $offset + $itemsPerPage) {
          // Get all the query parameters we currently have.
          $query = $this->currentRequest->query->all();
          $query['page'] = $currentPageNumber + 1;

          // Prepare the next page url.
          $nextPageUrl = Url::fromRoute('alshaya_acm_customer.list_orders_ajax', ['user' => $user->id()], ['query' => $query])->toString();

          // Prepare the next page button tag.
          $nextPageButton = [
            '#type' => 'html_tag',
            '#tag' => 'button',
            '#value' => $this->t('show more'),
            '#attributes' => [
              'attr-next-page' => $nextPageUrl,
            ],
          ];
        }

        // Loop through each order and prepare the array for template.
        foreach ($ordersPaged as $order) {
          if ($order_summary = alshaya_acm_customer_get_processed_order_summary($order)) {
            $orderDetails[] = [
              '#theme' => 'user_order_list_item',
              '#order' => $order_summary,
              '#order_detail_link' => Url::fromRoute('alshaya_acm_customer.orders_detail', [
                'user' => $user->id(),
                'order_id' => $order['increment_id'],
              ])->toString(),
            ];
          }
        }

        // Load my-account-help block for rendering on order list page.
        $help_block_entity = $this->entityTypeManager()->getStorage('block')->load('myaccountneedhelp');
        if ($help_block_entity) {
          $help_block = $this->entityTypeManager()->getViewBuilder('block')->view($help_block_entity);
        }
      }
    }
    catch (\Exception $e) {
      $orders = [];

      if (acq_commerce_is_exception_api_down_exception($e)) {
        $noOrdersFoundMessage = [
          '#theme' => 'global_error',
          '#message' => $e->getMessage(),
        ];
      }
    }

    $build = [
      '#theme' => 'user_order_list',
      '#search_form' => $searchForm,
      '#order_details' => $orderDetails,
      '#order_not_found' => $noOrdersFoundMessage,
      '#account' => $account,
      '#next_page_button' => $nextPageButton,
      '#help_block' => $help_block,
    ];

    if (!empty($orderDetails)) {
      $build['#attached'] = [
        'library' => [
          'alshaya_acm_customer/orders-list',
        ],
      ];
    }

    $cache_time_limit = $this->config('alshaya_acm_customer.orders_config')->get('cache_time_limit');
    $build['#cache'] = ['max-age' => $cache_time_limit];
    $build['#cache']['tags'] = $user->getCacheTags();
    $build['#cache']['contexts'] = $user->getCacheContexts();

    return $build;
  }

  /**
   * Prints json of pager and orders list for current page.
   *
   * @param \Drupal\user\UserInterface $user
   *   User for which the orders are to be displayed.
   */
  public function listOrdersAjax(UserInterface $user = NULL) {
    $response = [];
    $fullBuild = $this->listOrders($user);

    $response['orders_list'] = '';
    foreach ($fullBuild['#order_details'] as $order) {
      $response['orders_list'] .= '<li>' . $this->renderer->render($order) . '</li>';
    }

    $response['next_page_button'] = $this->renderer->render($fullBuild['#next_page_button']);

    print json_encode($response);
    exit;
  }

  /**
   * Controller function for order detail page.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders detail page is being viewed.
   * @param string $order_id
   *   Order id to view the detail for.
   *
   * @return array
   *   Build array.
   */
  public function orderDetail(UserInterface $user, $order_id) {
    $this->moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $order = $this->ordersManager->getOrderByIncrementId($order_id);
    if (empty($order) || ($order["customer_id"] != $user->get('acq_customer_id')->getString())) {
      $response = new RedirectResponse(Url::fromRoute('entity.user.canonical', ['user' => $user->id()])->toString());
      $response->send();
      exit;
    }

    $build = alshaya_acm_customer_build_order_detail($order);
    $build['order'] = $order;

    // Build account details array.
    $account = [];
    $account['first_name'] = $user->get('field_first_name')->getString();
    $account['last_name'] = $user->get('field_last_name')->getString();

    // If order invoice is available for download.
    if (!empty($order['extension']['invoice_path'])) {
      // Download invoice link.
      $build['#download_link'] = Url::fromRoute('alshaya_acm_customer.invoice_download', [
        'user' => $user->id(),
        'order_id' => $order_id,
      ]);
    }

    $build['#print_link'] = Url::fromRoute('alshaya_acm_customer.orders_print', [
      'user' => $user->id(),
      'order_id' => $order_id,
    ]);
    $build['#account'] = $account;
    if ($vat_text = $this->config('alshaya_acm_product.settings')->get('vat_text')) {
      $build['#vat_text'] = $vat_text;
    }
    $build['#theme'] = 'user_order_detail';

    $cache_time_limit = $this->config('alshaya_acm_customer.orders_config')->get('cache_time_limit');
    $build['#cache'] = ['max-age' => $cache_time_limit];
    // Refund text depends on alshaya_acm_checkout.settings.
    $build['#cache']['tags'] = Cache::mergeTags($user->getCacheTags(), ['config:alshaya_acm_checkout.settings']);
    $build['#cache']['contexts'] = $user->getCacheContexts();

    // Allow other modules to update order details build.
    $this->moduleHandler()->alter('alshaya_acm_customer_orders_details_build', $order, $build);

    // Get order details and expose via Drupal settings.
    $build['#attached']['drupalSettings']['order'] = $this->prepareOrderDetails($build);

    return $build;
  }

  /**
   * Prepares order details for front end.
   *
   * @param array $build
   *   The build array.
   *
   * @return array
   *   The order details.
   */
  private function prepareOrderDetails(array $build) {
    $details = $build['#order'];
    $details['total'] = $build['order']['grand_total'];
    $details['order_details'] = $build['#order_details'] ?? [];
    $details['order_details']['payment'] = $build['#order_details']['payment'] ?? [];
    $details['order_details']['store_open_hours'] = $build['#order_details']['store_open_hours'] ?? [];
    $details['online_booking_notice'] = $build['#online_booking_notice'] ?? [];
    $details['delivery_detail_notice'] = $build['#delivery_detail_notice'] ?? [];
    $details['vat_text'] = $build['#vat_text'] ?? '';
    $details['products'] = $build['#products'] ?? [];
    $details['refunded_products'] = $build['refunded_products'] ?? [];
    $details['cancelled_products'] = $build['#cancelled_products'] ?? [];
    $details['order_details']['billing_address_title'] = $this->t('Billing details');
    $details['order_details']['delivery_address_title'] = $this->t('Delivery details');
    $details['total_quantity_text'] = $this->formatPlural($build['#order']['quantity'], 'Total: @count item', 'Total: @count items');
    // Render product images, translate attribute labels.
    foreach (['products', 'cancelled_products'] as $type) {
      foreach ($details[$type] as &$product) {
        // Render images.
        if (isset($product['image'])) {
          $product['image'] = render($product['image']);
        }
        // Translate attribute labels.
        if (isset($product['attributes'])) {
          foreach ($product['attributes'] as &$attribute) {
            if ($attribute['label'] instanceof TranslatableMarkup) {
              $attribute['label'] = render($attribute['label']);
            }
          }
        }
      }
    }

    // Render details.
    foreach ($details['order_details'] as &$item) {
      if (is_array($item) && (isset($item['#markup']) || isset($item['#theme']))) {
        $item = render($item);
      }
    }

    return $details;
  }

  /**
   * Controller function for order print.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders detail page is being viewed.
   * @param string $order_id
   *   Order id to view the detail for.
   *
   * @return array
   *   Build array.
   */
  public function orderPrint(UserInterface $user, $order_id) {
    $this->moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    // Get order details and add more information for print.
    $build = $this->orderDetail($user, $order_id);

    $build['#barcode'] = alshaya_acm_customer_get_barcode($build['order']);
    $build['#account']['mail'] = $user->get('mail')->getString();
    $build['#site_logo'] = [
      '#theme' => 'image',
      '#uri' => theme_get_setting('logo.url'),
    ];
    $build['#theme'] = 'user_order_print';
    $build['#attached']['library'][] = 'alshaya_acm_customer/order_print';

    return $build;
  }

  /**
   * Controller function to show print view of last order for anonymous users.
   *
   * @return array
   *   Build array.
   */
  public function orderPrintLast() {
    $this->moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $order = _alshaya_acm_checkout_get_last_order_from_session();
    if (empty($order)) {
      return [];
    }

    $build = alshaya_acm_customer_build_order_detail($order);

    // Build account details array.
    $account = [];

    $account['first_name'] = $order['firstname'];
    $account['last_name'] = $order['lastname'];
    $account['mail'] = $order['email'];

    $build['#site_logo'] = [
      '#theme' => 'image',
      '#uri' => theme_get_setting('logo.url'),
    ];
    $build['#barcode'] = alshaya_acm_customer_get_barcode($order);
    $build['#account'] = $account;
    if ($vat_text = $this->config('alshaya_acm_product.settings')->get('vat_text')) {
      $build['#vat_text'] = $vat_text;
    }
    $build['#theme'] = 'user_order_print';
    $build['#attached']['library'][] = 'alshaya_acm_customer/order_print';

    // Not caching for this as this page is only accessed when user place order
    // and see printed version. User can also directly see/visit this page but
    // that is not a valid case + caching this having consequences for
    // anonymous users.
    $build['#cache']['max-age'] = 0;

    return $build;
  }

  /**
   * Controller function for order download.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders detail page is being viewed.
   * @param string $order_id
   *   Order to download.
   *
   * @return mixed
   *   The order invoice or exception.
   */
  public function orderDownload(UserInterface $user, $order_id) {
    $endpoint = 'order-manager/invoice/' . $order_id;

    $request_options = [
      'timeout' => $this->apiWrapper->getMagentoApiHelper()->getPhpTimeout('order_get'),
    ];

    // Request from magento to get invoice.
    $invoice_response = $this->apiWrapper->invokeApi($endpoint, [], 'GET', FALSE, $request_options);

    // If json_decode is not successful, means we have actual file response.
    // Otherwise we have error message which can be decoded by json.
    if (!json_decode($invoice_response, NULL)) {
      $response = new Response($invoice_response);
      // Get time format in 'YYYYMMDDHHMM'.
      $time_format = $this->dateFormatter->format($this->currentTime->getRequestTime(), 'custom', 'Ymdhi');
      $disposition = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        'Invoice_' . $time_format . '.pdf'
      );
      $response->headers->set('Content-Disposition', $disposition);
      $response->headers->set('Content-type', 'application/pdf');
      return $response;
    }

    // Invoice not found.
    throw new NotFoundHttpException();
  }

  /**
   * Checks if user can download the invoice.
   */
  public function checkInvoiceAccess(AccountInterface $account, UserInterface $user, $order_id) {
    if (empty($user) || empty($order_id)) {
      return AccessResult::forbidden();
    }

    // Only logged in users will be able to download invoice.
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    // If current user is the one for which invoice is requested
    // or the user is administrator we allow access.
    if (!($account->id() == $user->id() || $account->hasPermission('access all orders'))) {
      return AccessResult::forbidden();
    }

    $download_invoice = FALSE;

    $this->moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    // Get all orders of the current user.
    $customer_id = (int) $user->get('acq_customer_id')->getString();
    $user_order = $this->ordersManager->getOrderByIncrementId($order_id);
    // If order belongs to the current user and invoice is available for
    // download.
    if ($user_order) {
      $download_invoice = !empty($user_order['extension']['invoice_path']);
    }

    return AccessResult::allowedIf($download_invoice);
  }

}
