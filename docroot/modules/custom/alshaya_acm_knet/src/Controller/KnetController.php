<?php

namespace Drupal\alshaya_acm_knet\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class CheckoutSettingsForm.
 */
class KnetController extends ControllerBase {

  /**
   * Page callback to process the payment and return redirect URL.
   */
  public function response() {
    $paymentId = $_POST['paymentid'];
    $presult = $_POST['result'];
    $postdate = $_POST['postdate'];
    $tranid = $_POST['tranid'];
    $auth = $_POST['auth'];
    $ref = $_POST['ref'];
    $trackid = $_POST['trackid'];
    $udf1 = $_POST['udf1'];
    $udf2 = $_POST['udf2'];
    $udf3 = $_POST['udf3'];
    $udf4 = $_POST['udf4'];
    $udf5 = $_POST['udf5'];

    if ($presult == 'CAPTURED') {
      $result_url = 'https://www.knetpaytest.com.kw/php/result.php';

      $result_params = '?PaymentID=' . $paymentId . '&Result=' . $presult . '&PostDate=' . $postdate . '&TranID=' . $tranid . '&Auth=' . $auth . '&Ref=' . $ref . '&TrackID=' . $trackid . '&UDF1=' . $udf1 . '&UDF2=' . $udf2 . '&UDF3=' . $udf3 . '&UDF4=' . $udf4 . '&UDF5=' . $udf5;
    }
    else {
      $result_url = 'https://www.knetpaytest.com.kw/php/error.php';
      $result_params = '?PaymentID=' . $paymentId . '&Result=' . $presult . '&PostDate=' . $postdate . '&TranID=' . $tranid . '&Auth=' . $auth . '&Ref=' . $ref . '&TrackID=' . $trackid . '&UDF1=' . $udf1 . '&UDF2=' . $udf2 . '&UDF3=' . $udf3 . '&UDF4=' . $udf4 . '&UDF5=' . $udf5;

    }

    print 'REDIRECT=' . $result_url . $result_params;
    exit;
  }

  /**
   * Page callback for error state.
   *
   * @return array
   *   Build array.
   */
  public function error($cart_id) {
    // @TODO: Update the cart payment method and redirect user to payment page.
    $paymentID = $_GET['PaymentID'];

    $build = [
      '#markup' => $this->t('Some error occurred.'),
    ];

    return $build;
  }

}
