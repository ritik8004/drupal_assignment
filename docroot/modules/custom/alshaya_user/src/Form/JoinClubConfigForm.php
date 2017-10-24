<?php

namespace Drupal\alshaya_user\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Class CartConfigForm.
 */
class JoinClubConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_user_join_club';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_user.join_club'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_user.join_club');

    $image = $form_state->getValue('image');
    $image = $image ? reset($image) : '';
    if (isset($image[0]) && $file = File::load($image[0])) {
      $file->setPermanent();
      $file->save();
    }
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

    $config = $this->config('alshaya_user.join_club');

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
      '#format' => !empty($config->get('join_club_description.format')) ? $config->get('join_club_description.format') : 'rich_text',
      '#title' => $this->t('Description'),
      '#required' => TRUE,
      '#default_value' => $config->get('join_club_description.value'),
    ];

    return $form;
  }

}
