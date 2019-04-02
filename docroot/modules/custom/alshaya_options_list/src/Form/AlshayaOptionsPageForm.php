<?php

namespace Drupal\alshaya_options_list\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AlshayaOptionsPageForm.
 */
class AlshayaOptionsPageForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_options_page_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_options_list.admin_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_options_list.admin_settings');
    $attribute_options = $config->get('alshaya_options_pages');

    if (!empty($attribute_options)) {
      $form['#tree'] = TRUE;
      $form['alshaya_options_page_settings'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Options Page Settings'),
        '#prefix' => '<div id="options-page-fieldset-wrapper">',
        '#suffix' => '</div>',
      ];

      foreach ($attribute_options as $key => $attribute_option) {
        $form['alshaya_options_page_settings'][$key] = [
          '#type' => 'fieldset',
          '#Collapsible' => TRUE,
          '#title' => 'Settings for ' . $attribute_option['url'] . ' page.',
        ];

        $form['alshaya_options_page_settings'][$key]['alshaya_options_page_title'] = [
          '#type' => 'textfield',
          '#default_value' => $attribute_options[$key]['title'],
          '#title' => $this->t('The title for this options page.'),
        ];

        $form['alshaya_options_page_settings'][$key]['alshaya_options_page_description'] = [
          '#type' => 'textfield',
          '#default_value' => $attribute_options[$key]['description'],
          '#title' => $this->t('The description for this options page.'),
        ];
        foreach (array_filter($attribute_option['attributes']) as $selected_attribute) {
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute] = [
            '#type' => 'fieldset',
            '#Collapsible' => TRUE,
            '#title' => 'Settings for ' . $selected_attribute,
          ];
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute]['show-search'] = [
            '#type' => 'checkbox',
            '#default_value' => $attribute_options[$key]['attribute_details'][$selected_attribute]['show-search'],
            '#title' => $this->t('Enable search for %attribute', ['%attribute' => $selected_attribute]),
          ];
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute]['show-images'] = [
            '#type' => 'checkbox',
            '#default_value' => $attribute_options[$key]['attribute_details'][$selected_attribute]['show-images'],
            '#title' => $this->t('Show images for %attribute', ['%attribute' => $selected_attribute]),
          ];
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute]['group'] = [
            '#type' => 'checkbox',
            '#default_value' => $attribute_options[$key]['attribute_details'][$selected_attribute]['group'],
            '#title' => $this->t('Group %attribute alphabetically.', ['%attribute' => $selected_attribute]),
          ];
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute]['title'] = [
            '#type' => 'textfield',
            '#default_value' => $attribute_options[$key]['attribute_details'][$selected_attribute]['title'],
            '#title' => $this->t('Title for %attribute', ['%attribute' => $selected_attribute]),
          ];
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute]['mobile_title_toggle'] = [
            '#type' => 'checkbox',
            '#default_value' => $attribute_options[$key]['attribute_details'][$selected_attribute]['mobile_title_toggle'],
            '#title' => $this->t('Show button on mobile.'),
            '#attributes' => ['class' => ['mobile-title-toggle']],
          ];

          $name = 'alshaya_options_page_settings[' . $key . '][alshaya_options_page_attributes][' . $selected_attribute . '][mobile_title_toggle]';
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute]['mobile_title'] = [
            '#type' => 'textfield',
            '#default_value' => $attribute_options[$key]['attribute_details'][$selected_attribute]['mobile_title'],
            '#title' => $this->t('Mobile button title.'),
            '#prefix' => '<div id="options-mobile-title">',
            '#suffix' => '</div>',
            '#states' => [
              'visible' => [
                ':input[name="' . $name . '"]' => ['checked' => TRUE],
              ],
              'required' => [
                ':input[name="' . $name . '"]' => ['checked' => TRUE],
              ],
            ],
          ];
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute]['description'] = [
            '#type' => 'textfield',
            '#default_value' => $attribute_options[$key]['attribute_details'][$selected_attribute]['description'],
            '#title' => $this->t('Description for %attribute', ['%attribute' => $selected_attribute]),
          ];
        }

      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_options_list.admin_settings');
    $options_pages = $config->get('alshaya_options_pages');
    $values = $form_state->getValue('alshaya_options_page_settings');
    foreach ($values as $key => $value) {
      $options_pages[$key]['title'] = $value['alshaya_options_page_title'] ?? '';
      $options_pages[$key]['description'] = $value['alshaya_options_page_description'] ?? '';
      foreach ($value['alshaya_options_page_attributes'] as $attribute_title => $attributes) {
        $options_pages[$key]['attribute_details'][$attribute_title] = $attributes;
      }

      $config->set('alshaya_options_pages', $options_pages);
    }
    $config->save();

    return parent::submitForm($form, $form_state);
  }

}
