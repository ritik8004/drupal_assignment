<?php

namespace Drupal\alshaya_loyalty\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CartConfigForm.
 */
class JoinClubConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_loyalty_join_club';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_loyalty.join_club'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_loyalty.join_club');

    $image = $form_state->getValue('image');
    $image = $image ? reset($image) : '';
    $config->set('join_club_image.fid', $image);

    $config->set('join_club_description', $form_state->getValue('description'));

    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_loyalty.join_club');

    $form['image'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://',
      '#format' => 'rich_text',
      '#title' => $this->t('Image'),
      '#description' => $this->t('Leave blank to use default from code.'),
      '#default_value' => $config->get('join_club_image'),
    ];

    $form['description'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('Description'),
      '#required' => TRUE,
      '#default_value' => $config->get('join_club_description.value'),
    ];

    return $form;
  }

}
