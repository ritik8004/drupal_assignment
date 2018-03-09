<?php

namespace Drupal\alshaya_acm_customer\Controller;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Drupal\block\Entity\Block;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Api wrapper.
   *
   * @var \\Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('renderer'),
      $container->get('current_user'),
      $container->get('alshaya_api.api')
    );
  }

  /**
   * CustomerController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   Current request object.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   Renderer service object.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Api wrapper.
   */
  public function __construct(Request $current_request,
                              Renderer $renderer,
                              AccountInterface $current_user,
                              AlshayaApiWrapper $api_wrapper) {
    $this->currentRequest = $current_request;
    $this->renderer = $renderer;
    $this->currentUser = $current_user;
    $this->apiWrapper = $api_wrapper;

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

    try {
      // Get the orders to display for current user and filter applied.
      $orders = alshaya_acm_customer_get_user_orders($user->getEmail(), 'search', 'filter');

      if (empty($orders)) {
        // @TODO: Check the empty result message.
        if ($this->currentRequest->query->get('search')) {
          $noOrdersFoundMessage['#markup'] = '<div class="no--orders">' . $this->t('Your search yielded no results, please try different text in search.') . '</div>';
        }
        else {
          // Below message is taken from https://zpl.io/Oqv1o mockup.
          $noOrdersFoundMessage['#markup'] = '<div class="no--orders">' . $this->t('You haven’t ordered anything recently.') . '</div>';
        }
      }
      else {
        // Get current page number.
        $currentPageNumber = (int) $this->currentRequest->query->get('page');

        // Get the offset to start displaying orders from.
        $offset = $currentPageNumber * $itemsPerPage;

        // Get the orders to display for current page.
        $ordersPaged = array_slice($orders, $offset, $itemsPerPage, TRUE);

        if (count($orders) > $offset + $itemsPerPage) {
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
        $help_block_entity = Block::load('myaccountneedhelp');
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
      '#attached' => [
        'library' => ['alshaya_acm_customer/orders-list'],
      ],
      // @TODO: We may want to set it to cache time limit of API call.
      '#cache' => ['max-age' => 0],
    ];

    return $build;
  }

  /**
   * Prints json of pager and orders list for current page.
   *
   * @param \Drupal\user\UserInterface $user
   *   User for which the orders are to be displayed.
   */
  public function listOrdersAjax(UserInterface $user = NULL) {
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

    // Get the orders to display for current user and filter applied.
    $orders = alshaya_acm_customer_get_user_orders($user->getEmail());

    $order_index = array_search($order_id, array_column($orders, 'increment_id'));

    if ($order_index === FALSE) {
      throw new NotFoundHttpException();
    }

    $order = $orders[$order_index];

    $build = alshaya_acm_customer_build_order_detail($order);
    $build['order'] = $order;

    // Build account details array.
    $account = [];
    $account['first_name'] = $user->get('field_first_name')->getString();
    $account['last_name'] = $user->get('field_last_name')->getString();

    // If order is delivered.
    // @todo need to show the link only if attribute is present.
    $order_status_delivered = explode(',', $this->config('alshaya_acm_customer.orders_config')->get('order_status_delivered'));
    if (!in_array($order['status'], $order_status_delivered)) {
      // Download invoice link.
      $build['#download_link'] = Url::fromRoute('alshaya_acm_customer.invoice_download', ['user' => $user->id(), 'order_id' => $order_id]);
    }

    $build['#print_link'] = Url::fromRoute('alshaya_acm_customer.orders_print', ['user' => $user->id(), 'order_id' => $order_id]);
    $build['#account'] = $account;
    if ($vat_text = $this->config('alshaya_acm_product.settings')->get('vat_text')) {
      $build['#vat_text'] = $vat_text;
    }
    $build['#theme'] = 'user_order_detail';
    $build['#cache'] = ['max-age' => 0];

    return $build;
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
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Response object.
   */
  public function orderDownload(UserInterface $user, $order_id) {
    $this->moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');
    $download_invoice = FALSE;
    $error_message = $this->t('You do not have access to this invoice.');
    // Get all orders of the current user.
    $user_orders = alshaya_acm_customer_get_user_orders($this->currentUser->getEmail());
    foreach ($user_orders as $order) {
      // If order id matches.
      // @todo check for the extension attribute as well.
      if ($order['increment_id'] == $order_id && $order['email']) {
        $download_invoice = TRUE;
        break;
      }
    }

    // If user can download invoice.
    if ($download_invoice) {
      // @todo Need this url from order itself.
      $endpoint = 'order-manager/invoice/' . $order_id;
      // Request from magento to get invoice.
      $invoice_response = $this->apiWrapper->invokeApi($endpoint, [], 'GET');
      // If json_decode is not successful, means we have actual file response.
      // Otherwise we have error message which can be decoded by json.
      if (!json_decode($invoice_response)) {
        $response = new Response($invoice_response);
        $disposition = $response->headers->makeDisposition(
          ResponseHeaderBag::DISPOSITION_ATTACHMENT,
          'Invoice_' . $order_id
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
      }

      // If unable to download the invoice or error.
      $error_message = $this->t('Invoice is not found.');

    }

    // @todo need to discuss if we throw excption or this message as excption
    // will only log in logger and take user to user/uid page.
    drupal_set_message($error_message, 'error');
    // Redirect to order detail page.
    $url = Url::fromRoute('alshaya_acm_customer.orders_detail', ['user' => $this->currentUser->id(), 'order_id' => $order_id]);
    return new RedirectResponse($url->toString());
  }

}
