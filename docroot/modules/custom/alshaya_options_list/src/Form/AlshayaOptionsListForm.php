<?php

namespace Drupal\alshaya_options_list\Form;

use Drupal\alshaya_options_list\AlshayaOptionsListHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class Alshaya Options List Form.
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
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * AlshayaOptionsListForm constructor.
   *
   * @param Drupal\alshaya_options_list\AlshayaOptionsListHelper $alshaya_options_service
   *   Alshaya options service.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router builder service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   */
  public function __construct(AlshayaOptionsListHelper $alshaya_options_service,
                              RouteBuilderInterface $router_builder,
                              EntityTypeManagerInterface $entityTypeManager) {
    $this->alshayaOptionsService = $alshaya_options_service;
    $this->routerBuilder = $router_builder;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_options_list.alshaya_options_service'),
      $container->get('router.builder'),
      $container->get('entity_type.manager')
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

    // Remove page url from breadcrumb block.
    $url = $config->get('alshaya_options_pages.' . $key . '.url');
    $block = $this->entityTypeManager->getStorage('block')->load('breadcrumbs');
    $visibility = $block->getVisibility();
    if (isset($visibility['request_path']['pages']) && stripos($visibility['request_path']['pages'], PHP_EOL . '/' . $url)) {
      $pages = explode(PHP_EOL, $visibility['request_path']['pages']);
      unset($pages[array_search('/' . $url, $pages)]);
      $visibility['request_path']['pages'] = implode(PHP_EOL, $pages);
      $block->setVisibilityConfig('request_path', $visibility['request_path']);
      $block->save();
    }

    // Delete config.
    $config->clear('alshaya_options_pages.' . $key);
    $config->save();

    // Rebuild routes so that routes get deleted.
    $this->routerBuilder->rebuild();
    // Invalidate page cache.
    Cache::invalidateTags([AlshayaOptionsListHelper::OPTIONS_PAGE_CACHETAG]);

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

      // Allow breadcrumb on url.
      $block = $this->entityTypeManager->getStorage('block')->load('breadcrumbs');
      $visibility = $block->getVisibility();
      $breadcrumb_url_string = PHP_EOL . '/' . $url;
      if (isset($visibility['request_path']['pages']) && !stripos($visibility['request_path']['pages'], $breadcrumb_url_string)) {
        $visibility['request_path']['pages'] = $visibility['request_path']['pages'] . $breadcrumb_url_string;
        $block->setVisibilityConfig('request_path', $visibility['request_path']);
        $block->save();
      }

    }

    $config->save();

    // Rebuild routes so that new routes get added.
    $this->routerBuilder->rebuild();

    // Invalidate page cache.
    Cache::invalidateTags([AlshayaOptionsListHelper::OPTIONS_PAGE_CACHETAG]);

    return parent::submitForm($form, $form_state);
  }

}
