<?php

/**
 * @file
 * Utility function for alshaya_block.
 */

use Drupal\block\Entity\Block;
use Drupal\block\BlockInterface;

/**
 * Helper function to switch block configuration.
 *
 * @param array $block_mapping
 *   Mapping of previous and new block name.
 */
function alshaya_block_switch_block_config(array $block_mapping) {
  // Return from here if block mapping is empty.
  if (empty($block_mapping)) {
    return;
  }

  foreach ($block_mapping as $prev_block_id => $new_block_id) {
    $existing_block_weight = NULL;
    $prev_block = Block::load($prev_block_id);
    if ($prev_block instanceof BlockInterface) {
      // Change the block weight based on the previous block weight.
      $existing_block_weight = $prev_block->getWeight();
      $prev_block->disable();
      $prev_block->save();
      // Load the new Block and Change the weight.
      if ($new_block_id) {
        $new_block = Block::load($new_block_id);
        if ($new_block instanceof BlockInterface) {
          $new_block->setWeight($existing_block_weight);
          // Update the block region based on the previous block.
          $new_block->setRegion($prev_block->getRegion());
          $new_block->save();
        }
      }
    }
  }
}

/**
 * Helper function to revert the block configuration.
 *
 * @param array $block_mapping
 *   Mapping of previous and new block name.
 */
function alshaya_block_revert_block_config(array $block_mapping) {
  // Return from here if block mapping is empty.
  if (empty($block_mapping)) {
    return;
  }

  foreach ($block_mapping as $prev_block_id => $new_block_id) {
    $block = Block::load($prev_block_id);
    if ($block instanceof BlockInterface) {
      $block->enable()->save();
    }
  }
}
