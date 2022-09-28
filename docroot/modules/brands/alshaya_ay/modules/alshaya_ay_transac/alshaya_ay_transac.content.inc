<?php

/**
 * @file
 * File to create default content for Alo Yoga.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default block_content.
 */
function alshaya_ay_transac_create_default_content() {
  // Create alo is a brand of purpose block.
  alshaya_ay_transac_create_brand_purpose_block_content();
}

/**
 * Create default alo is a brand of purpose block content.
 */
function alshaya_ay_transac_create_brand_purpose_block_content() {
  // Create alo is a brand of purpose block content.
  $block_content = BlockContent::create([
    'uuid' => '440b823b-e16c-457a-bc03-4a096f8ae171',
    'info' => 'Alo is a Brand of Purpose',
    'machine_name' => 'brand_purpose',
    'body' => [
      'value' => '<div class="block-name">
      <h4 class="block-name__title">ALO IS A BRAND OF PURPOSE</h4>
      <p class="block-name__subtitle">Your purchase helps support our mission to bring yoga to the world.</p>
      <div class="block-name__content">
        <div class="block-name__purpose">
          <div class="block-name__purpose-header block-name__accordion-header">
            <img class="block-name__purpose-icon" src="" loading="lazy" />
            <h6 class="block-name__purpose-title">100% Sweatshop-free</h6>
          </div>
          <div class="block-name__purpose-content block-name__accordion-content">
            <p class="block-name__purpose-info">Alo is human-kind. Our W.R.A.P.-certified factories provide fair wages & workers’ rights.</p>
            <a class="block-name__purpose-link">Learn More</a>
          </div>
        </div>
        <div class="block-name__purpose">
          <div class="block-name__purpose-header block-name__accordion-header">
            <img class="block-name__purpose-icon" src="" loading="lazy" />
            <h6 class="block-name__purpose-title">Alo is Eco-Aware</h6>
          </div>
          <div class="block-name__purpose-content block-name__accordion-content">
            <p class="block-name__purpose-info">We use advanced, eco-friendly production methods to reduce waste & protect our natural resources</p>
            <a class="block-name__purpose-link">Learn More</a>
          </div>
        </div>
      </div>
    </div>',
      'format' => 'rich_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block_content->save();
  // Create alo is a brand of purpose block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'Alo is a Brand of Purpose',
    'body' => [
      'value' => '<div class="block-name">
      <h4 class="block-name__title">ALO IS A BRAND OF PURPOSE</h4>
      <p class="block-name__subtitle">Your purchase helps support our mission to bring yoga to the world.</p>
      <div class="block-name__content">
        <div class="block-name__purpose">
          <div class="block-name__purpose-header block-name__accordion-header">
            <img class="block-name__purpose-icon" src="" loading="lazy" />
            <h6 class="block-name__purpose-title">100% Sweatshop-free</h6>
          </div>
          <div class="block-name__purpose-content block-name__accordion-content">
            <p class="block-name__purpose-info">Alo is human-kind. Our W.R.A.P.-certified factories provide fair wages & workers’ rights.</p>
            <a class="block-name__purpose-link">Learn More</a>
          </div>
        </div>
        <div class="block-name__purpose">
          <div class="block-name__purpose-header block-name__accordion-header">
            <img class="block-name__purpose-icon" src="" loading="lazy" />
            <h6 class="block-name__purpose-title">Alo is Eco-Aware</h6>
          </div>
          <div class="block-name__purpose-content block-name__accordion-content">
            <p class="block-name__purpose-info">We use advanced, eco-friendly production methods to reduce waste & protect our natural resources</p>
            <a class="block-name__purpose-link">Learn More</a>
          </div>
        </div>
      </div>
    </div>',
      'format' => 'rich_text',
    ],
  ]);
  $ar_block_content->save();
}
