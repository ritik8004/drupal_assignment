<?php

namespace Drupal\alshaya_pdp_layouts\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\alshaya_pdp_layouts\PdpLayoutManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlshayaPdpLayoutForm.
 */
class AlshayaPdpLayoutForm extends ConfigFormBase {

  /**
   * The PDP Layout Manager.
   *
   * @var \Drupal\alshaya_pdp_layouts\PdpLayoutManager
   */
  protected $pluginManager;

  /**
   * AlshayaPdpLayoutForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\alshaya_pdp_layouts\PdpLayoutManager $pdp_layout_manager
   *   The PDP Layout Manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, PdpLayoutManager $pdp_layout_manager) {
    parent::__construct($config_factory);
    $this->pdpLayoutManager = $pdp_layout_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.pdp_layouts')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_pdp_layouts';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_pdp_layouts.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_pdp_layouts.settings');

    $alshaya_pdp_layouts = $this->pdpLayoutManager;
    $layouts = $alshaya_pdp_layouts->getDefinitions();
    $options = [];
    foreach ($layouts as $key => $value) {
      $options[$key] = $value['label']->__toString();
    }

    $form['pdp_layout'] = [
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $config->get('pdp_layout'),
      '#title' => $this->t('Select PDP Layout'),
      '#description' => $this->t('Select a layout for the PDP page.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_pdp_layouts.settings');
    $config->set('pdp_layout', $form_state->getValue('pdp_layout'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
