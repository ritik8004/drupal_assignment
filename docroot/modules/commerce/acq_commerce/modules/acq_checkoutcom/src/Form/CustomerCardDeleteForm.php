<?php

namespace Drupal\acq_checkoutcom\Form;

use Drupal\acq_checkoutcom\ApiHelper;
use Drupal\acq_checkoutcom\CheckoutComAPIWrapper;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Delete the saved card of user.
 */
class CustomerCardDeleteForm extends ConfirmFormBase {

  /**
   * Checkout.com api wrapper object.
   *
   * @var \Drupal\acq_checkoutcom\CheckoutComAPIWrapper
   */
  protected $checkoutComApi;

  /**
   * The api helper object.
   *
   * @var \Drupal\acq_checkoutcom\ApiHelper
   */
  protected $apiHelper;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * A request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * CustomerCardDeleteForm constructor.
   *
   * @param \Drupal\acq_checkoutcom\CheckoutComAPIWrapper $checkout_com_Api
   *   Checkout.com api wrapper object.
   * @param \Drupal\acq_checkoutcom\ApiHelper $api_helper
   *   The api helper object.
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   *   The current user object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request object.
   */
  public function __construct(
    CheckoutComAPIWrapper $checkout_com_Api,
    ApiHelper $api_helper,
    AccountProxyInterface $account_proxy,
    MessengerInterface $messenger,
    RequestStack $requestStack
  ) {
    $this->checkoutComApi = $checkout_com_Api;
    $this->apiHelper = $api_helper;
    $this->currentUser = $account_proxy;
    $this->messenger = $messenger;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_checkoutcom.checkout_api'),
      $container->get('acq_checkoutcom.agent_api'),
      $container->get('current_user'),
      $container->get('messenger'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'payment_card_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete this card?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This will delete the saved card. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return $this->t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('acq_checkoutcom.payment_cards', ['user' => $this->getRequest()->get('user')->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, UserInterface $user = NULL, $public_hash = NULL) {
    $form = parent::buildForm($form, $form_state);

    // This is rendered as a modal dialog, so we need to set some extras.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    $form['uid'] = [
      '#type' => 'hidden',
      '#value' => $user->id(),
    ];

    $form['customer_id'] = [
      '#type' => 'hidden',
      '#value' => $user->get('acq_customer_id')->getString(),
    ];

    $form['public_hash'] = [
      '#type' => 'hidden',
      '#value' => $public_hash,
    ];

    // Update the buttons and bind callbacks.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->getConfirmText(),
      '#ajax' => ['callback' => '::deleteCard'],
    ];

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('No'),
      '#weight' => -10,
      '#attributes' => [
        'class' => ['button', 'dialog-cancel'],
      ],
    ];

    return $form;
  }

  /**
   * Delete the purger.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return the ajax response object.
   */
  public function deleteCard(array &$form, FormStateInterface $form_state) {
    $uid = $form_state->getValue('uid');
    $public_hash = $form_state->getValue('public_hash');
    // Delete the card for given user.
    if (($uid == $this->currentUser->id() || $this->currentUser->hasPermission('administer users')) && $public_hash) {
      $user = $form_state->getBuildInfo()['args'][0];
      if (!$this->apiHelper->deleteCustomerCard($user, $public_hash)) {
        $this->messenger->addError(
          $this->t('Could not delete your card, please try again later.')
        );
      }
      else {
        $this->messenger->addStatus(
          $this->t('Your card has been deleted.')
        );
      }
      Cache::invalidateTags(['user:' . $uid]);
    }
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    $destination = $this->request->query->get('destination');
    if (empty($destination)) {
      $destination = $this->getCancelUrl()->toString();
    }
    $response->addCommand(new RedirectCommand($destination));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
