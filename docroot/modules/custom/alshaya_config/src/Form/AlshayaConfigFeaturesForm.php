<?php

namespace Drupal\alshaya_config\Form;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a form to collect security check configuration.
 */
class AlshayaConfigFeaturesForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The module installer service object.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * Constructs a \Drupal\alshaya_config\FeaturesForm object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer service object.
   */
  public function __construct(ModuleHandlerInterface $module_handler,
                              ModuleInstallerInterface $module_installer) {
    $this->moduleHandler = $module_handler;
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('module_installer')
    );
  }

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
    $storedConfig = $this->config('alshaya_config.settings');

    $config = [];

    $config['alshaya_arabic'] = [
      'type' => 'module',
      'description' => $this->t('Enable Arabic language for this site'),
      'default_value' => $this->moduleHandler->moduleExists('alshaya_arabic'),
    ];

    // @todo This is just an example for now and could be removed going
    // forward. By default language switcher doesn't show up if there is only
    // one language.
    $config['alshaya_i18n'] = [
      'type' => 'module',
      'description' => $this->t('Enable or disable the language switcher on the site'),
      'default_value' => $this->moduleHandler->moduleExists('alshaya_i18n'),
    ];

    // @todo Below is just an example of type variable. It is not fully
    // functional as of now.
    //  phpcs:disable
    //  $config['home_banner'] = [
    //   'type' => 'variable',
    //   'description' => t('Show the home page hero banner'),
    //   'default_value' => empty($storedConfig->get('home_banner')) ? 0 : 1,
    // ];
    // phpcs:enable

    // Config to enable AND operator on Search and PLP pages.
    if ($this->moduleHandler->moduleExists('alshaya_search')) {
      $config['alshaya_search_and_operator'] = [
        'type' => 'variable',
        'description' => $this->t('Use AND operator for Search. Defaults to OR.'),
        'default_value' => $storedConfig->get('alshaya_search_and_operator'),
      ];
    }

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
    $storedConfig = $this->configFactory()->getEditable('alshaya_config.settings');

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
              $this->moduleInstaller->install([$configKey]);
            }
            else {
              $this->moduleInstaller->uninstall([$configKey]);
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
