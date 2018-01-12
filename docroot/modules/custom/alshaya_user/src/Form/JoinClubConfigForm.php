<?php

namespace Drupal\alshaya_user\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CartConfigForm.
 */
class JoinClubConfigForm extends ConfigFormBase {

  /**
   * Entity Type Manager service object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * JoinClubConfigForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service object.
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
    return 'alshaya_user_join_club';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['alshaya_user.join_club'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('alshaya_user.join_club');

    $fid = $form_state->getValue('image');
    $fid = $fid ? reset($fid) : '';
    if (isset($fid)) {
      $file = $this->entityTypeManager->getStorage('file')->load($fid);
      if ($file) {
        $file->status = FILE_STATUS_PERMANENT;
        $file->save();
      }
    }
    $config->set('join_club_image.fid', $fid);
    $config->set('join_club_description', $form_state->getValue('description'));

    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('alshaya_user.join_club');

    $form['image'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://',
      '#format' => 'rich_text',
      '#title' => $this->t('Image'),
      '#description' => $this->t('Leave blank to use default from code.'),
      '#default_value' => $config->get('join_club_image'),
    ];

    $form['description'] = [
      '#type' => 'text_format',
      '#format' => !empty($config->get('join_club_description.format')) ? $config->get('join_club_description.format') : 'rich_text',
      '#title' => $this->t('Description'),
      '#required' => TRUE,
      '#default_value' => $config->get('join_club_description.value'),
    ];

    return $form;
  }

}
