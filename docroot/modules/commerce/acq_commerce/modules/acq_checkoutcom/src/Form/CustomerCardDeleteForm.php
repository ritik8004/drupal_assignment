<?php

namespace Drupal\acq_checkoutcom\Form;

use Drupal\acq_checkoutcom\CheckoutComAPIWrapper;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;

/**
 * Delete the {id} purger instance.
 */
class CustomerCardDeleteForm extends ConfirmFormBase {

  /**
   * Checkout.com api wrapper object.
   *
   * @var \Drupal\acq_checkoutcom\CheckoutComAPIWrapper
   */
  protected $checkoutComApi;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * CustomerCardDeleteForm constructor.
   *
   * @param \Drupal\acq_checkoutcom\CheckoutComAPIWrapper $checkout_com_Api
   *   Checkout.com api wrapper object.
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   *   The current user object.
   */
  public function __construct(
    CheckoutComAPIWrapper $checkout_com_Api,
    AccountProxyInterface $account_proxy
  ) {
    $this->checkoutComApi = $checkout_com_Api;
    $this->currentUser = $account_proxy;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_checkoutcom.api'),
      $container->get('current_user')
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
    return new Url('acq_checkoutcom.payment_cards', ['user' => $this->getRequest()->get('user')]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, UserInterface $user = NULL, $card_id = NULL) {
    $form = parent::buildForm($form, $form_state);

    // This is rendered as a modal dialog, so we need to set some extras.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    $form['uid'] = [
      '#type' => 'hidden',
      '#value' => $user->id(),
    ];

    $form['card_id'] = [
      '#type' => 'hidden',
      '#value' => $card_id,
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
      '#ajax' => ['callback' => '::closeDialog'],
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
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    $uid = $form_state->getValue('uid');
    $card_id = $form_state->getValue('card_id');
    // Delete the card for given user.
    if (($uid == $this->currentUser->id() || $this->currentUser->hasPermission('administer site configuration') && $card_id)) {
      // Delete card script.
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Respond a CloseModalDialogCommand to close the modal dialog.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return the ajax response object.
   */
  public function closeDialog(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}
