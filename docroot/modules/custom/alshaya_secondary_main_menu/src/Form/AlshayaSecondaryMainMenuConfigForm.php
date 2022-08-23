<?php

namespace Drupal\alshaya_secondary_main_menu\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Alshaya secondary_main Menu Config Form.
 */
class AlshayaSecondaryMainMenuConfigForm extends ConfigFormBase {

  /**
   * Default secondary_main menu layout.
   */
  public const SECONDARY_MAIN_MENU_DEFAULT_LAYOUT = 'default';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_secondary_main_menu';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_secondary_main_menu.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_secondary_main_menu.settings');

    $form['desktop_secondary_main_menu_layout'] = [
      '#type' => 'select',
      '#options' => [
        self::SECONDARY_MAIN_MENU_DEFAULT_LAYOUT => $this->t('Default menu display'),
      ],
      '#default_value' => $config->get('desktop_secondary_main_menu_layout'),
      '#title' => $this->t('secondary main menu display on desktop'),
      '#description' => $this->t('Select inline menu display option to display the l3 option inline to l2 otherwise it will follow the core.'),
    ];

    $form['max_nb_col'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum number of columns to show'),
      '#description' => $this->t('Set the maximum number of columns to show.'),
      '#default_value' => (!empty($config->get('max_nb_col'))) ? $config->get('max_nb_col') : 6,
    ];

    $form['ideal_max_col_length'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ideal length of the column'),
      '#description' => $this->t('Set ideal length of a column'),
      '#default_value' => (!empty($config->get('ideal_max_col_length'))) ? $config->get('ideal_max_col_length') : 10,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_secondary_main_menu.settings');
    $config->set('desktop_secondary_main_menu_layout', $form_state->getValue('desktop_secondary_main_menu_layout'));
    $config->set('max_nb_col', $form_state->getValue('max_nb_col'));
    $config->set('ideal_max_col_length', $form_state->getValue('ideal_max_col_length'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
