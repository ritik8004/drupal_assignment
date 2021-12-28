<?php

namespace Drupal\alshaya_js_optimisations\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_js_optimisations\JsOptimisationsConfig;
use Drupal\Core\Asset\LibraryDiscovery;
use Drupal\Core\Routing\RouteBuilderInterface;

/**
 * Class Alshaya JS Performance Config Form.
 */
class AlshayaJSPerformanceConfigForm extends ConfigFormBase {

  /**
   * The JS optimisation config.
   *
   * @var \Drupal\alshaya_js_optimisations\JsOptimisationsConfig
   */
  protected $jsOptimisation;

  /**
   * The Library Discovery object.
   *
   * @var \Drupal\Core\Asset\LibraryDiscovery
   */
  protected $libraryDiscovery;

  /**
   * The Route builder object.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * Constructs a JS optimisation config object.
   *
   * @param \Drupal\alshaya_js_optimisations\JsOptimisationsConfig $js_optimsation
   *   Optimisations config methods and variables.
   * @param \Drupal\Core\Asset\LibraryDiscovery $library_discovery
   *   Library Discovery methods and variables.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   Route builder methods and variables.
   */
  public function __construct(JsOptimisationsConfig $js_optimsation, LibraryDiscovery $library_discovery, RouteBuilderInterface $router_builder) {
    $this->jsOptimisation = $js_optimsation;
    $this->libraryDiscovery = $library_discovery;
    $this->routerBuilder = $router_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_js_optimisations.config'),
      $container->get('library.discovery'),
      $container->get('router.builder'),
    );
  }

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
   *
   * @todo Add accordion help texts.
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

    $form['critical_js']['site_libraries'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Site Libraries'),
      '#placeholder' => $this->t('Enter YAML formatted text'),
      '#description' => $this->t('JS asset libraries to load with high priority on pageload. They render content and interactions above the fold, that are immediately visible on pageload. They are also responsibe for any critical and highlighting functionality on a page.'),
      '#rows' => 10,
      '#states' => [
        'disabled' => [
          ':input[name="enabled"]' => ['checked' => FALSE],
        ],
      ],
      '#default_value' => isset($critical_js['site_libraries']) ? $critical_js['site_libraries'] : '',
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

    $form['critical_js']['sitewide_1'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Sitewide Scripts 1'),
      '#placeholder' => $this->t('Enter YAML formatted text'),
      '#description' => $this->t('JS asset libraries used on all pages throughout the site. These are libraries and initialisation codes that are required by all pages.'),
      '#rows' => 10,
      '#states' => [
        'disabled' => [
          ':input[name="enabled"]' => ['checked' => FALSE],
        ],
      ],
      '#default_value' => isset($critical_js['sitewide_1']) ? $critical_js['sitewide_1'] : '',
    ];

    $form['critical_js']['sitewide_2'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Sitewide Scripts 2'),
      '#placeholder' => $this->t('Enter YAML formatted text'),
      '#description' => $this->t('JS asset libraries used on all pages throughout the site. These are libraries and initialisation codes that are required by all pages.'),
      '#rows' => 10,
      '#states' => [
        'disabled' => [
          ':input[name="enabled"]' => ['checked' => FALSE],
        ],
      ],
      '#default_value' => $critical_js['sitewide_2'],
    ];

    // Hidden field to save processed JS categories with dependencies.
    $form['critical_js']['hidden_data'] = [
      '#type' => 'hidden',
      '#value' => isset($critical_js['hidden_data']) ? $critical_js['hidden_data'] : '',
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
    $fields = JsOptimisationsConfig::$jsCategory;
    foreach ($fields as $field => $attributes) {
      try {
        Yaml::decode($form_state->getValue($field));
      }
      catch (\Exception $e) {
        $form_state->setErrorByName($field, $this->t('The import failed with the following message: %message', ['%message' => $e->getMessage()]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_js_optimisations.settings');
    $critical_js = [
      'enabled' => $form_state->getValue('enabled'),
      'critical' => $form_state->getValue('critical'),
      'site_libraries' => $form_state->getValue('site_libraries'),
      'sitewide_1' => $form_state->getValue('sitewide_1'),
      'sitewide_2' => $form_state->getValue('sitewide_2'),
      'ie_only' => $form_state->getValue('ie_only'),
    ];

    // Adding value to the hidden field.
    if ($critical_js['enabled']) {
      $hidden_data = $this->jsOptimisation->resolveCategories($critical_js);
      $critical_js['hidden_data'] = Yaml::encode($hidden_data);
    }
    else {
      $critical_js['hidden_data'] = [];
    }

    $config->set('critical_js', $critical_js);
    $config->set('enable_uglification', $form_state->getValue('enable_uglification'));
    $config->save();
    // Clear Cache to trigger hook_library_info_alter with new priorities.
    $this->libraryDiscovery->clearCachedDefinitions();
    $this->routerBuilder->rebuild();
    return parent::submitForm($form, $form_state);
  }

}
