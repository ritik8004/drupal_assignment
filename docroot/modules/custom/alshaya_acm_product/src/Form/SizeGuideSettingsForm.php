<?php

namespace Drupal\alshaya_acm_product\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\entity\Node;

/**
 * Class SizeGuideSettingsForm.
 */
class SizeGuideSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'size_guide_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_acm_product.size_guide'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_acm_product.size_guide');
    $config->set('size_guide_enabled', $form_state->getValue('size_guide_enabled'));
    $config->set('size_guide_modal_content_node', $form_state->getValue('size_guide_modal_content_node'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_acm_product.size_guide');
    $target_bundles = ['static_html', 'advanced_page'];
    $node = NULL;
    if ($config->get('size_guide_modal_content_node')) {
      $node = Node::load($config->get('size_guide_modal_content_node'));
    }
    $form['size_guide_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Size Guide'),
      '#required' => TRUE,
      '#default_value' => $config->get('size_guide_enabled'),
    ];

    $form['size_guide_modal_content_node'] = [
      '#type' => 'entity_autocomplete',
      '#title' => t('Size guide modal content node.'),
      '#target_type' => 'node',
      '#selection_setttings' => ['target_bundles' => $target_bundles],
      '#default_value' => $node,
      '#size' => '60',
      '#maxlength' => '60',
      '#description' => t('Please select the node which will be rendered as size guide modal.'),
      '#states' => [
        'visible' => [
          'input[name="size_guide_enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    return $form;
  }

}
