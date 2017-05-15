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
    echo 'REDIRECT=' . $result_url . $result_params;
  }

  /**
   * Page callback for error state.
   *
   * @return array
   *   Build array.
   */
  public function error() {
    $paymentID = $_GET['PaymentID'];
    $result = $_GET['Result'];
    $postdate = $_GET['PostDate'];
    $tranid = $_GET['TranID'];
    $auth = $_GET['Auth'];
    $ref = $_GET['Ref'];
    $trackid = $_GET['TrackID'];
    $udf1 = $_GET['UDF1'];
    $udf2 = $_GET['UDF2'];
    $udf3 = $_GET['UDF3'];
    $udf4 = $_GET['UDF4'];
    $udf5 = $_GET['UDF5'];

    $build = [
      '#markup' => $this->t('Some error occurred.'),
    ];

    return $build;
  }

}
