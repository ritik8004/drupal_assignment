<?php

namespace Drupal\alshaya_acm_checkoutcom\Form;

use Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper;
use Drupal\Core\Ajax\RedirectCommand;
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
   * Checkout.com API Helper.
   *
   * @var \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper
   */
  protected $apiHelper;

  /**
   * A request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * CustomerCardDeleteForm constructor.
   *
   * @param \Drupal\alshaya_acm_checkoutcom\Helper\AlshayaAcmCheckoutComAPIHelper $api_helper
   *   Checkout.com API Helper.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request object.
   */
  public function __construct(
    AlshayaAcmCheckoutComAPIHelper $api_helper,
    RequestStack $requestStack
  ) {
    $this->apiHelper = $api_helper;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm_checkoutcom.api_helper'),
      $container->get('request_stack'),
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
    return $this->t('delete card');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Are you sure you want to delete this card?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Yes, delete this card');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return $this->t('No, take me back');
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
      '#value' => $this->getCancelText(),
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
    $public_hash = $form_state->getValue('public_hash');

    // Delete the card for given user.
    if ($this->apiHelper->deleteCustomerCard($public_hash)) {
      $this->messenger()->addStatus(
        $this->t('Your card has been deleted.')
      );
    }
    else {
      $this->messenger()->addError(
        $this->t('Could not delete your card, please try again later.')
      );
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
