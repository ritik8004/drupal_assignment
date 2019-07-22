<?php

namespace Drupal\alshaya_options_list\Form;

use Drupal\alshaya_options_list\AlshayaOptionsListHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteBuilderInterface;

/**
 * Class AlshayaOptionsListForm.
 */
class AlshayaOptionsListForm extends ConfigFormBase {

  /**
   * Alshaya Options List Service.
   *
   * @var Drupal\alshaya_options_list\AlshayaOptionsListHelper
   */
  protected $alshayaOptionsService;

  /**
   * The router builder.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * AlshayaOptionsListForm constructor.
   *
   * @param Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshaya_options_service
   *   Alshaya options service.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router builder service.
   */
  public function __construct(AlshayaOptionsListHelper $alshaya_options_service,
                              RouteBuilderInterface $router_builder) {
    $this->alshayaOptionsService = $alshaya_options_service;
    $this->routerBuilder = $router_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_options_list.alshaya_options_service'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_options_list_form';
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
    $attribute_options = (array) $config->get('alshaya_options_pages');

    $form['alshaya_shop_by_pages_enable'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('alshaya_shop_by_pages_enable'),
      '#title' => $this->t('Enable options page on site.'),
    ];

    $form['#tree'] = TRUE;
    $form['alshaya_options_page'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Options Page Settings'),
      '#prefix' => '<div id="options-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#states' => [
        'visible' => [
          ':input[name="alshaya_shop_by_pages_enable"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $temp_count = count($attribute_options) == 0 ? $form_state->get('temp_count') + 1 : $form_state->get('temp_count');

    if (!empty($attribute_options)) {
      foreach ($attribute_options as $key => $attribute_option) {
        $form['alshaya_options_page'][$key] = [
          '#type' => 'fieldset',
          '#Collapsible' => TRUE,
        ];
        $form['alshaya_options_page'][$key]['alshaya_options_page_url'] = [
          '#type' => 'textfield',
          '#default_value' => $attribute_option['url'] ?? '',
          '#title' => $this->t('Page url on which options should be displayed.'),
        ];

        $form['alshaya_options_page'][$key]['alshaya_options_page_title'] = [
          '#type' => 'textfield',
          '#default_value' => $attribute_option['title'] ?? '',
          '#title' => $this->t('Option page title'),
        ];

        $form['alshaya_options_page'][$key]['alshaya_options_attributes'] = [
          '#type' => 'checkboxes',
          '#options' => $this->alshayaOptionsService->getAttributeCodeOptions(),
          '#default_value' => !empty($attribute_option['attributes']) ? $attribute_option['attributes'] : [],
          '#title' => $this->t('The attribute to list on the options page.'),
        ];

        $form['alshaya_options_page'][$key]['alshaya_options_delete'] = [
          '#type' => 'submit',
          '#value' => $this->t('Delete'),
          '#submit' => ['::deleteThis'],
          '#ajax' => [
            'callback' => '::addRemoveCallback',
            'wrapper' => 'options-fieldset-wrapper',
          ],
        ];
      }
    }

    if ($temp_count > 0) {
      for ($i = 0; $i < $temp_count; $i++) {
        $form['alshaya_options_page'][$i] = [
          '#type' => 'fieldset',
          '#Collapsible' => TRUE,
        ];
        $form['alshaya_options_page'][$i]['alshaya_options_page_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Page url on which options should be displayed.'),
        ];

        $form['alshaya_options_page'][$i]['alshaya_options_page_title'] = [
          '#type' => 'textfield',
          '#title' => $this->t('The title for this options page.'),
        ];

        $form['alshaya_options_page'][$i]['alshaya_options_attributes'] = [
          '#type' => 'checkboxes',
          '#options' => $this->alshayaOptionsService->getAttributeCodeOptions(),
          '#title' => $this->t('The attribute to list on the options page.'),
        ];
      }
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['alshaya_options_page']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add More'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addRemoveCallback',
        'wrapper' => 'options-fieldset-wrapper',
      ],
    ];

    $form_state->setCached(FALSE);
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $options_field = $form_state->get('temp_count') ?? 0;
    $form_state->set('temp_count', ($options_field + 1));
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteThis(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_options_list.settings');
    $triggering_element = $form_state->getTriggeringElement();
    $key = $triggering_element['#parents'][1];
    $config->clear('alshaya_options_pages.' . $key);
    $config->save();
    // Rebuild routes so that routes get deleted.
    $this->routerBuilder->rebuild();
    // Invalidate page cache.
    Cache::invalidateTags(['alshaya-options-page']);

    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function addRemoveCallback(array &$form, FormStateInterface $form_state) {
    return $form['alshaya_options_page'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_options_list.settings');
    $config->set('alshaya_shop_by_pages_enable', $form_state->getValue('alshaya_shop_by_pages_enable'));
    $values = $form_state->getValue('alshaya_options_page');
    foreach ($values as $value) {
      $url = ltrim($value['alshaya_options_page_url'] ?? '', '/');
      $attributes = $value['alshaya_options_attributes'] ?? '';
      if (empty($url) || empty($attributes)) {
        continue;
      }
      $config_key = 'alshaya_options_pages.' . str_replace('/', '-', $url);
      $config->set($config_key . '.title', $value['alshaya_options_page_title'] ?? '');
      $config->set($config_key . '.url', $url);
      $config->set($config_key . '.attributes', $attributes);
    }

    $config->save();

    // Rebuild routes so that new routes get added.
    $this->routerBuilder->rebuild();

    // Invalidate page cache.
    Cache::invalidateTags(['alshaya-options-page']);

    return parent::submitForm($form, $form_state);
  }

}
