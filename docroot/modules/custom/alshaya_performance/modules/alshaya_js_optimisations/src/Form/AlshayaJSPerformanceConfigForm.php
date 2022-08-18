<?php

namespace Drupal\alshaya_js_optimisations\Form;

use Drupal\alshaya_js_optimisations\AlshayaJsOptimisationHelper;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Asset\LibraryDiscovery;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Alshaya JS Performance Config Form.
 */
class AlshayaJSPerformanceConfigForm extends ConfigFormBase {

  /**
   * The JS optimisation service.
   *
   * @var \Drupal\alshaya_js_optimisations\AlshayaJsOptimisationHelper
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
   * Constructs a JS optimisation service object.
   *
   * @param \Drupal\alshaya_js_optimisations\AlshayaJsOptimisationHelper $js_optimsation
   *   Optimisations config, methods and variables.
   * @param \Drupal\Core\Asset\LibraryDiscovery $library_discovery
   *   Library Discovery methods and variables.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   Route builder methods and variables.
   */
  public function __construct(AlshayaJsOptimisationHelper $js_optimsation, LibraryDiscovery $library_discovery, RouteBuilderInterface $router_builder) {
    $this->jsOptimisation = $js_optimsation;
    $this->libraryDiscovery = $library_discovery;
    $this->routerBuilder = $router_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_js_optimisations.helper'),
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
    $critical_js = $config->get('critical_js');
    $js_optimisations_config = AlshayaJsOptimisationHelper::getJsOptimisationTextConfig();
    $critical_js_text_config = $js_optimisations_config['critical_js'];
    $js_categories_config = AlshayaJsOptimisationHelper::$jsCategories;
    $js_categories = array_keys($js_categories_config);
    $yaml_placeholder = $this->t('Enter YAML formatted text');

    $form['enable_uglification'] = [
      '#type' => 'checkbox',
      '#title' => $js_optimisations_config['uglification']['label'],
      '#description' => $js_optimisations_config['uglification']['description'],
      '#default_value' => $config->get('enable_uglification'),
    ];

    $form['critical_js'] = [
      '#type' => 'fieldset',
      '#title' => $critical_js_text_config['label'],
      '#description' => $critical_js_text_config['description'],
    ];

    $form['critical_js']['status'] = [
      '#type' => 'checkbox',
      '#title' => $critical_js_text_config['status_label'],
      '#default_value' => $critical_js['status'] ?? FALSE,
    ];

    foreach ($js_categories as $category) {
      $form['critical_js'][$category] = [
        '#type' => 'textarea',
        '#title' => $critical_js_text_config[$category]['label'],
        '#placeholder' => $yaml_placeholder,
        '#description' => $critical_js_text_config[$category]['description'],
        '#rows' => 8,
        '#states' => [
          'disabled' => [
            ':input[name="status"]' => ['checked' => FALSE],
          ],
        ],
        '#default_value' => $critical_js[$category] ?? '',
      ];
    }

    $form['warning'] = [
      '#type' => 'markup',
      '#markup' => '<div class="txt-warning">' . $js_optimisations_config['form_warning'] . '</div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate YAML syntax.
    $fields = AlshayaJsOptimisationHelper::$jsCategories;
    foreach ($fields as $field => $attributes) {
      try {
        Yaml::decode($form_state->getValue($field));
      }
      catch (\Exception) {
        $form_state->setErrorByName($field, $this->t('Invalid YAML data on %field', ['%field' => $attributes['label']]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_js_optimisations.settings');
    $critical_js = [
      'status' => $form_state->getValue('status'),
      'ie_only' => $form_state->getValue('ie_only'),
      'site_libraries' => $form_state->getValue('site_libraries'),
      'handlebars' => $form_state->getValue('handlebars'),
      'rcs_listeners' => $form_state->getValue('rcs_listeners'),
      'critical' => $form_state->getValue('critical'),
      'sitewide_1' => $form_state->getValue('sitewide_1'),
      'sitewide_2' => $form_state->getValue('sitewide_2'),
    ];

    // Store processed library info to Critical JS settings.
    $processed_libraries = '';
    if ($critical_js['status']) {
      $processed_libraries = $this->jsOptimisation->generateProcessedLibraries($critical_js);
    }
    $critical_js['processed_libraries'] = $processed_libraries;

    $config->set('critical_js', $critical_js);
    $config->set('enable_uglification', $form_state->getValue('enable_uglification'));
    $config->save();

    // Clear Cache to trigger hook_library_info_alter with updated priorities.
    $this->libraryDiscovery->clearCachedDefinitions();
    $this->routerBuilder->rebuild();

    return parent::submitForm($form, $form_state);
  }

}
