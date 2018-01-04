<?php

/**
 * @file
 * File to create default content for Debenhams.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * Create default block_content.
 */
function alshaya_d_create_default_content() {
  // Create copyright block.
  alshaya_d_create_copyright_block_content();
  // Create email sign up cta block.
  alshaya_d_create_email_signup_form_incentive_text_block_content();
}

/**
 * Create default copyright block content.
 */
function alshaya_d_create_copyright_block_content() {
  // Create copyright block content.
  $block_content = BlockContent::create([
    'uuid' => '340b823b-e16c-457a-bc03-4a096f8ae171',
    'info' => 'Copyright',
    'machine_name' => 'copyright',
    'body' => [
      'value' => "<p>&copy; Debenhams. All Rights Reserved</p>",
      'format' => 'rich_text',
    ],
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block_content->save();
  // Create copyright block translation.
  $ar_block_content = $block_content->addTranslation('ar', [
    'info' => 'Copyright',
    'body' => [
      'value' => "<p>دبنهامز. كل الحقوق محفوظة. &copy;</p>",
      'format' => 'rich_text',
    ],
  ]);
  $ar_block_content->save();
}

/**
 * Create default email signup form block content.
 */
function alshaya_d_create_email_signup_form_incentive_text_block_content() {
  // Create email signup form incentive text block content.
  $block_content = BlockContent::create([
    'uuid' => '19207aad-0e43-4d3a-96d7-c74141f75bb1',
    'info' => 'Sign up to get the latest from Debenhams, like:',
    'machine_name' => 'email_sign_up_content',
    'body' => [
      'value' => "<ul>
                    <li><p>Exclusive offers &amp; deals.</p></li>
                    <li><p>New product alerts.</p></li>
                    <li><p>Store events &amp; openings in your area.</p></li>
                    <li><p>News &amp; offers from Debenhams and its sister brands</p></li>
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
