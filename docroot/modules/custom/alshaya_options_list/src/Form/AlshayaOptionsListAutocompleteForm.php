<?php

namespace Drupal\alshaya_options_list\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\alshaya_options_list\AlshayaOptionsListService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Autocomplete search form for the attributes page.
 *
 * @internal
 */
class AlshayaOptionsListAutocompleteForm extends FormBase {

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Alshaya Options List Service.
   *
   * @var Drupal\alshaya_options_list\AlshayaOptionsListService
   */
  private $alshayaOptionsService;

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param Drupal\alshaya_options_list\AlshayaOptionsListService $alshaya_options_service
   *   Alshaya options service.
   */
  public function __construct(AlshayaOptionsListService $alshaya_options_service) {
    $this->alshayaOptionsService = $alshaya_options_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_options_list.alshaya_options_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_options_list_autocomplete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $form_arg = NULL) {
    $form['alshaya_options_list_autocomplete_form'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#attributes' => [
        'placeholder' => [
          $this->t('Search for a fragrance...'),
        ],
      ],
      '#ajax' => [
        'callback' => '::ajaxSearchCallback',
        'event' => 'keyup',
        'wrapper' => 'ajax-' . $form_arg['attribute_code'],
      ],
    ];
    $form['attribute_code'] = [
      '#type' => 'hidden',
      '#value' => $form_arg['attribute_code'] ?? '',
    ];
    $form['page_code'] = [
      '#type' => 'hidden',
      '#value' => $form_arg['page_code'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSearchCallback(array &$form, FormStateInterface $form_state) {
    $options_list = [];
    $search_string = $form_state->getValue('alshaya_options_list_autocomplete_form');
    $page_code = $form_state->getValue('page_code');
    $attribute_code = $form_state->getValue('attribute_code');
    $config = $this->config('alshaya_options_list.admin_settings');
    $attribute_options = $config->get('alshaya_options_pages');
    $group = $attribute_options[$page_code]['attribute_details'][$attribute_code]['group'];
    $show_images = $attribute_options[$page_code]['attribute_details'][$attribute_code]['show-images'];
    $options_list['terms'] = $this->alshayaOptionsService->fetchAllTermsForAttribute($attribute_code, $show_images, $group, $search_string);
    if ($group) {
      $options_list['group'] = TRUE;
      $options_list['terms'] = $this->alshayaOptionsService->groupAlphabetically($options_list['terms']);
    }

    $attribute_markup = [
      '#theme' => 'alshaya_options_attribute',
      '#option' => $options_list,
      '#attribute_code' => $attribute_code,
    ];

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('.ajax-' . $attribute_code, $attribute_markup));
    return $response;
  }

}
