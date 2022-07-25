<?php

namespace Drupal\alshaya_hello_member\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Configure Alshaya Hello Member settings.
 */
class AlshayaHelloMemberSettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new Block.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alshaya_hello_member_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alshaya_hello_member.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_hello_member.settings');
    $form['hello_member_configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuration'),
      '#open' => TRUE,
    ];
    $form['hello_member_configuration']['status'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('status'),
      '#title' => $this->t('Enable Hello Member on site.'),
    ];
    $form['hello_member_configuration']['points_history_page_size'] = [
      '#type' => 'text',
      '#default_value' => $config->get('points_history_page_size'),
      '#title' => $this->t('Points history page size.'),
      '#description' => $this->t('Enter page size for points history page.'),
    ];
    $form['hello_member_configuration']['minimum_age'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum age'),
      '#default_value' => $config->get('minimum_age') ?? 18,
      '#required' => TRUE,
    ];

    $form['hello_member_configuration']['aura_integration_status'] = [
      '#type' => 'checkbox',
      '#default_value' => $config->get('aura_integration_status'),
      '#title' => $this->t('Enable aura integration with hello member..'),
      '#description' => $this->t('When aura integration is status with hello member,
        customer can choose to redeem aura points.'),
    ];

    $form['hello_member_configuration']['membership_info_content_node'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Membership info content node'),
      '#target_type' => 'node',
      '#selection_setttings' => [
        'target_bundles' => ['static_html', 'advanced_page'],
      ],
      '#default_value' => $config->get('membership_info_content_node') ?
      $this->entityTypeManager->getStorage('node')->load($config->get('membership_info_content_node')) : NULL,
      '#size' => '60',
      '#maxlength' => '60',
      '#description' => $this->t('Please select the node which will be redirect on click of membership info link.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('alshaya_hello_member.settings')
      ->set('status', $form_state->getValue('status'))
      ->set('aura_integration_status', $form_state->getValue('aura_integration_status'))
      ->set('points_history_page_size', $form_state->getValue('points_history_page_size'))
      ->set('minimum_age', $form_state->getValue('minimum_age'))
      ->set('membership_info_content_node', $form_state->getValue('membership_info_content_node'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
