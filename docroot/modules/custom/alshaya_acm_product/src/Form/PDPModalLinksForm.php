<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class PDP Modal Links Form.
 */
class PDPModalLinksForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   A list of entity definition objects.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pdp_modal_links_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.pdp_modal_links'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.pdp_modal_links');
    $config->set('size_guide_enabled', $form_state->getValue('size_guide_enabled'));
    $config->set('show_size_guide_on_pdp_page', $form_state->getValue('show_size_guide_on_pdp_page'));
    $config->set('size_guide_modal_content_node', $form_state->getValue('size_guide_modal_content_node'));
    $config->set('delivery_content_node', $form_state->getValue('delivery_content_node'));

    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_acm_product.pdp_modal_links');
    $target_bundles = ['static_html', 'advanced_page'];
    $node = NULL;
    if ($config->get('size_guide_modal_content_node')) {
      $node_storage = $this->entityManager->getStorage('node');
      $node = $node_storage->load($config->get('size_guide_modal_content_node'));
    }
    $form['size_guide_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Size Guide'),
      '#required' => FALSE,
      '#default_value' => $config->get('size_guide_enabled'),
    ];

    $form['show_size_guide_on_pdp_page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show Size Guide directly on PDP Page'),
      '#required' => FALSE,
      '#default_value' => $config->get('show_size_guide_on_pdp_page'),
    ];

    $form['size_guide_modal_content_node'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Size guide modal content node.'),
      '#target_type' => 'node',
      '#selection_setttings' => ['target_bundles' => $target_bundles],
      '#default_value' => $node,
      '#size' => '60',
      '#maxlength' => '60',
      '#description' => $this->t('Please select the node which will be rendered as size guide modal.'),
      '#states' => [
        'visible' => [
          'input[name="size_guide_enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $node = NULL;
    if ($config->get('delivery_content_node')) {
      $node_storage = $this->entityManager->getStorage('node');
      $node = $node_storage->load($config->get('delivery_content_node'));
    }

    $form['delivery_content_node'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Delivery content node.'),
      '#target_type' => 'node',
      '#selection_setttings' => ['target_bundles' => $target_bundles],
      '#default_value' => $node,
      '#size' => '60',
      '#maxlength' => '60',
      '#description' => $this->t('Please select the node which will be rendered as delivery content. If empty, then link will not render.'),
    ];

    return $form;
  }

}
