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
 * Create brand of purpose block for AY PDP.
 */
function alshaya_ay_transac_create_brand_purpose_block_content() {
  $block_content = BlockContent::create([
    'uuid' => '440b823b-e16c-457a-bc03-4a096f8ae171',
    'info' => 'Alo is a Brand of Purpose',
    'machine_name' => 'brand_purpose',
    'body' => [
      'value' => '<div class="brand-purpose hide-block load-after-product-data-fetch">
      <h4 class="brand-purpose__title">ALO IS A BRAND OF PURPOSE</h4>
      <p class="brand-purpose__subtitle">Your purchase helps support our mission to bring yoga to the world.</p>
      <div class="brand-purpose__content">
        <div class="brand-purpose__purpose alshaya-accordion--mobile">
          <div class="brand-purpose__purpose-header alshaya-accordion-header">
            <img class="brand-purpose__purpose-icon" src="/themes/custom/transac/alshaya_alo_yoga/imgs/icons/sweatshop-free.svg?v1" loading="lazy" />
            <h6 class="brand-purpose__purpose-title">100% Sweatshop-free</h6>
          </div>
          <div class="brand-purpose__purpose-content alshaya-accordion-content">
            <p class="brand-purpose__purpose-info">Alo is human-kind. Our W.R.A.P.-certified factories provide fair wages & workers’ rights.</p>
            <a class="brand-purpose__purpose-link" href="#">Learn More</a>
          </div>
        </div>
        <div class="brand-purpose__purpose alshaya-accordion--mobile">
          <div class="brand-purpose__purpose-header alshaya-accordion-header">
            <img class="brand-purpose__purpose-icon" src="/themes/custom/transac/alshaya_alo_yoga/imgs/icons/eco-aware.svg?v1" loading="lazy" />
            <h6 class="brand-purpose__purpose-title">Alo is Eco-Aware</h6>
          </div>
          <div class="brand-purpose__purpose-content alshaya-accordion-content">
            <p class="brand-purpose__purpose-info">We use advanced, eco-friendly production methods to reduce waste & protect our natural resources</p>
            <a class="brand-purpose__purpose-link" href="#">Learn More</a>
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
  // Create brand of purpose block for AY PDP translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'Alo is a Brand of Purpose',
    'body' => [
      'value' => '<div class="brand-purpose">
      <h4 class="brand-purpose__title">ألو يوغا هي علامة تجارية تحمل هدفاً</h4>
      <p class="brand-purpose__subtitle">إن شرائك يساعدنا على إتمام مهمتنا ونشر اليوغا في العالم</p>
      <div class="brand-purpose__content">
        <div class="brand-purpose__purpose alshaya-accordion--mobile">
          <div class="brand-purpose__purpose-header alshaya-accordion-header">
            <img class="brand-purpose__purpose-icon" src="/themes/custom/transac/alshaya_alo_yoga/imgs/icons/sweatshop-free.svg?v1" loading="lazy" />
            <h6 class="brand-purpose__purpose-title">مصانع غير مُستغلة للعمال بنسبة %100</h6>
          </div>
          <div class="brand-purpose__purpose-content alshaya-accordion-content">
            <p class="brand-purpose__purpose-info">ألو هي ماركة تهتم بالإنسان. وتصنع منتجاتها في مصانع معتمدة بشهادة (WRAP) التي تثبت امتثالها لمبادئ التصنيع الأخلاقية عبر توفير الأجور العادلة والحفاظ على حقوق العمال</p>
            <a class="brand-purpose__purpose-link" href="#">أعرف أكثر</a>
          </div>
        </div>
        <div class="brand-purpose__purpose alshaya-accordion--mobile">
          <div class="brand-purpose__purpose-header alshaya-accordion-header">
            <img class="brand-purpose__purpose-icon" src="/themes/custom/transac/alshaya_alo_yoga/imgs/icons/eco-aware.svg?v1" loading="lazy" />
            <h6 class="brand-purpose__purpose-title">ألو يوغا هي ماركة صديقة للبيئة</h6>
          </div>
          <div class="brand-purpose__purpose-content alshaya-accordion-content">
            <p class="brand-purpose__purpose-info">نحن نستخدم وسائل إنتاج متقدمة وصديقة للبيئة لتقليل النفايات وحماية مصادرنا الطبيعية</p>
            <a class="brand-purpose__purpose-link" href="#">أعرف أكثر</a>
          </div>
        </div>
      </div>
    </div>',
      'format' => 'rich_text',
    ],
  ]);
  $ar_block_content->save();
}
