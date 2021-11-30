<?php

namespace Drupal\alshaya_egift_card\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check balance modal form class.
 */
class AlshayaBalanceReplaceForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'check_balance_replace_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {
    $form['#prefix'] = '<div id="check-balance-replace-form">';
    $form['#suffix'] = '</div>';

    $form['card_balance'] = [
      '#type' => 'item',
      '#markup' => '<div class="balance-title">' . $this->t('Here is your card balance', [], ['context' => 'egift']) . '</div><div id="balance"></div><div id="card-details">' . $this->t('for eGift card ending in ..', [], ['context' => 'egift']) . ' <span id="card-number"></span></div><div id="validity-details">' . $this->t('Card Valid Up To', [], ['context' => 'egift']) .  '<span id="validity"></span></div>',
    ];

    $link_url = Url::fromRoute('alshaya_egift_card.check_balance');
    $link_url->setOptions([
      'attributes' => [
        'class' => ['use-ajax', 'button', 'button--small', 'secondary', 'btn', 'btn-secondary'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['height' => 'auto', 'width' => 'auto']),
        'role' => 'button',
      ]
    ]);

    $form['topup'] = [
      '#type' => 'item',
      '#markup' => Link::fromTextAndUrl($this->t('TOP UP CARD', [], ['context' => 'egift']), $link_url)->toString(),
    ];

    $form['check_another_card'] = [
      '#type' => 'item',
      '#markup' => Link::fromTextAndUrl($this->t('CHECK ANOTHER CARD', [], ['context' => 'egift']), $link_url)->toString(),
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
