<?php

namespace Drupal\alshaya_acm_dashboard\Controller;

use Drupal\acq_commerce\Conductor\APIWrapper;
use Drupal\alshaya_acm\AlshayaMdcQueueManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Alshaya acm dashboard routes.
 */
class AlshayaAcmDashboardController extends ControllerBase {

  /**
   * Acm dashboard manager instance.
   *
   * @var \Drupal\alshaya_acm\AlshayaMdcQueueManager
   */
  private $mdcQueueManager;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  private $dateFormatter;

  /**
   * Conductor API wrapper.
   *
   * @var \Drupal\acq_commerce\Conductor\APIWrapper
   */
  private $apiWrapper;

  /**
   * AlshayaAcmDashboardController constructor.
   *
   * @param \Drupal\alshaya_acm\AlshayaMdcQueueManager $mdcQueueManager
   *   Mdc Queue Manager
   * @param \Drupal\Core\Datetime\DateFormatter $dateFormatter
   *   Date formatter service.
   * @param \Drupal\acq_commerce\Conductor\APIWrapper $api_wrapper
   *   Conductor API  wrapper service.
   */
  public function __construct(AlshayaMdcQueueManager $mdcQueueManager,
                              DateFormatter $dateFormatter,
                              APIWrapper $api_wrapper) {
    $this->mdcQueueManager = $mdcQueueManager;
    $this->dateFormatter = $dateFormatter;
    $this->apiWrapper = $api_wrapper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('alshaya_acm.mdc_queue_manager'),
      $container->get('date.formatter'),
      $container->get('acq_commerce.agent_api')
    );
  }

  /**
   * Render the queue count for Magento & ACM queues.
   */
  public function queuesStatuses() {
    $acm_dashboard_settings = $this->config('alshaya_acm_dashboard.settings');
    $mdc_queues = $acm_dashboard_settings->get('queues');
    $mdc_queue_stats = [];

    foreach ($mdc_queues as $queue_machine_name => $label) {
      $mdc_queue_result = $this->mdcQueueManager->getMdcQueueStats($queue_machine_name);
      if (!$mdc_queue_result) {
        continue;
      }

      $mdc_queue_stats[$queue_machine_name]['stats'] = json_decode($mdc_queue_result);
      $mdc_queue_stats[$queue_machine_name]['label'] = $label;
    }

    $build['mdc_stats'] = [
      '#type' => 'table',
      '#caption' => $this
        ->t('MDC Queue Stats (Note: Queue count below is for all countries for the current Brand)'),
      '#header' => [
        $this->t('Queue Name'),
        $this->t('Number of items in Queue'),
        $this->t('ETA to ACM(approx.)'),
      ],
    ];

    foreach ($mdc_queue_stats as $key => $queue_stat) {
      $queue_processing_rate = $acm_dashboard_settings->get('processing_rate_' . $key);
      $build['mdc_stats']['#rows'][] = [
        $queue_stat['label'],
        $queue_stat['stats']->messages,
        $this->dateFormatter->format(REQUEST_TIME + (($queue_stat['stats']->messages * $queue_processing_rate) / 1000), 'custom', 'D M j G:i:s T Y'),
      ];
    }

    $acm_queue_stats = $this->apiWrapper->getQueueStatus();

    $build['acm_stats'] = [
      '#type' => 'table',
      '#caption' => $this->t('ACM Queue Stats. (Shows stats only for current brand & country)'),
      '#header' => [
        $this->t('Number of items in Queue'),
        $this->t('ETA to Drupal(approx.)'),
      ],
      '#rows' => [
        [
          $acm_queue_stats,
          $this->dateFormatter->format(REQUEST_TIME + (($acm_queue_stats * $acm_dashboard_settings->get('processing_rate_acm_queue')) / 1000), 'custom', 'D M j G:i:s T Y'),
        ],
      ],
    ];

    return $build;
  }

}
