<?php

namespace Drupal\alshaya_acm_customer\Controller;

use Drupal\acq_checkout\ACQAddressFormatter;
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
    $orders = $this->getUserOrders($user);

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
      // Initialise pager.
      $currentPageNumber = pager_default_initialize(count($orders), $itemsPerPage);

      // Get the offset to start displaying orders from.
      $offset = $currentPageNumber * $itemsPerPage;

      // Get the orders to display for current page.
      $ordersPaged = array_slice($orders, $offset, $itemsPerPage, TRUE);

      // Loop through each order and prepare the array for template.
      foreach ($ordersPaged as $orderId => $order) {
        $orderDetails[] = [
          '#theme' => 'user_order_list_item',
          '#order' => $this->getProcessedOrderRow($orderId, $order),
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
      '#pager' => ['#type' => 'pager'],
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

    $response['pager'] = \Drupal::service('renderer')->render($fullBuild['#pager']);

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

    // Get the orders to display for current user and filter applied.
    $orders = $this->getUserOrders($user);

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
    $build['#order'] = $this->getProcessedOrderRow($order_id, $order);
    $build['#order_details'] = $this->getProcessedOrderDetails($order);
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

  /**
   * Returns orders from cache if available.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object for which the orders are required.
   *
   * @return array
   *   Array of orders.
   */
  protected function getUserOrders(UserInterface $user) {
    $orders = [];

    $cid = 'orders_list_' . $user->id();

    if ($cache = \Drupal::cache()->get($cid)) {
      $orders = $cache->data;
    }
    else {
      $orders = \Drupal::service('acq_commerce.api')->getCustomerOrders($user->getEmail());

      // Get the cache expiration time based on config value.
      $cacheTimeLimit = \Drupal::config('alshaya_acm_customer.orders_config')->get('cache_time_limit');

      // We can disable caching via config by setting it to zero.
      if ($cacheTimeLimit > 0) {
        $expire = strtotime('+' . $cacheTimeLimit . ' seconds');

        // Store in cache.
        \Drupal::cache()->set($cid, $orders, $expire);
      }
    }

    // Search by Order ID, SKU, Name.
    if ($search = \Drupal::request()->query->get('search')) {
      $orders = array_filter($orders, function ($order, $orderId) use ($search) {
        // Search by Order ID.
        if (stripos($orderId, $search) > -1) {
          return TRUE;
        }

        foreach ($order['items'] as $orderItem) {
          // Search by name.
          if (stripos($orderItem['name'], $search) > -1) {
            return TRUE;
          }
          // Search by SKU.
          elseif (stripos($orderItem['sku'], $search) > -1) {
            return TRUE;
          }
        }

        return FALSE;
      }, ARRAY_FILTER_USE_BOTH);
    }

    return $orders;
  }

  /**
   * Apply conditions and get order status.
   *
   * @param array $order
   *   Item array.
   *
   * @return string
   *   Status of order, ensure string can be used directly as class too.
   */
  private function getOrderStatus(array $order) {
    // We support only three status as of now.
    $status = ['pending' => 0, 'delivered' => 0, 'returned' => 0];

    // Check for each item status.
    foreach ($order['items'] as $item) {
      $itemStatus = $this->getOrderItemStatus($item);
      $status[$itemStatus]++;
    }

    // @TODO: Add conditions for partial delivery status - not in MVP1.
    // Check MMCPA-145 comments for more details.
    if ($status['returned'] !== 0) {
      return ['text' => $this->t('returned'), 'class' => 'status-returned'];
    }
    elseif ($status['delivered'] !== 0) {
      return ['text' => $this->t('delivered'), 'class' => 'status-delivered'];
    }

    // Finally if it is neither delivered nor returned, it is pending.
    return ['text' => $this->t('pending'), 'class' => 'status-pending'];;
  }

  /**
   * Apply conditions and get order item status.
   *
   * @param array $item
   *   Item array.
   *
   * @return string
   *   Status of item, ensure string can be used directly as class too.
   */
  private function getOrderItemStatus(array $item) {
    if (empty($item['shipped']) && empty($item['refunded'])) {
      return 'pending';
    }

    if (empty($item['refunded']) && $item['shipped'] === $item['ordered']) {
      return 'delivered';
    }

    if (empty($item['shipped']) && $item['refunded'] === $item['ordered']) {
      return 'returned';
    }

    // @TODO: Check condition for partial delivery, partial pending.
    // @TODO: Check condition for partial delivery, partial returned.
    return 'pending';
  }

  /**
   * Get total number of items in order.
   *
   * @param array $order
   *   Item array.
   *
   * @return int
   *   Number of total items in the order.
   */
  private function getOrderTotalQuantity(array $order) {
    $total = 0;

    foreach ($order['items'] as $item) {
      $total += $item['ordered'];
    }

    return $total;
  }

  /**
   * Helper function to prepare order row to pass to template.
   *
   * @param mixed $orderId
   *   Order id.
   * @param array $order
   *   Order array from API.
   *
   * @return array
   *   Processed order array.
   */
  private function getProcessedOrderRow($orderId, array $order) {
    $orderRow = [];

    // @TODO: MMCPA-612.
    $orderRow['orderId'] = $orderId;
    // @TODO: MMCPA-612.
    $orderRow['orderDate'] = '30 Nov. 2016 @ 20h55';

    // We will display the name of first order item.
    $item = reset($order['items']);
    $orderRow['name'] = $item['name'];

    // Calculate total items in the order.
    $orderRow['quantity'] = $this->getOrderTotalQuantity($order);

    // Format total to have max 3 decimals as per mockup.
    $orderRow['total'] = number_format($order['totals']['grand'], 3);

    // Calculate status of order based on status of items.
    $orderRow['status'] = $this->getOrderStatus($order);

    return $orderRow;
  }

  /**
   * Helper function to prepare order details to pass to template.
   *
   * @param array $order
   *   Order array from API.
   *
   * @return array
   *   Processed order array.
   */
  private function getProcessedOrderDetails(array $order) {
    $orderDetails = [];

    $orderDetails['delivery_to'] = $order['shipping']['address']['firstname'] . ' ' . $order['shipping']['address']['lastname'];

    // @TODO: MMCPA-641.
    $orderDetails['contact_no'] = '+965 12 34 5679';

    $address_formatter = new ACQAddressFormatter();
    $orderDetails['delivery_address'] = $address_formatter->render((object) $order['shipping']['address']);

    // @TODO: MMCPA-641.
    $orderDetails['delivery_method'] = $order['shipping']['method']['carrier_code'];
    $orderDetails['delivery_charge'] = $order['shipping']['method']['amount'];

    $orderDetails['payment_method'] = $order['payment']['method_title'];

    $orderDetails['sub_total'] = $order['totals']['sub'];
    $orderDetails['order_total'] = $order['totals']['grand'];

    return $orderDetails;
  }

}
