<?php

namespace Drupal\alshaya_master\Decorator;

use Drupal\Core\Form\FormAjaxResponseBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Alshaya Form Ajax Response Builder.
 *
 * @package Drupal\alshaya_master\Decorator
 */
class AlshayaFormAjaxResponseBuilder extends FormAjaxResponseBuilder {

  use LoggerChannelTrait;

  /**
   * {@inheritdoc}
   */
  public function buildResponse(Request $request,
                                array $form,
                                FormStateInterface $form_state,
                                array $commands) {
    try {
      return parent::buildResponse($request, $form, $form_state, $commands);
    }
    catch (\HttpException $e) {
      // Add more details in logs to be able to understand reason behind
      // failure and fix it properly.
      if ($e->getMessage() === 'The specified #ajax callback is empty or not callable.') {
        if (($triggering_element = $form_state->getTriggeringElement()) && isset($triggering_element['#ajax']['callback'])) {
          $callback = $triggering_element['#ajax']['callback'];
          $logger = $this->getLogger('AlshayaFormAjaxResponseBuilder');

          $logger->error('The specified #ajax callback @callback is empty or not callable.', [
            '@callback' => $callback,
          ]);

          if (Settings::get('alshaya_form_ajax_callback_error_log_request_data', 1)) {
            $logger->error('Full data @data', [
              '@data' => json_encode($form_state->getUserInput()),
            ]);
          }
        }
      }

      throw $e;
    }
  }

}
