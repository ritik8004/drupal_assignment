<?php

namespace Drupal\alshaya_acm_knet\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class CheckoutSettingsForm.
 */
class KnetController extends ControllerBase {

  /**
   * Page callback to process the payment and return redirect URL.
   */
  public function response() {
    $response['payment_id'] = $_POST['paymentid'];
    $response['result'] = $_POST['result'];
    $response['post_date'] = $_POST['postdate'];
    $response['transaction_id'] = $_POST['tranid'];
    $response['auth'] = $_POST['auth'];
    $response['ref'] = $_POST['ref'];
    $response['tracking_id'] = $_POST['trackid'];
    $response['user_id'] = $_POST['udf1'];
    $response['customer_id'] = $_POST['udf2'];
    $response['order_id'] = $_POST['udf3'];
    $response['internal_order_id'] = $_POST['udf4'];

    $result_params = '?response=' . base64_encode(serialize($response));

    if ($response['result'] == 'CAPTURED') {
      $result_url = Url::fromRoute('alshaya_acm_knet.success', ['order_id' => $response['order_id']], ['absolute' => TRUE])->toString();
    }
    else {
      $result_url = Url::fromRoute('alshaya_acm_knet.error', ['order_id' => $response['order_id']], ['absolute' => TRUE])->toString();
    }

    print 'REDIRECT=' . $result_url . $result_params;
    exit;
  }

  /**
   * Page callback for success state.
   */
  public function success($order_id) {
    $data = unserialize(base64_decode(\Drupal::request()->get('response')));

    if (empty($data) || $data['order_id'] != $order_id) {
      return new AccessDeniedHttpException();
    }

    // @TODO: Update the order payment method and redirect user to payment page.
    // @TODO: We need to redirect to confirmation page after processing.
  }

  /**
   * Page callback for error state.
   *
   * @return array
   *   Build array.
   */
  public function error($order_id) {
    $data = unserialize(base64_decode(\Drupal::request()->get('response')));

    if (empty($data) || $data['order_id'] != $order_id) {
      // Error from our side, K-Net error won't have this params.
    }

    $build = [
      '#markup' => $this->t('Some error occurred.'),
    ];

    return $build;
  }

}
