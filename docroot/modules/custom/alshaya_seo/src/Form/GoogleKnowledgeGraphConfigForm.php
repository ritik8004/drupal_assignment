<?php

namespace Drupal\alshaya_seo\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GoogleKnowledgeGraphConfigForm.
 */
class GoogleKnowledgeGraphConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_seo_google_knowledge_graph_config';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_seo.google_knowledge_graph'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_seo.google_knowledge_graph');

    $form['same_as'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Same As'),
      '#description' => $this->t('Enter one link per line if you have multiple links.'),
      '#default_value' => $config->get('same_as') ? $config->get('same_as') : '',
    ];

    $form['contact_telephone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact Telephone'),
      '#description' => $this->t('This will be used as is in JSON output.'),
      '#default_value' => $config->get('contact_telephone') ? $config->get('contact_telephone') : '',
    ];

    $form['contact_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact Type'),
      '#description' => $this->t('For instance: Customer Support'),
      '#default_value' => $config->get('contact_type') ? $config->get('contact_type') : '',
    ];

    $form['contact_areaserved'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact Area Served'),
      '#description' => $this->t('For instance: KW'),
      '#default_value' => $config->get('contact_areaserved') ? $config->get('contact_areaserved') : '',
    ];

    $form['contact_option'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact Option'),
      '#description' => $this->t('For instance: TollFree'),
      '#default_value' => $config->get('contact_option') ? $config->get('contact_option') : '',
    ];

    $form['contact_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact URL'),
      '#description' => $this->t('For instance: https://kw.hm.com/en/contact'),
      '#default_value' => $config->get('contact_url') ? $config->get('contact_url') : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_seo.google_knowledge_graph');
    $config->set('same_as', $form_state->getValue('same_as'));
    $config->set('contact_telephone', $form_state->getValue('contact_telephone'));
    $config->set('contact_type', $form_state->getValue('contact_type'));
    $config->set('contact_areaserved', $form_state->getValue('contact_areaserved'));
    $config->set('contact_option', $form_state->getValue('contact_option'));
    $config->set('contact_url', $form_state->getValue('contact_url'));
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
