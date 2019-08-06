<?php

namespace Drupal\alshaya_acm_promotion\Form;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_commerce\UpdateCartErrorEvent;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class SelectFreeGiftForm.
 *
 * @package Drupal\alshaya_acm_promotion\Form
 */
class SelectFreeGiftForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'select_free_gift';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#cache']['contexts'][] = 'route';

    $storage = $form_state->getStorage();
    $coupon = $storage['coupon'] ?? '';
    $sku = SKU::loadFromSku($storage['sku'] ?? '');
    if (empty($coupon) || !($sku instanceof SKUInterface)) {
      return $form;
    }

    $form['coupon'] = [
      '#type' => 'hidden',
      '#value' => $coupon,
    ];

    $form['sku'] = [
      '#type' => 'hidden',
      '#value' => $sku->getSku(),
    ];

    $form['select'] = [
      '#type' => 'button',
      '#value' => $this->t('ADD FREE GIFT'),
      '#ajax' => [
        'url' => Url::fromRoute('alshaya_acm_promotion.select_free_gift'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

}
