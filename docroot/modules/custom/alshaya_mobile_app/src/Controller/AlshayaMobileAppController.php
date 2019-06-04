<?php

namespace Drupal\alshaya_mobile_app\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class AlshayaMobileAppController.
 */
class AlshayaMobileAppController extends ControllerBase {

  /**
   * Get control back from knet on error.
   *
   * @param string $state_key
   *   Statue Key.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to final page.
   */
  public function mobileError(string $state_key) {
    $data = $this->state()->get($state_key);
    if (empty($data)) {
      $this->getLogger('alshaya_mobile_app')->warning('KNET mobile error page requested with invalid state_key: @state_key', [
        '@state_key' => $state_key,
      ]);
      throw new AccessDeniedHttpException();
    }
    $data['status'] = 'error';
    $this->state()->set($state_key, $data);
    return $this->redirect('alshaya_mobile_app.mobile_final');
  }

  /**
   * Get control back from knet on successful transaction flow.
   *
   * And update status in state variable based on success or failure of payment.
   *
   * @param string $state_key
   *   State Key.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect to final page.
   */
  public function mobileComplete(string $state_key) {
    $data = $this->state()->get($state_key);
    if (empty($data)) {
      $this->getLogger('alshaya_mobile_app')->warning('KNET mobile finalize page requested with invalid state_key: @state_key', [
        '@state_key' => $state_key,
      ]);
      throw new AccessDeniedHttpException();
    }
    $data['status'] = ($data['result'] == 'CAPTURED') ? 'success' : 'failed';
    $this->state()->set($state_key, $data);
    return $this->redirect('alshaya_mobile_app.mobile_final');
  }

  /**
   * Empty controller for mobile to get controller back.
   */
  public function mobileFinal() {
    exit;
  }

}
