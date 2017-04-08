<?php

namespace Drupal\alshaya_acm_customer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\UserInterface;

/**
 * Customer controller to add/override pages for customer.
 */
class CustomerController extends ControllerBase {

  /**
   * Returns the build to the orders list page.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders list page is being viewed.
   *
   * @return array
   *   Build array.
   */
  public function listOrders(UserInterface $user = NULL) {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $build = [];

    // Get items to show per page from config.
    $itemsPerPage = \Drupal::config('alshaya_acm_customer.orders_config')->get('items_per_page');

    // Get the currency code and position.
    $currencyCode = \Drupal::config('acq_commerce.currency')->get('currency_code');
    $currencyCodePosition = \Drupal::config('acq_commerce.currency')->get('currency_code_position');

    // Build account details array.
    $account = [];
    $account['first_name'] = $user->get('field_first_name')->getString();
    $account['last_name'] = $user->get('field_last_name')->getString();

    // Get the search form.
    $searchForm = \Drupal::formBuilder()->getForm('Drupal\alshaya_acm_customer\Form\OrderSearchForm');
    $searchForm['form_id']['#printed'] = TRUE;
    $searchForm['form_build_id']['#printed'] = TRUE;

    // Get the orders to display for current user and filter applied.
    $orders = alshaya_acm_customer_get_user_orders($user, 'search', 'filter');

    // Initialising order details array to array.
    $orderDetails = [];

    $noOrdersFoundMessage = ['#markup' => ''];

    if (empty($orders)) {
      // @TODO: Check the empty result message.
      if ($search = \Drupal::request()->query->get('search')) {
        $noOrdersFoundMessage['#markup'] = $this->t('Your search yielded no results, please try different text in search.');
      }
      else {
        // Below message is taken from https://zpl.io/Oqv1o mockup.
        $noOrdersFoundMessage['#markup'] = $this->t('You haven’t ordered anything recently.');
      }
    }
    else {
      // Get current page number.
      $currentPageNumber = (int) \Drupal::request()->query->get('page');

      // Get the offset to start displaying orders from.
      $offset = $currentPageNumber * $itemsPerPage;

      // Get the orders to display for current page.
      $ordersPaged = array_slice($orders, $offset, $itemsPerPage, TRUE);

      $nextPageButton = [];

      if (count($orders) > $offset + $itemsPerPage) {
        // Get all the query parameters we currently have.
        $query = \Drupal::request()->query->all();
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
      foreach ($ordersPaged as $orderId => $order) {
        $orderDetails[] = [
          '#theme' => 'user_order_list_item',
          '#order' => alshaya_acm_customer_get_processed_order_summary($orderId, $order),
          '#order_detail_link' => Url::fromRoute('alshaya_acm_customer.orders_detail', ['user' => $user->id(), 'order_id' => $orderId])->toString(),
          '#currency_code' => $currencyCode,
          '#currency_code_position' => $currencyCodePosition,
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
      '#attached' => [
        'library' => ['alshaya_acm_customer/orders-list-infinite-scroll'],
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
      $response['orders_list'] .= '<li>' . \Drupal::service('renderer')->render($order) . '</li>';
    }

    $response['next_page_button'] = \Drupal::service('renderer')->render($fullBuild['#next_page_button']);

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
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_product', 'inc', 'alshaya_acm_product.utility');
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    // Get the orders to display for current user and filter applied.
    $orders = alshaya_acm_customer_get_user_orders($user);

    $order = $orders[$order_id];

    if (empty($order)) {
      throw new NotFoundHttpException();
    }

    $products = [];
    foreach ($order['items'] as $item) {
      $product = $item;
      $product['total'] = number_format($item['ordered'] * $item['price'], 3);

      try {
        // Check if we can find a parent SKU for this.
        $parentSku = alshaya_acm_product_get_parent_sku_by_sku($item['sku']);

        // We will use the parent SKU name for display.
        $product['name'] = $parentSku->label();

        // Try to find attributes to display for this product.
        $product['attributes'] = alshaya_acm_product_get_sku_configurable_values($item['sku']);
      }
      catch (\Exception $e) {
        // Current SKU seems to be a simple one, we don't need to do anything.
      }

      $product['image'] = '';

      // Load sku from item_id that we have in $item.
      $media = alshaya_acm_product_get_sku_media($item['sku']);

      // If we have image for the product.
      if (!empty($media)) {
        $image = array_shift($media);
        $file_uri = $image->getFileUri();
        $product['image'] = ImageStyle::load('checkout_summary_block_thumbnail')->buildUrl($file_uri);
      }

      $products[] = $product;
    }

    // Get the currency code and position.
    $currencyCode = \Drupal::config('acq_commerce.currency')->get('currency_code');
    $currencyCodePosition = \Drupal::config('acq_commerce.currency')->get('currency_code_position');

    // Build account details array.
    $account = [];
    $account['first_name'] = $user->get('field_first_name')->getString();
    $account['last_name'] = $user->get('field_last_name')->getString();

    $build = [];
    $build['#order'] = alshaya_acm_customer_get_processed_order_summary($order_id, $order);
    $build['#order_details'] = alshaya_acm_customer_get_processed_order_details($order);
    $build['#products'] = $products;
    // @TODO: MMCPA-641.
    $build['#delivery_detail_notice'] = $this->t('Your order will be delivered between 1 and 3 days');
    $build['#account'] = $account;
    $build['#currency_code'] = $currencyCode;
    $build['#currency_code_position'] = $currencyCodePosition;
    $build['#theme'] = 'user_order_detail';
    $build['#cache'] = ['max-age' => 0];

    return $build;
  }

}
