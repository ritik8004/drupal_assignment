<?php

namespace Drupal\alshaya_master\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Class Alshaya404MaintenanceSettings.
 */
class Alshaya404MaintenanceSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_404_maintenance_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_master.maintenanace_404_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_master.maintenanace_404_settings');
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $form['404_container'] = [
      '#type' => 'details',
      '#title' => $this->t('404 Content'),
    ];
    $form['404_container']['404_message'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => $this->t('404 Text'),
      '#default_value' => $config->get('404_message.value'),
    ];

    $default_404_file = !empty($config->get('404_image.' . $langcode)) ? [$config->get('404_image.' . $langcode)] : [];
    $form['404_container']['404_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload image'),
      '#upload_location' => 'public://404_image/' . $langcode . '/',
      '#default_value' => $default_404_file,
      '#upload_validators'  => [
        'file_validate_extensions' => ['png gif jpg jpeg apng svg'],
      ],
    ];

    $form['maintenance_container'] = [
      '#type' => 'details',
      '#title' => $this->t('Maintenanace mode content'),
    ];
    $form['maintenance_container']['maintenance_mode_rich_message'] = [
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#title' => t('Message to display when in maintenance mode'),
      '#default_value' => $config->get('maintenance_mode_rich_message.value'),
    ];

    $default_maintenanace_file = !empty($config->get('maintenance_mode_image')) ? [$config->get('maintenance_mode_image')] : [];
    $form['maintenance_container']['maintenance_mode_image'] = [
      '#type' => 'managed_file',
      '#title' => t('Upload image'),
      '#upload_location' => 'public://maintenance_mode_image/',
      '#default_value' => $default_maintenanace_file,
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
    $config = $this->config('alshaya_master.maintenanace_404_settings');

    $values = $form_state->getValues();
    $fid_404 = '';
    if (!empty($values['404_image'])) {
      $file = File::load($values['404_image'][0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $fid_404 = $file->id();
        // Add file usage or file will be gone in next garbage collection.
        \Drupal::service('file.usage')->add($file, 'alshaya_master', '404_image', 1);
        // Create image style derivative.
        // Create image style derivative.
        $this->createImageStyle('1284x424', $file->getFileUri());
      }
    }

    $fid_maintenance = '';
    if (!empty($values['maintenance_mode_image'])) {
      $file = File::load($values['maintenance_mode_image'][0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $fid_maintenance = $file->id();
        // Add file usage or file will be gone in next garbage collection.
        \Drupal::service('file.usage')->add($file, 'alshaya_master', 'system_maintenance', 1);
        // Create image style derivative.
        $this->createImageStyle('1284x424', $file->getFileUri());
      }
    }

    // Get current langcode.
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $config->set('404_message', $values['404_message']);
    $config->set('404_image.' . $langcode, $fid_404);
    $config->set('maintenance_mode_rich_message', $values['maintenance_mode_rich_message']);
    $config->set('maintenance_mode_image', $fid_maintenance);
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * Creates the image style.
   *
   * @param string $image_style
   *   Image style name.
   * @param string $uri
   *   Image uri name.
   */
  protected function createImageStyle($image_style, $uri) {
    $style = ImageStyle::load($image_style);
    $destination = $style->buildUri($uri);
    $style->createDerivative($uri, $destination);
  }

}
