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
            '#type' => 'details',
            '#title' => 'Settings for ' . $selected_attribute,
            '#prefix' => '<div id="options-fieldset-wrapper-' . $key . '-' . $selected_attribute . '">',
            '#suffix' => '</div>',
          ];
          // Attribute displays count.
          $existingCount = 0;
          if (!empty($attribute_options[$key]['attribute_details'][$selected_attribute])) {
            foreach ($attribute_options[$key]['attribute_details'][$selected_attribute] as $attribute_data_index => $attribute_data) {
              $existingCount++;
              $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute][$attribute_data_index] = [
                '#type' => 'details',
                '#title' => t('Display') . ' ' . $existingCount,
              ];
              $attribute_data['index'] = $attribute_data_index;
              $attribute_data['key'] = $key;
              $attribute_data['selected_attribute'] = $selected_attribute;
              $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute][$attribute_data_index] += $this->attributeOptionsFields($attribute_data);
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
              $attribute_data = [
                'index' => $i,
                'key' => $key,
                'selected_attribute' => $selected_attribute,
              ];
              $form['alshaya_options_page_settings'][$key]['alshaya_options_page_attributes'][$selected_attribute][$i] += $this->attributeOptionsFields($attribute_data);
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
   * Function to increase the fieldset temp count &
   * rebuild the form on add more.
   *
   * @param array $form
   *   form element array.
   *
   * @param array $form_state
   *   form data.
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $options_field = $form_state->get('temp_count_' . $triggering_element['#parents'][1] . '_' . $triggering_element['#parents'][3]) ?? 0;
    $form_state->set('temp_count_' . $triggering_element['#parents'][1] . '_' . $triggering_element['#parents'][3], ($options_field + 1));
    $form_state->setRebuild();
  }

  /**
   * Function to decrease the fieldset temp count &
   * rebuild the form on remove.
   *
   * @param array $form
   *   form element array.
   *
   * @param array $form_state
   *   form data.
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
   * Callback function to refresh the fieldset after add/remove.
   *
   * @param array $form
   *   form element array.
   *
   * @param array $form_state
   *   form data.
   *
   * @return array
   *   form element
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
   *
   * Formset of displaying options settings for attributes.
   */
  public function attributeOptionsFields($attribute_data) {
    $form['title'] = [
      '#type' => 'textfield',
      '#default_value' => $attribute_data['title'] ?? '',
      '#title' => $this->t('Title for %attribute', ['%attribute' => $attribute_data['selected_attribute']]),
    ];
    $form['description'] = [
      '#type' => 'textfield',
      '#default_value' => $attribute_data['description'] ?? '',
      '#title' => $this->t('Description for %attribute', ['%attribute' => $attribute_data['selected_attribute']]),
    ];
    $form['show-search'] = [
      '#type' => 'checkbox',
      '#default_value' => $attribute_data['show-search'] ?? '',
      '#title' => $this->t('Enable search for %attribute', ['%attribute' => $attribute_data['selected_attribute']]),
    ];
    $search_name = 'alshaya_options_page_settings[' . $attribute_data['key'] . '][alshaya_options_page_attributes][' . $attribute_data['selected_attribute'] . '][' . $attribute_data['index'] . '][show-search]';
    $form['search-placeholder'] = [
      '#type' => 'textfield',
      '#default_value' => $attribute_data['search-placeholder'] ?? '',
      '#title' => $this->t('Search placeholder for %attribute', ['%attribute' => $attribute_data['selected_attribute']]),
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

    $module_path = drupal_get_path('module', 'alshaya_options_list');
    $form['show-images'] = [
      '#type' => 'checkbox',
      '#default_value' => $attribute_data['show-images'] ?? '',
      '#title' => $this->t('Show images for %attribute', ['%attribute' => $attribute_data['selected_attribute']]),
      '#description' => $this->t('Check to display attribute with images. Images need to be added to the attribute taxonomy term. You cannot group the terms alphabetically if this option is selected.'),
      '#suffix' => '<img src="' . base_path() . $module_path . '/images/shop-by-brand-image-small.png" alt="Shop by brand image">',
    ];
    $form['group'] = [
      '#type' => 'checkbox',
      '#default_value' => $attribute_data['group'] ?? '',
      '#title' => $this->t('Group %attribute alphabetically.', ['%attribute' => $attribute_data['selected_attribute']]),
      '#description' => $this->t('Check to group the attributes by alphabet. keep unchecked, if the previous option "show images" is checked.'),
      '#suffix' => '<img src="' . base_path() . $module_path . '/images/shop-by-brand-group-search-small.png" alt="Shop by brand group search" style="height:100%;max-height:100px">',
    ];
    $form['mobile_title_toggle'] = [
      '#type' => 'checkbox',
      '#default_value' => $attribute_data['mobile_title_toggle'] ?? '',
      '#title' => $this->t('Show button on mobile.'),
      '#attributes' => ['class' => ['mobile-title-toggle']],
      '#description' => $this->t('If checked, a button will be visible in mobile display. The attribute options will be displayed on clicking this button.'),
    ];

    $name = 'alshaya_options_page_settings[' . $attribute_data['key'] . '][alshaya_options_page_attributes][' . $attribute_data['selected_attribute'] . '][' . $attribute_data['index'] . '][mobile_title_toggle]';
    $form['mobile_title'] = [
      '#type' => 'textfield',
      '#default_value' => $attribute_data['mobile_title'] ?? '',
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
    if ($attribute_data['index'] != 0) {
      $form['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#name' => $attribute_data['key'] . '-' . $attribute_data['selected_attribute'] . '-' . $attribute_data['index'],
        '#submit' => ['::removeCallback'],
        '#ajax' => [
          'callback' => '::addRemoveCallback',
          'wrapper' => 'options-fieldset-wrapper-' . $attribute_data['key'] . '-' . $attribute_data['selected_attribute'],
        ],
      ];
    }
    return $form;
  }

}
