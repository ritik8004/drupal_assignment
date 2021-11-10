<?php

/**
 * @file
 * File to create default content for victoria's secret non transac.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default block_content.
 */
function alshaya_vs_non_transac_create_default_content() {
  // Create email sign up cta block.
  alshaya_vs_non_transac_create_email_signup_form_incentive_text_block_content();
}

/**
 * Create default email signup form block content.
 */
function alshaya_vs_non_transac_create_email_signup_form_incentive_text_block_content() {
  /** @var \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository */
  $entity_repository = \Drupal::service('entity.repository');
  $block = $entity_repository->loadEntityByUuid('block_content', '19207aad-0e43-4d3a-96d7-c74141f75bb1');
  if ($block instanceof BlockContent) {
    // Block already created.
    return;
  }

  // Create email signup form incentive text block content.
  $block_content = BlockContent::create([
    'uuid' => '19207aad-0e43-4d3a-96d7-c74141f75bb1',
    'info' => 'Sign up to get the latest from Victoria Secret, like:',
    'machine_name' => 'email_sign_up_content',
    'body' => [
      'value' => "<ul>
                    <li><p>Exclusive offers &amp; deals.</p></li>
                    <li><p>New product alerts.</p></li>
                    <li><p>Store events &amp; openings in your area.</p></li>
                    <li><p>News &amp; offers from victoria's secret and its sister brands</p></li>
                  </ul>",
      'format' => 'rich_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block_content->save();
  // Create email signup form incentive block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'الاشتراك للحصول على أحدث من فيكتوريا سكريت، مثل:',
    'body' => [
      'value' => "<ul>
                    <li><p>عروض حصرية & أمب؛ صفقات.</p></li>
                    <li><p>تنبيهات المنتجات الجديدة.</p></li>
                    <li><p>تخزين الأحداث والفتحات في منطقتك.</p></li>
                    <li><p>أخبار وعروض من سر فيكتوريا والعلامات التجارية الشقيقة لها</p></li>
                  </ul>",
      'format' => 'rich_text',
    ],
  ]);
  $ar_block_content->save();
}
