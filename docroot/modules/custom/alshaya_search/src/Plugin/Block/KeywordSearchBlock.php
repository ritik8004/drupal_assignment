<?php

namespace Drupal\alshaya_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'KeywordSearchBlock' block.
 *
 * @Block(
 *  id = "keyword_search_block",
 *  admin_label = @Translation("Keyword search block"),
 * )
 */
class KeywordSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * KeywordSearchBlock constructor.
   *
   * @param array $configuration
   *   Plugin Configuration.
   * @param string $plugin_id
   *   Plugin Id.
   * @param mixed $plugin_definition
   *   Plugin definition as parsed from annotation.
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   Form builder service.
   */
  public function __construct(array $configuration,
                                 $plugin_id,
                                 $plugin_definition,
                                 FormBuilder $formBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Form\EnforcedResponseException
   */
  public function build() {
    $build = [];
    $view = Views::getView('search');
    $view->setDisplay('page');
    $view->initHandlers();
    $view->setAjaxEnabled(FALSE);

    $form_state = new FormState();

    $form_state->setMethod('get');
    $form_state->set('rerender', NULL);
    $form_state->setStorage([
      'view' => $view,
      'display' => &$view->display_handler->display,
      'rerender' => TRUE,
    ]);

    $form = $this->formBuilder->buildForm('\Drupal\views\Form\ViewsExposedForm', $form_state);

    $form['#attached']['library'][] = 'alshaya_search/disable_keyword_ajax';
    $form['keywords']['#attributes']['autocapitalize'] = 'none';

    // Unset sort widget & its submit handler since we this block
    // should return keyword search field.
    unset($form['sort_bef_combine'], $form['#submit'][0]);

    $build['keyword_search_block'] = $form;
    $build['keyword_search_block']['#cache']['contexts'][] = 'url.query_args:keywords';
    return $build;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

}
