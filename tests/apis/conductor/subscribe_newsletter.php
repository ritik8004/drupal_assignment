<?php

/**
 * @file
 * Subscribe to newsletter.
 */

$mail = 'me+l5@nik4u.com';

require_once __DIR__ . '/../test.php';

$api = 'agent/newsletter/subscribe';

$method = 'POST';

$data = [];
$data['form_params']['email'] = $mail;

invoke_api($api, $method, $data);
