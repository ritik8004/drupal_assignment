<?php

namespace Drupal\alshaya_feed\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

/**
 * Class Alshaya feed settings.
 *
 * @package Drupal\alshaya_feed\Form
 */
class AlshayaFeedSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_feed_settings_form';
  }

  /**
   * Get Config name.
   *
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return ['alshaya_feed.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_feed.settings');
    $categories = $config->get('categories_to_exclude') ?? [];
    $form['categories_to_exclude'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Categories to exclude'),
      '#description' => $this->t('Separate categories with a newline.'),
      '#default_value' => implode(PHP_EOL, $categories),
    ];
    $fields = $config->get('brand_fields') ?? [];
    $form['brand_fields'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Brand Specific Fields'),
      '#description' => $this->t('Add brand specifc fields which are required in the product feed.
      Separate fields with a newline.'),
      '#default_value' => implode(PHP_EOL, $fields),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_feed.settings');
    $categories = [];
    if (!empty($form_state->getValue('categories_to_exclude'))) {
      $categories = preg_split('/\n|\r\n?/', $form_state->getValue('categories_to_exclude'));
    }
    $config->set('categories_to_exclude', $categories);
    $brand_fields = [];
    if (!empty($form_state->getValue('brand_fields'))) {
      $brand_fields = preg_split('/\n|\r\n?/', $form_state->getValue('brand_fields'));
    }
    $config->set('brand_fields', $brand_fields);
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
