<?php

namespace Drupal\alshaya_checkout_by_agent\Controller;

use Drupal\alshaya_api\AlshayaApiWrapper;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for Smart Agent related callbacks.
 */
class AlshayaSmartAgentController extends ControllerBase {

  use LoggerChannelTrait;

  /**
   * Alshaya API Wrapper service.
   *
   * @var \Drupal\alshaya_api\AlshayaApiWrapper
   */
  protected $apiWrapper;

  /**
   * Constructor.
   *
   * @param \Drupal\alshaya_api\AlshayaApiWrapper $api_wrapper
   *   Alshaya API Wrapper service.
   */
  public function __construct(AlshayaApiWrapper $api_wrapper) {
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_api.api')
    );
  }

  /**
   * Page Callback to resume cart shared by agent.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   HTTP Request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect response.
   */
  public function resume(Request $request) {
    // In case of error, we redirect to cart page.
    $redirect = new RedirectResponse(Url::fromRoute('acq_cart.cart')->toString(), 302);
    $redirect->setMaxAge(0);
    $redirect->headers->set('cache-control', 'must-revalidate, no-cache, no-store, private');
    // Referring channel params and set header in case of assist app or web.
    $channel = $request->query->get('channel') ?? NULL;
    $content = $request->query->get('data');
    if (empty($content)) {
      return $redirect;
    }
    // Decrypt the data.
    $data = $this->apiWrapper->getDecryptedSmartAgentData($content, $channel);

    // Redirect to cart page if cart id or smart agent details is empty.
    if (empty($data) || empty($data['success'])) {
      $this->getLogger('AlshayaSmartAgentController')->warning('Error occurred while trying to decrypt the data, Response: @response.', [
        '@response' => json_encode($data),
      ]);

      return $redirect;
    }

    if (alshaya_acm_customer_is_customer($this->currentUser())) {
      // Associate the cart if customer logged in.
      $user = $this->entityTypeManager()->getStorage('user')->load($this->currentUser()->id());
      $customer_id = $user->get('acq_customer_id')->getString();

      $this->getLogger('AlshayaSmartAgentController')->notice('Associating cart from Smart agent to Customer. Cart ID: @cart_id, Customer ID: @customer_id.', [
        '@cart_id' => $data['cart_id'],
        '@customer_id' => $customer_id,
      ]);

      $this->apiWrapper->associateCartToCustomer($data['cart_id'], $customer_id);
    }
    else {
      // For guest users, set the guest cart id in cookie, we will read from
      // there in JS to resume the cart.
      setrawcookie('resume_cart_id', Xss::filter($data['masked_quote_id']), [
        'expires' => strtotime('+1 year'),
        'path' => '/',
      ]);
    }

    return $redirect;
  }

}
