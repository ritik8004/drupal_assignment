<?php

namespace Drupal\alshaya_main_menu\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;

/**
 * Class Alshaya Main Menu Config Form.
 */
class AlshayaMainMenuConfigForm extends ConfigFormBase {

  /**
   * Default main menu layout.
   */
  public const MAIN_MENU_DEFAULT_LAYOUT = 'default';

  /**
   * Inline main menu layout.
   */
  public const MAIN_MENU_INLINE_LAYOUT = 'menu_inline_display';

  /**
   * Dynamic width main menu layout.
   */
  public const MAIN_MENU_DYNAMIC_LAYOUT = 'menu_dynamic_display';

  /**
   * Default layout for mobile main menu navigation.
   */
  public const MOBILE_MENU_DEFAULT = 'default';

  /**
   * Visual mobile main menu layout.
   */
  public const MOBILE_MENU_VISUAL = 'visual_mobile_menu';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_main_menu';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_main_menu.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('alshaya_main_menu.settings');

    $form['mobile_main_menu_max_depth'] = [
      '#type' => 'select',
      '#title' => $this->t('Main menu maximum depth for mobile'),
      '#description' => $this->t('Set the maixmum depth to display menu levels for mobile. 0 for not to restrict.'),
      '#options' => range(0, 6),
      '#default_value' => $config->get('mobile_main_menu_max_depth'),
    ];

    $form['desktop_main_menu_layout'] = [
      '#type' => 'select',
      '#options' => [
        self::MAIN_MENU_DEFAULT_LAYOUT => $this->t('Default menu display'),
        self::MAIN_MENU_INLINE_LAYOUT => $this->t('Inline menu display'),
        self::MAIN_MENU_DYNAMIC_LAYOUT => $this->t('Dynamic Width Mega Menu'),
      ],
      '#default_value' => $config->get('desktop_main_menu_layout'),
      '#title' => $this->t('Main menu display on desktop'),
      '#description' => $this->t('Select inline menu display option to display the l3 option inline to l2 otherwise it will follow the core.'),
    ];

    $form['mobile_main_menu_layout'] = [
      '#type' => 'select',
      '#options' => [
        self::MOBILE_MENU_DEFAULT => $this->t('Default mobile menu display'),
        self::MOBILE_MENU_VISUAL => $this->t('Visual layout for mobile menu display'),
      ],
      '#default_value' => $config->get('mobile_main_menu_layout'),
      '#title' => $this->t('Main menu display on mobile view'),
      '#description' => $this->t('This settings is to select different layout for main menu on mobile view. Note: The visual menu requires certain parameter from MDC backend.'),
    ];

    $form['max_nb_col'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maxinum number of columns to show'),
      '#description' => $this->t('Set the maximum number of columns to show.'),
      '#default_value' => (!empty($config->get('max_nb_col'))) ? $config->get('max_nb_col') : 6,
    ];

    $form['ideal_max_col_length'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ideal length of the column'),
      '#description' => $this->t('Set ideal length of a column'),
      '#default_value' => (!empty($config->get('ideal_max_col_length'))) ? $config->get('ideal_max_col_length') : 10,
    ];

    $form['show_l2_in_separate_column'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show each L2 and their child menus in a separate column'),
      '#default_value' => $config->get('show_l2_in_separate_column'),
    ];

    $form['show_highlight'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show / Hide menu highlights CTA'),
      '#default_value' => $config->get('show_highlight'),
    ];

    $form['show_menu_full_width'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show menu overlay in full width'),
      '#default_value' => $config->get('show_menu_full_width'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_main_menu.settings');
    $config->set('mobile_main_menu_max_depth', $form_state->getValue('mobile_main_menu_max_depth'));
    $config->set('desktop_main_menu_layout', $form_state->getValue('desktop_main_menu_layout'));
    $config->set('max_nb_col', $form_state->getValue('max_nb_col'));
    $config->set('ideal_max_col_length', $form_state->getValue('ideal_max_col_length'));
    $config->set('show_l2_in_separate_column', $form_state->getValue('show_l2_in_separate_column'));
    $config->set('show_highlight', $form_state->getValue('show_highlight'));
    $config->set('show_menu_full_width', $form_state->getValue('show_menu_full_width'));
    $config->save();
    Cache::invalidateTags([ProductCategoryTree::CACHE_TAG]);

    return parent::submitForm($form, $form_state);
  }

}
