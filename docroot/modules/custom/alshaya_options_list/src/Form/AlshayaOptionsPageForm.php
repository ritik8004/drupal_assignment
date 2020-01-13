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
    return ['alshaya_options_list.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_options_list.settings');
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
          '#default_value' => $attribute_options[$key]['title'] ?? '',
          '#title' => $this->t('The title for this options page.'),
        ];

        $form['alshaya_options_page_settings'][$key]['alshaya_options_page_description'] = [
          '#type' => 'textfield',
          '#default_value' => $attribute_options[$key]['description'] ?? '',
          '#title' => $this->t('The description for this options page.'),
        ];

        $form['alshaya_options_page_settings'][$key]['alshaya_options_page_menu_title'] = [
          '#type' => 'textfield',
          '#default_value' => $attribute_options[$key]['menu-title'] ?? '',
          '#title' => $this->t('The title menu link in the header for this options page.'),
        ];

        foreach (array_filter($attribute_option['attributes']) as $selected_attribute) {
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute] = [
            '#type' => 'fieldset',
            '#Collapsible' => TRUE,
            '#title' => 'Settings for ' . $selected_attribute,
            '#prefix' => '<div id="options-fieldset-wrapper-' . $key . '-' . $selected_attribute . '">',
            '#suffix' => '</div>',
          ];
          $existingCount = 0;
          if (!empty($attribute_options[$key]['attribute_details'][$selected_attribute])) {
            foreach ($attribute_options[$key]['attribute_details'][$selected_attribute] as $no => $selected_attribue_val) {
              $existingCount++;
              $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute][$no] = [
                '#type' => 'fieldset',
                '#Collapsible' => TRUE,
              ];
              $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute][$no] += $this->attributeOptionsFields($key, $selected_attribute, $no, $selected_attribue_val);
            }
            if ($form_state->get('temp_count_' . $key . '_' . $selected_attribute) === NULL) {
              $form_state->set('temp_count_' . $key . '_' . $selected_attribute, $existingCount);
            }
          }
          else {
            if ($form_state->get('temp_count_' . $key . '_' . $selected_attribute) === NULL) {
              $form_state->set('temp_count_' . $key . '_' . $selected_attribute, 1);
            }
          }

          $temp_count = $form_state->get('temp_count_' . $key . '_' . $selected_attribute);
          if ($temp_count > 0) {
            for ($i = $existingCount; $i < $temp_count; $i++) {
              $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute][$i] = [
                '#type' => 'fieldset',
                '#Collapsible' => TRUE,
              ];
              $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute][$i] += $this->attributeOptionsFields($key, $selected_attribute, $i, []);
            }
          }
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute]['actions'] = [
            '#type' => 'actions',
          ];
          $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute]['actions'][$key]['add_name'] = [
            '#type' => 'submit',
            '#name' => $key . '-' . $selected_attribute,
            '#value' => $this->t('Add More'),
            '#submit' => ['::addOne'],
            '#ajax' => [
              'callback' => '::addRemoveCallback',
              'wrapper' => 'options-fieldset-wrapper-' . $key . '-' . $selected_attribute,
            ],
          ];
        }
      }
    }
    else {
      $form['empty_page'] = [
        '#markup' => $this->t('No page available. Create a page before configuring page settings.'),
        '#prefix' => '<strong>',
        '#suffix' => '</strong>',
      ];
    }
    $form_state->setCached(FALSE);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $options_field = $form_state->get('temp_count_' . $triggering_element['#parents'][1] . '_' . $triggering_element['#parents'][3]) ?? 0;
    $form_state->set('temp_count_' . $triggering_element['#parents'][1] . '_' . $triggering_element['#parents'][3], ($options_field + 1));
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function removeCallback(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_options_list.settings');
    $triggering_element = $form_state->getTriggeringElement();
    $key = $triggering_element['#parents'][1];

    $key = $triggering_element['#parents'][1] . '.attribute_details.' . $triggering_element['#parents'][3] . '.' . $triggering_element['#parents'][4];
    // Delete config.
    $config->clear('alshaya_options_pages.' . $key);
    $config->save();

    $options_field = $form_state->get('temp_count_' . $triggering_element['#parents'][1] . '_' . $triggering_element['#parents'][3]);
    if ($options_field > 1) {
      $form_state->set('temp_count_' . $triggering_element['#parents'][1] . '_' . $triggering_element['#parents'][3], $options_field - 1);
    }

    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function addRemoveCallback(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    return $form['alshaya_options_page_settings'][$triggering_element['#parents'][1]]['alshaya_options_page_attributes'][$triggering_element['#parents'][3]];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_options_list.settings');
    $options_pages = $config->get('alshaya_options_pages');
    $values = $form_state->getValue('alshaya_options_page_settings');
    foreach ($values as $key => $value) {
      $options_pages[$key]['title'] = $value['alshaya_options_page_title'] ?? '';
      $options_pages[$key]['description'] = $value['alshaya_options_page_description'] ?? '';
      $options_pages[$key]['menu-title'] = $value['alshaya_options_page_menu_title'] ?? '';
      foreach ($value['alshaya_options_page_attributes'] as $attribute_title => $attributes) {
        unset($attributes['actions']);
        $options_pages[$key]['attribute_details'][$attribute_title] = $attributes;
      }

      $config->set('alshaya_options_pages', $options_pages);
    }
    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * Attribute options form fields.
   */
  public function attributeOptionsFields($key, $selected_attribute, $no, $selected_attribue_val) {
    $form['title'] = [
      '#type' => 'textfield',
      '#default_value' => $selected_attribue_val['title'] ?? '',
      '#title' => $this->t('Title for %attribute', ['%attribute' => $selected_attribute]),
    ];
    $form['description'] = [
      '#type' => 'textfield',
      '#default_value' => $selected_attribue_val['description'] ?? '',
      '#title' => $this->t('Description for %attribute', ['%attribute' => $selected_attribute]),
    ];
    $form['show-search'] = [
      '#type' => 'checkbox',
      '#default_value' => $selected_attribue_val['show-search'] ?? '',
      '#title' => $this->t('Enable search for %attribute', ['%attribute' => $selected_attribute]),
    ];
    $search_name = 'alshaya_options_page_settings[' . $key . '][alshaya_options_page_attributes][' . $selected_attribute . '][' . $no . '][show-search]';
    $form['search-placeholder'] = [
      '#type' => 'textfield',
      '#default_value' => $selected_attribue_val['search-placeholder'] ?? '',
      '#title' => $this->t('Search placeholder for %attribute', ['%attribute' => $selected_attribute]),
      '#prefix' => '<div id="options-search-placeholder">',
      '#suffix' => '</div>',
      '#states' => [
        'visible' => [
          ':input[name="' . $search_name . '"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="' . $search_name . '"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['show-images'] = [
      '#type' => 'checkbox',
      '#default_value' => $selected_attribue_val['show-images'] ?? '',
      '#title' => $this->t('Show images for %attribute', ['%attribute' => $selected_attribute]),
      '#description' => $this->t('Check to display attribute with images. Images need to be added to the attribute taxonomy term. You cannot group the terms alphabetically if this option is selected.'),
    ];
    $form['group'] = [
      '#type' => 'checkbox',
      '#default_value' => $selected_attribue_val['group'] ?? '',
      '#title' => $this->t('Group %attribute alphabetically.', ['%attribute' => $selected_attribute]),
      '#description' => $this->t('Check to group the attributes by alphabet. keep unchecked, if the previous option "show images" is checked.'),
    ];
    $form['mobile_title_toggle'] = [
      '#type' => 'checkbox',
      '#default_value' => $selected_attribue_val['mobile_title_toggle'] ?? '',
      '#title' => $this->t('Show button on mobile.'),
      '#attributes' => ['class' => ['mobile-title-toggle']],
      '#description' => $this->t('If checked, a button will be visible in mobile display. The attribute options will be displayed on clicking this button.'),
    ];

    $name = 'alshaya_options_page_settings[' . $key . '][alshaya_options_page_attributes][' . $selected_attribute . '][' . $no . '][mobile_title_toggle]';
    $form['mobile_title'] = [
      '#type' => 'textfield',
      '#default_value' => $selected_attribue_val['mobile_title'] ?? '',
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
    // Don't add remove for first option.
    if ($no != 0) {
      $form['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#name' => $key . '-' . $selected_attribute . '-' . $no,
        '#submit' => ['::removeCallback'],
        '#ajax' => [
          'callback' => '::addRemoveCallback',
          'wrapper' => 'options-fieldset-wrapper-' . $key . '-' . $selected_attribute,
        ],
      ];
    }
    return $form;
  }

}
