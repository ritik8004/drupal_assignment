<?php

namespace Drupal\alshaya_master\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Class Alshaya404Settings.
 */
class Alshaya404Settings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_404_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_master.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_master.settings');
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $form['404_message'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('404 Text'),
      '#default_value' => $config->get('404_message'),
    ];

    $default_file = !empty($config->get('404_image.' . $langcode)) ? [$config->get('404_image.' . $langcode)] : [];
    $form['404_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload image'),
      '#upload_location' => 'public://404_image/',
      '#default_value' => $default_file,
      '#upload_validators'  => [
        'file_validate_extensions' => ['png gif jpg jpeg apng svg'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_master.settings');

    $values = $form_state->getValues();
    $fid = '';
    if (!empty($values['404_image'])) {
      $file = File::load($values['404_image'][0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $fid = $file->id();
        // Add file usage or file will be gone in next garbage collection.
        \Drupal::service('file.usage')->add($file, 'alshaya_master', '404_image', 1);
        // Create image style derivative.
        $style = ImageStyle::load('1284x424');
        $destination = $style->buildUri($file->getFileUri());
        $style->createDerivative($file->getFileUri(), $destination);
      }
    }

    // Get current langcode.
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $config->set('404_message', $values['404_message']['value']);
    $config->set('404_image.' . $langcode, $fid);
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
