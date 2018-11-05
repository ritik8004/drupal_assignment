<?php

namespace Drupal\alshaya_main_menu\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;

/**
 * Class AlshayaMainMenuConfigForm.
 */
class AlshayaMainMenuConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_main_menu';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_main_menu.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_main_menu.settings');

    $form['mobile_main_menu_max_depth'] = [
      '#type' => 'select',
      '#title' => $this->t('Main menu maximum depth for mobile.'),
      '#description' => $this->t('Set the maixmum depth to display menu levels for mobile. 0 for not to restrict.'),
      '#options' => range(0, 6),
      '#default_value' => $config->get('mobile_main_menu_max_depth'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_main_menu.settings');
    $config->set('mobile_main_menu_max_depth', $form_state->getValue('mobile_main_menu_max_depth'));
    $config->save();
    Cache::invalidateTags([ProductCategoryTree::CACHE_TAG]);

    return parent::submitForm($form, $form_state);
  }

}
