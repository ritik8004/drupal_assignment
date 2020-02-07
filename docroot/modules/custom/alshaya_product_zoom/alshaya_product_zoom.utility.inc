<?php

/**
 * @file
 * Utility file for getting settings and config for product zoom.
 */

/**
 * Get default settings for CloudZoom library.
 *
 * @return array
 *   Returns the default settings for CloudZoom library.
 */
function alshaya_product_zoom_cloudzoom_default_settings() {
  return [
    'slide_style' => 'product_zoom_medium_606x504',
    'zoom_style' => 'product_zoom_large_800x800',
    'thumb_style' => 'pdp_gallery_thumbnail',
    'zoom_width' => 'auto',
    'zoom_height' => 'auto',
    'zoom_position' => 'right',
    'adjust_x' => '0',
    'adjust_y' => '0',
    'tint' => '',
    'tint_opacity' => '0.25',
    'lens_opacity' => '0.85',
    'soft_focus' => 'false',
    'smooth_move' => '3',
  ];
}

/**
 * Get the rel attribute for Alshaya Product zoom.
 *
 * @param array $settings
 *   Product CloudZoom settings.
 *
 * @return string
 *   return the rel attribute.
 */
function alshaya_product_zoom_get_rel_cloudzoom(array $settings) {
  $string = '';
  $string .= "zoomWidth:'" . $settings['zoom_width'] . "'";
  $string .= ",zoomHeight:'" . $settings['zoom_height'] . "'";
  $string .= ",position:'" . $settings['zoom_position'] . "'";
  $string .= ",adjustX:'" . $settings['adjust_x'] . "'";
  $string .= ",adjustY:'" . $settings['adjust_y'] . "'";
  $string .= ",tint:'" . $settings['tint'] . "'";
  $string .= ",tintOpacity:'" . $settings['tint_opacity'] . "'";
  $string .= ",lensOpacity:'" . $settings['lens_opacity'] . "'";
  $string .= ",softFocus:" . $settings['soft_focus'];
  $string .= ",smoothMove:'" . $settings['smooth_move'] . "'";
  return $string;
}
