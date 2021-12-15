<?php

namespace Drupal\alshaya_js_optimisations\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Alshaya JS Performance Config Form.
 */
class AlshayaJSPerformanceConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_js_optimisations';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_js_optimisations.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_js_optimisations.settings');

    $form['enable_uglification'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable JS Uglification'),
      '#description' => $this->t('Uglification removes whitespaces, minfies variables and function names on the scripts. This will reduce the script size and its download size by a small percentage.'),
      '#default_value' => $config->get('enable_uglification'),
    ];

    $form['critical_js'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Critical JS Settings'),
      '#description' => $this->t('Critical JS functionality helps to prioritise loading of important scripts. Other non critical scripts will be deprioritised and loaded asynchronously. Please configure this settings properly as the wrong settings can break the application and/or give no performance gains.'),
    ];

    $critical_js = $config->get('critical_js');

    $form['critical_js']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Critical JS'),
      '#default_value' => isset($critical_js['enabled']) ? $critical_js['enabled'] : FALSE,
    ];

    $form['critical_js']['critical'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Critical Scripts'),
      '#placeholder' => $this->t('Enter YAML formatted text'),
      '#description' => $this->t('JS asset libraries to load with high priority on pageload. They render content and interactions above the fold, that are immediately visible on pageload. They are also responsibe for any critical and highlighting functionality on a page.'),
      '#rows' => 10,
      '#states' => [
        'disabled' => [
          ':input[name="enabled"]' => ['checked' => FALSE],
        ],
      ],
      '#default_value' => isset($critical_js['critical']) ? $critical_js['critical'] : '',
    ];

    $form['critical_js']['sitewide'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Sitewide Scripts'),
      '#placeholder' => $this->t('Enter YAML formatted text'),
      '#description' => $this->t('JS asset libraries used on all pages throughout the site. These are libraries and initialisation codes that are required by all pages.'),
      '#rows' => 10,
      '#states' => [
        'disabled' => [
          ':input[name="enabled"]' => ['checked' => FALSE],
        ],
      ],
      '#default_value' => isset($critical_js['sitewide']) ? $critical_js['sitewide'] : '',
    ];

    $form['critical_js']['ie_only'] = [
      '#type' => 'textarea',
      '#title' => $this->t('IE Only Scripts'),
      '#placeholder' => $this->t('Enter YAML formatted text'),
      '#description' => $this->t('JS asset library polyfills used only to provide compatibility with IE.'),
      '#rows' => 5,
      '#states' => [
        'disabled' => [
          ':input[name="enabled"]' => ['checked' => FALSE],
        ],
      ],
      '#default_value' => isset($critical_js['ie_only']) ? $critical_js['ie_only'] : '',
    ];

    $warning_message = $this->t('WARNING: Saving this form will rebuild cache on JS files. The first load of site pages will be slower than usual.');
    $form['warning'] = [
      '#type' => 'markup',
      '#markup' => '<div class="txt-warning">' . $warning_message . '</div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate YAML syntax.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_js_optimisations.settings');
    $critical_js = [
      'enabled' => $form_state->getValue('enabled'),
      'critical' => $form_state->getValue('critical'),
      'sitewide' => $form_state->getValue('sitewide'),
      'ie_only' => $form_state->getValue('ie_only'),
    ];
    $config->set('critical_js', $critical_js);
    $config->set('enable_uglification', $form_state->getValue('enable_uglification'));
    $config->save();

    // Clear JS file / aggregates Cache.
    // Trigger hook_library_info_alter.
    return parent::submitForm($form, $form_state);
  }

}
