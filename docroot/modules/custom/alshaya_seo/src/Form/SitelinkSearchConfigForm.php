<?php

namespace Drupal\alshaya_seo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SitelinkSearchConfigForm.
 */
class SitelinkSearchConfigForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a EntityManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_seo_sitelink_search_config';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_seo.sitelink_search'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_seo.sitelink_search');
    $form['enable_sitelink_searchbox'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Sitelink Searchbox'),
      '#required' => FALSE,
      '#default_value' => $config->get('enable_sitelink_searchbox'),
      '#disabled' => ($this->moduleHandler->moduleExists('search_api')) &&  (Settings::get('alshaya_seo.google_sitelink_searchbox_enable')) ? FALSE : TRUE,
      '#description' => $this->t('This setting will work only with search enabled production sites.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_seo.sitelink_search');
    $config->set('enable_sitelink_searchbox', $form_state->getValue('enable_sitelink_searchbox'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
