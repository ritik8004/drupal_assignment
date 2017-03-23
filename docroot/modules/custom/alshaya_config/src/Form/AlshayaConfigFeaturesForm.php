<?php

namespace Drupal\alshaya_config\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements a form to collect security check configuration.
 */
class AlshayaConfigFeaturesForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_config_features_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_config.settings'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getCurrentConfig() {
    $storedConfig = \Drupal::config('alshaya_config.settings');

    $config = [];

    $moduleHandler = \Drupal::service('module_handler');

    $config['alshaya_arabic'] = [
      'type' => 'module',
      'description' => t('Enable Arabic language for this site'),
      'default_value' => $moduleHandler->moduleExists('alshaya_arabic'),
    ];

    // @TODO: This is just an example for now and could be removed going
    // forward. By default language switcher doesn't show up if there is only
    // one language.
    $config['alshaya_i18n'] = [
      'type' => 'module',
      'description' => t('Enable or disable the language switcher on the site'),
      'default_value' => $moduleHandler->moduleExists('alshaya_i18n'),
    ];

    // @TODO: Below is just an example of type variable. It is not fully
    // functional as of now.
    //  @codingStandardsIgnoreStart
    //  $config['home_banner'] = [
    //   'type' => 'variable',
    //   'description' => t('Show the home page hero banner'),
    //   'default_value' => empty($storedConfig->get('home_banner')) ? 0 : 1,
    // ];
    // @codingStandardsIgnoreEnd

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->getCurrentConfig();

    foreach ($config as $configKey => $configData) {
      $form[$configKey] = [
        '#type' => 'checkbox',
        '#default_value' => $configData['default_value'],
        '#title' => $configData['description'],
        '#return_value' => 1,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the configFactory object to update variables.
    $storedConfig = \Drupal::configFactory()->getEditable('alshaya_config.settings');

    // Initialise update flags to false.
    $updatedModules = FALSE;
    $updatedVariables = FALSE;

    // Get the current configuration.
    $config = $this->getCurrentConfig();

    foreach ($config as $configKey => $configData) {
      $newStatus = $form_state->getValue($configKey);

      if ($newStatus != $configData['default_value']) {
        switch ($configData['type']) {
          case 'module':
            $updatedModules = TRUE;
            if ($newStatus) {
              \Drupal::service('module_installer')->install([$configKey]);
            }
            else {
              \Drupal::service('module_installer')->uninstall([$configKey]);
            }
            break;

          case 'variable':
            $updatedVariables = TRUE;
            $storedConfig->set($configKey, $newStatus);
            break;
        }
      }
    }

    // Call save() for config variable changes.
    if ($updatedVariables) {
      $storedConfig->save();
    }

    // Do full cache rebuild if change done in either module or variable.
    if ($updatedModules || $updatedVariables) {
      drupal_flush_all_caches();
    }
  }

}
