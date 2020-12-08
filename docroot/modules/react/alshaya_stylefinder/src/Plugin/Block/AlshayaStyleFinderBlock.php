<?php

namespace Drupal\alshaya_stylefinder\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides Style Finder block.
 *
 * @Block(
 *   id = "alshaya_stylefinder",
 *   admin_label = @Translation("Alshaya Style Finder")
 * )
 */
class AlshayaStyleFinderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Entity Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              EntityTypeManagerInterface $entity_type_manager
                            ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();
    $form['reference_quiz_node_id'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Create a quiz'),
      '#description' => $this->t('Allows to create a quiz.'),
      '#default_value' => isset($config['reference_quiz_node_id']) ? $this->entityTypeManager->getStorage('node')->load($config['reference_quiz_node_id'][0]['target_id']) : '',
      '#tags' => TRUE,
      '#selection_settings' => [
        'target_bundles' => ['quiz'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['reference_quiz_node_id'] = $values['reference_quiz_node_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $quiz_node_id = $config['reference_quiz_node_id'][0]['target_id'];
    $quizDetails = [];
    $cache_tags = [];
    if (!empty($quiz_node_id)) {
      $quiz_node = $this->entityTypeManager->getStorage('node')->load($quiz_node_id);
      $cache_tags = Cache::mergeTags($cache_tags, array_merge($quiz_node->getCacheTags()));
      $quizDetails['quiz_title'] = $quiz_node->title->value;
      $quizDetails['quiz_instruction'] = strip_tags($quiz_node->field_instruction->value) ?? NULL;
      $quizDetails['quiz_type'] = $quiz_node->field_quiz_type->value ?? NULL;
      foreach ($quiz_node->field_quiz_question as $question) {
        $ques_nid = $question->target_id;
        $question_details[$ques_nid] = $this->quizQuestionDetails($ques_nid);
      }
      $quizDetails['question'] = $question_details;
    }
    return [
      '#markup' => '<div id="style-finder-container"></div>',
      '#attached' => [
        'library' => [
          'alshaya_stylefinder/alshaya_stylefinder',
        ],
        'drupalSettings' => [
          'styleFinder' => [
            'quizDetails' => $quizDetails,
          ],
        ],
      ],
      '#cache' => [
        'tags' => $cache_tags,
      ],
    ];
  }

  /**
   * Custom Function to return question node details.
   *
   * @param mixed $q_nid
   *   Node id of the question.
   *
   * @return array
   *   The field details from the node.
   */
  private function quizQuestionDetails($q_nid) {
    $question_details = [];
    $question_node = $this->entityTypeManager->getStorage('node')->load($q_nid);
    $question_details['ques_instruction'] = strip_tags($question_node->field_instruction->value) ?? NULL;
    $question_details['title'] = $question_node->title->value;
    foreach ($question_node->field_answer as $answer) {
      $answer_nid = $answer->target_id;
      $answer_details[$answer_nid] = $this->quizAnswerDetails($answer_nid);
    }
    $question_details['answer'] = $answer_details;
    if (!empty($question_node->field_references->target_id)) {
      $term_id = $question_node->field_references->target_id;
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
      if (!$term->get('path')->isEmpty()) {
        $term_alias = $term->get('path')->alias;
      }
    }
    $question_details['see_more_reference'] = $term_alias ?? NULL;
    return $question_details;
  }

  /**
   * Custom Function to return answer node details.
   *
   * @param mixed $a_nid
   *   Node id of the question.
   *
   * @return array
   *   The field details from the node.
   */
  private function quizAnswerDetails($a_nid) {
    $answer_details = [];
    $answer_node = $this->entityTypeManager->getStorage('node')->load($a_nid);
    // To get the Product Image URL.
    $image_id = $answer_node->field_product_image->target_id;
    $imageSrc = NULL;
    if (!empty($image_id)) {
      $imageSrc = $this->getFileUrlFromId($image_id);
    }

    // To get the next question details if present.
    if ($answer_node->hasField('field_next_question')) {
      $next_question = $answer_node->get('field_next_question')->getValue();
    }
    $next_ques_details = [];
    if (!empty($next_question)) {
      foreach ($next_question as $next_ques) {
        $next_ques_nid = $next_ques['target_id'];
        $next_ques_details[$next_ques_nid] = $this->quizQuestionDetails($next_ques_nid);
      }
    }

    // To fetch the choice of Answer node.
    $choice = NULL;
    if (!empty($answer_node->field_choice_4->target_id)) {
      $term_id = $answer_node->field_choice_4->target_id;
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
      $choice = $term->getName();
      $answer_details['choice'] = $choice;
    }
    else {
      if ($answer_node->field_choice_1->value) {
        $answer_details['choice'] = $answer_node->field_choice_1->value ?? NULL;
      }
      if ($answer_node->field_choice_2->value) {
        $answer_details['choice'] = $answer_node->field_choice_2->value ?? NULL;
      }
      if ($answer_node->field_choice_3->value) {
        $answer_details['choice'] = $answer_node->field_choice_3->value ?? NULL;
      }
    }

    $answer_details['title'] = $answer_node->title->value;
    $answer_details['description'] = strip_tags($answer_node->field_answer_summary->value) ?? NULL;
    $answer_details['image_url'] = $imageSrc;
    $answer_details['question'] = $next_ques_details;
    return $answer_details;
  }

  /**
   * Returns Image path.
   *
   * @param int $file_target_id
   *   The id of the image file.
   *
   * @return string
   *   The URL of the Image file.
   */
  public function getFileUrlFromId(int $file_target_id) {
    $file_url = "";
    if ($file_target_id) {
      $file = $this->entityTypeManager->getStorage('file')->load($file_target_id);
      $file_url = file_create_url($file->getFileUri());
    }
    return $file_url;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'languages',
    ]);
  }

}
