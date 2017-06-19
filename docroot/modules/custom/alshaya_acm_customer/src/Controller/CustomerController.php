<?php

namespace Drupal\alshaya_acm_customer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Drupal\block\Entity\Block;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    // Build account details array.
    $account = [];
    $account['first_name'] = $user->get('field_first_name')->getString();
    $account['last_name'] = $user->get('field_last_name')->getString();

    // Get the search form.
    $searchForm = \Drupal::formBuilder()->getForm('Drupal\alshaya_acm_customer\Form\OrderSearchForm');
    $searchForm['form_id']['#printed'] = TRUE;
    $searchForm['form_build_id']['#printed'] = TRUE;

    // Get the orders to display for current user and filter applied.
    $orders = alshaya_acm_customer_get_user_orders($user->getEmail(), 'search', 'filter');

    // Initialising order details array to array.
    $orderDetails = [];
    $nextPageButton = [];
    $noOrdersFoundMessage = ['#markup' => ''];
    $help_block = NULL;

    if (empty($orders)) {
      // @TODO: Check the empty result message.
      if ($search = \Drupal::request()->query->get('search')) {
        $noOrdersFoundMessage['#markup'] = '<div class="no--orders">' . $this->t('Your search yielded no results, please try different text in search.') . '</div>';
      }
      else {
        // Below message is taken from https://zpl.io/Oqv1o mockup.
        $noOrdersFoundMessage['#markup'] = '<div class="no--orders">' . $this->t('You haven’t ordered anything recently.') . '</div>';
      }
    }
    else {
      // Get current page number.
      $currentPageNumber = (int) \Drupal::request()->query->get('page');

      // Get the offset to start displaying orders from.
      $offset = $currentPageNumber * $itemsPerPage;

      // Get the orders to display for current page.
      $ordersPaged = array_slice($orders, $offset, $itemsPerPage, TRUE);

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
      foreach ($ordersPaged as $order) {
        $orderDetails[] = [
          '#theme' => 'user_order_list_item',
          '#order' => alshaya_acm_customer_get_processed_order_summary($order),
          '#order_detail_link' => Url::fromRoute('alshaya_acm_customer.orders_detail', ['user' => $user->id(), 'order_id' => $order['increment_id']])->toString(),
        ];
      }

      // Load my-account-help block for rendering on order list page.
      $help_block_entity = Block::load('myaccountneedhelp');
      if ($help_block_entity) {
        $help_block = \Drupal::entityTypeManager()->getViewBuilder('block')->view($help_block_entity);
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
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

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

    $build['#print_link'] = Url::fromRoute('alshaya_acm_customer.orders_print', ['user' => $user->id(), 'order_id' => $order_id]);
    $build['#account'] = $account;
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
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    // Get order details and add more information for print.
    $build = $this->orderDetail($user, $order_id);

    $build['#barcode'] = alshaya_acm_customer_get_barcode($build['order']);
    $build['#account']['mail'] = $user->get('mail')->getString();
    $build['#account']['privilege_card_number'] = $user->get('field_privilege_card_number')->getString();
    $build['#site_logo'] = [
      '#theme' => 'image',
      '#uri' => theme_get_setting('logo.url'),
    ];
    $build['#theme'] = 'user_order_print';

    return $build;
  }

  /**
   * Controller function to show print view of last order for anonymous users.
   *
   * @return array
   *   Build array.
   */
  public function orderPrintLast() {
    \Drupal::moduleHandler()->loadInclude('alshaya_acm_customer', 'inc', 'alshaya_acm_customer.orders');

    $temp_store = \Drupal::service('user.private_tempstore')->get('alshaya_acm_checkout');
    $order_data = $temp_store->get('order');
    $email = $temp_store->get('email');

    // Throw access denied if nothing in session.
    if (empty($order_data) || empty($order_data['id']) || empty($email)) {
      throw new AccessDeniedHttpException();
    }

    // @TODO: Remove the fix when we get the full order details.
    $order_id = str_replace('"', '', $order_data['id']);

    // Get the orders to display for current user.
    $orders = alshaya_acm_customer_get_user_orders($email);

    $order_index = array_search($order_id, array_column($orders, 'order_id'));

    if ($order_index === FALSE) {
      throw new NotFoundHttpException();
    }

    $order = $orders[$order_index];

    $build = alshaya_acm_customer_build_order_detail($order);

    // Build account details array.
    $account = [];

    // @TODO: Get privilege card number once integration done.
    $account['first_name'] = $order['firstname'];
    $account['last_name'] = $order['lastname'];
    $account['mail'] = $order['email'];

    $build['#site_logo'] = [
      '#theme' => 'image',
      '#uri' => theme_get_setting('logo.url'),
    ];
    $build['#barcode'] = alshaya_acm_customer_get_barcode($order);
    $build['#account'] = $account;
    $build['#theme'] = 'user_order_print';

    return $build;
  }

}
