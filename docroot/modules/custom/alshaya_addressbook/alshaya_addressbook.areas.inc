<?php

/**
 * @file
 * Utility file to add areas in system as taxonomy terms.
 */

use Drupal\taxonomy\Entity\Term;

/**
 * Utility function to create terms for each area in site's country.
 */
function _alshaya_addressbook_create_area_terms() {
  $country_code = _alshaya_custom_get_site_level_country_code();

  $area_list = _alshaya_addressbook_get_areas($country_code);

  if (empty($area_list)) {
    return;
  }

  // Vocabulary to store these areas into.
  $vid = 'area_list';

  foreach ($area_list as $area) {
    try {
      // Create term for each area.
      $term = Term::create([
        'name' => $area['en'],
        'vid' => $vid,
      ]);

      $term = $term->addTranslation('ar', [
        'name' => $area['ar'],
      ]);

      $term->save();
    }
    catch (\Exception $e) {
      // Log error if any.
      \Drupal::logger('alshaya_addressbook')->error($e->getMessage());
    }
  }
}

/**
 * Utility function to get all areas for specific country with its translation.
 *
 * @param string $country_code
 *   Country code to get areas for.
 *
 * @return array
 *   Areas for the country.
 */
function _alshaya_addressbook_get_areas($country_code = 'kw') {
  $country_code = strtolower($country_code);

  $areas = [];

  $areas['kw'] = [];
  $areas['kw'][] = [
    'en' => 'Abdullah Al-Salem',
    'ar' => 'ضاحية عبد الله السالم',
  ];
  $areas['kw'][] = ['en' => 'Adailiya', 'ar' => 'العديلية'];
  $areas['kw'][] = ['en' => 'Al-Daiya', 'ar' => 'الدعية'];
  $areas['kw'][] = ['en' => 'Al-Dasma', 'ar' => 'الدسمة'];
  $areas['kw'][] = ['en' => 'Al-Doha', 'ar' => 'الدوحة'];
  $areas['kw'][] = ['en' => 'Al-Faiha', 'ar' => 'الفيحاء'];
  $areas['kw'][] = ['en' => 'Al-Mansouriah', 'ar' => 'المنصورية'];
  $areas['kw'][] = ['en' => 'Al-Qadisiya', 'ar' => 'القادسية'];
  $areas['kw'][] = ['en' => 'Al-Qibla', 'ar' => 'القبلة'];
  $areas['kw'][] = ['en' => 'Al-Shamiya', 'ar' => 'الشامية'];
  $areas['kw'][] = ['en' => 'Al-Shuwaikh', 'ar' => 'الشويخ'];
  $areas['kw'][] = ['en' => 'Al-Soor', 'ar' => 'السور'];
  $areas['kw'][] = ['en' => 'Al-Yarmouk', 'ar' => 'اليرموك'];
  $areas['kw'][] = ['en' => 'Al-Nuzha', 'ar' => 'النزهة'];
  $areas['kw'][] = ['en' => 'Bneid Al-Gar', 'ar' => 'بنيد القار'];
  $areas['kw'][] = ['en' => 'Dasman', 'ar' => 'دسمان'];
  $areas['kw'][] = ['en' => 'Garnata', 'ar' => 'غرناطة'];
  $areas['kw'][] = ['en' => 'Jaber Al-Ahmed', 'ar' => 'مدينة جابر الأحمد'];
  $areas['kw'][] = ['en' => 'Kaifan', 'ar' => 'كيفان'];
  $areas['kw'][] = ['en' => 'Khaldiya', 'ar' => 'الخالدية'];
  $areas['kw'][] = ['en' => 'Kuwait City', 'ar' => 'مدينة الكويت'];
  $areas['kw'][] = ['en' => 'Mirqab', 'ar' => 'المرقاب'];
  $areas['kw'][] = ['en' => 'Al-Nahda', 'ar' => 'النهضة'];
  $areas['kw'][] = ['en' => 'Qairawan', 'ar' => 'القيروان'];
  $areas['kw'][] = ['en' => 'Qurtuba', 'ar' => 'قرطبة'];
  $areas['kw'][] = ['en' => 'Rawdah', 'ar' => 'الروضة'];
  $areas['kw'][] = ['en' => 'Salhia', 'ar' => 'الصالحية'];
  $areas['kw'][] = ['en' => 'Sawaber', 'ar' => 'الصوابر'];
  $areas['kw'][] = ['en' => 'Sharq', 'ar' => 'شرق'];
  $areas['kw'][] = [
    'en' => 'Shuwaikh Educational Area',
    'ar' => 'الشويخ المنطقة التعليمية',
  ];
  $areas['kw'][] = [
    'en' => 'Shuwaikh Industrial Area',
    'ar' => 'الشويخ المنطقة الصناعية',
  ];
  $areas['kw'][] = ['en' => 'Sulaibikhat', 'ar' => 'الصليبيخات'];
  $areas['kw'][] = ['en' => 'Surra', 'ar' => 'السرة'];
  $areas['kw'][] = ['en' => 'Wateya', 'ar' => 'الوطية'];
  $areas['kw'][] = ['en' => 'Al-Salam', 'ar' => 'السلام'];
  $areas['kw'][] = ['en' => 'Al-Shuhada', 'ar' => 'الشهداء'];
  $areas['kw'][] = ['en' => 'Bayan', 'ar' => 'بيان'];
  $areas['kw'][] = ['en' => 'Bidaa', 'ar' => 'البدع'];
  $areas['kw'][] = ['en' => 'Hateen', 'ar' => 'حطين'];
  $areas['kw'][] = ['en' => 'Hawalli', 'ar' => 'حولي'];
  $areas['kw'][] = ['en' => 'Jabriya', 'ar' => 'الجابرية'];
  $areas['kw'][] = ['en' => 'Midan Hawalli', 'ar' => 'ميدان حولي'];
  $areas['kw'][] = ['en' => 'Mishref', 'ar' => 'مشرف'];
  $areas['kw'][] = [
    'en' => 'Mubarak Al-Abdullah',
    'ar' => 'ضاحية مبارك العبد الله الجابر',
  ];
  $areas['kw'][] = ['en' => 'Rumaithiya', 'ar' => 'الرميثية'];
  $areas['kw'][] = ['en' => 'Salmiya', 'ar' => 'السالمية'];
  $areas['kw'][] = ['en' => 'Salwa', 'ar' => 'سلوى'];
  $areas['kw'][] = ['en' => 'Shaab', 'ar' => 'الشعب'];
  $areas['kw'][] = ['en' => 'Zahraa', 'ar' => 'الزهراء'];
  $areas['kw'][] = ['en' => 'Abbasiya', 'ar' => 'العباسية'];
  $areas['kw'][] = [
    'en' => 'Abdullah Al-Mubarak Al-Sabah',
    'ar' => 'ضاحية عبد الله المبارك',
  ];
  $areas['kw'][] = ['en' => 'Abraq Khaitan', 'ar' => 'أبرق خيطان'];
  $areas['kw'][] = ['en' => 'Al-Rai', 'ar' => 'الري'];
  $areas['kw'][] = ['en' => 'Andalus', 'ar' => 'الأندلس'];
  $areas['kw'][] = ['en' => 'Ardiya', 'ar' => 'العارضية'];
  $areas['kw'][] = ['en' => 'Dajeej', 'ar' => 'الضجيج'];
  $areas['kw'][] = ['en' => 'Farwaniyah', 'ar' => 'الفروانية'];
  $areas['kw'][] = ['en' => 'Firdous', 'ar' => 'الفردوس'];
  $areas['kw'][] = ['en' => 'Hasawy', 'ar' => 'الحساوي'];
  $areas['kw'][] = ['en' => 'Ishbilia', 'ar' => 'اشبيلية'];
  $areas['kw'][] = ['en' => 'Jleeb Al-Shuyoukh', 'ar' => 'جليب الشيوخ'];
  $areas['kw'][] = ['en' => 'Khaitan', 'ar' => 'خيطان'];
  $areas['kw'][] = [
    'en' => 'Kuwait International Airport',
    'ar' => 'مطار الكويت الدولي',
  ];
  $areas['kw'][] = ['en' => 'New Khitan', 'ar' => 'خيطان الجديدة'];
  $areas['kw'][] = ['en' => 'Omariyah', 'ar' => 'العمرية'];
  $areas['kw'][] = ['en' => 'Shadadiya', 'ar' => 'الشدادية'];
  $areas['kw'][] = ['en' => 'Rabiya', 'ar' => 'الرابية'];
  $areas['kw'][] = ['en' => 'Rahab', 'ar' => 'الرحاب'];
  $areas['kw'][] = ['en' => 'Riggae', 'ar' => 'الرقعي'];
  $areas['kw'][] = ['en' => 'Sabah Al-Naser', 'ar' => 'ضاحية صباح الناصر'];
  $areas['kw'][] = ['en' => 'Sulaibia', 'ar' => 'الصليبية'];
  $areas['kw'][] = ['en' => 'Abu Halifa', 'ar' => 'أبو حليفة'];
  $areas['kw'][] = ['en' => 'Al-Ahmadi', 'ar' => 'الأحمدي'];
  $areas['kw'][] = ['en' => 'Al-Wafrah', 'ar' => 'الوفرة'];
  $areas['kw'][] = [
    'en' => 'Ali Sabah Al-Salem',
    'ar' => 'ضاحية علي صباح السالم',
  ];
  $areas['kw'][] = ['en' => 'Aqila', 'ar' => 'العقيلة'];
  $areas['kw'][] = ['en' => 'Daher', 'ar' => 'الظهر'];
  $areas['kw'][] = ['en' => 'Fahaheel', 'ar' => 'الفحيحيل'];
  $areas['kw'][] = ['en' => 'Fahad Al-Ahmad', 'ar' => 'ضاحية فهد الأحمد'];
  $areas['kw'][] = ['en' => 'Fintas', 'ar' => 'الفنطاس'];
  $areas['kw'][] = ['en' => 'Hadiya', 'ar' => 'هدية'];
  $areas['kw'][] = ['en' => 'Jaber Al-Ali', 'ar' => 'ضاحية جابر العلي'];
  $areas['kw'][] = ['en' => 'Mahboula', 'ar' => 'المهبولة'];
  $areas['kw'][] = ['en' => 'Mangaf', 'ar' => 'المنقف'];
  $areas['kw'][] = ['en' => 'Riqqa', 'ar' => 'الرقة'];
  $areas['kw'][] = ['en' => 'Shuaiba', 'ar' => 'الشعيبة'];
  $areas['kw'][] = ['en' => 'Subah Al-Ahmad', 'ar' => 'مدينة صباح الأحمد'];
  $areas['kw'][] = ['en' => 'Subahiya', 'ar' => 'الصباحية'];
  $areas['kw'][] = ['en' => 'Umm Al-Hayman', 'ar' => 'أم الهيمان'];
  $areas['kw'][] = ['en' => 'Al-Zoor', 'ar' => 'الزور'];
  $areas['kw'][] = ['en' => 'Bnaider', 'ar' => 'بنيدر'];
  $areas['kw'][] = ['en' => 'Khairan', 'ar' => 'الخيران'];
  $areas['kw'][] = ['en' => 'Abu Fatira', 'ar' => 'أبو فطيرة'];
  $areas['kw'][] = ['en' => 'Abu Al-Hasani', 'ar' => 'أبو الحصاني'];
  $areas['kw'][] = ['en' => 'Al-Adan', 'ar' => 'العدان'];
  $areas['kw'][] = ['en' => 'Al-Qurain', 'ar' => 'القرين'];
  $areas['kw'][] = ['en' => 'Al-Qusour', 'ar' => 'القصور'];
  $areas['kw'][] = ['en' => 'Funaitees', 'ar' => 'الفنيطيس'];
  $areas['kw'][] = ['en' => 'Masayel', 'ar' => 'المسايل'];
  $areas['kw'][] = ['en' => 'Messila', 'ar' => 'المسيلة'];
  $areas['kw'][] = ['en' => 'Mubarak Al-Kabeer', 'ar' => 'مبارك الكبير'];
  $areas['kw'][] = ['en' => 'Sabah Al-Salem', 'ar' => 'صباح السالم'];
  $areas['kw'][] = ['en' => 'Sabhan', 'ar' => 'صبحان'];
  $areas['kw'][] = ['en' => 'Abdelli', 'ar' => 'العبدلي'];
  $areas['kw'][] = ['en' => 'Al-Naseem', 'ar' => 'النسيم'];
  $areas['kw'][] = ['en' => 'Al-Qasr', 'ar' => 'القصر'];
  $areas['kw'][] = ['en' => 'Al-Salmi', 'ar' => 'السالمي'];
  $areas['kw'][] = ['en' => 'Amghara', 'ar' => 'أمغرة'];
  $areas['kw'][] = ['en' => 'Jaber Al-Ahmad', 'ar' => 'جابر الأحمد'];
  $areas['kw'][] = ['en' => 'Jahra', 'ar' => 'الجهراء'];
  $areas['kw'][] = ['en' => 'Kabd', 'ar' => 'كبد'];
  $areas['kw'][] = ['en' => 'Kazma', 'ar' => 'كاظمة'];
  $areas['kw'][] = ['en' => 'Mutla', 'ar' => 'المطلاع'];
  $areas['kw'][] = ['en' => 'Naeem', 'ar' => 'النعيم'];
  $areas['kw'][] = ['en' => 'New Jahra', 'ar' => 'الجهراء الجديدة'];
  $areas['kw'][] = ['en' => 'Old Jahra', 'ar' => 'الجهراء القديمة'];
  $areas['kw'][] = ['en' => 'Ouyoun', 'ar' => 'العيون'];
  $areas['kw'][] = ['en' => 'Qaisariya', 'ar' => 'القيصرية'];
  $areas['kw'][] = ['en' => 'Saad Al-Abdallah', 'ar' => 'مدينة سعد العبد الله'];
  $areas['kw'][] = [
    'en' => 'Sulaibiya Agriculture',
    'ar' => 'الصليبية الزراعية',
  ];
  $areas['kw'][] = [
    'en' => 'Sulaibiya industrial',
    'ar' => 'الصليبية الصناعية',
  ];
  $areas['kw'][] = ['en' => 'Sulaibiya Shabiya', 'ar' => 'الصليبية الحرفية'];
  $areas['kw'][] = ['en' => 'Taima', 'ar' => 'تيماء'];
  $areas['kw'][] = ['en' => 'Waha', 'ar' => 'الواحة'];
  $areas['kw'][] = ['en' => 'Rawdatain', 'ar' => 'الروضتين'];
  $areas['kw'][] = ['en' => 'Um Al-Aish', 'ar' => 'أم العيش'];
  $areas['kw'][] = ['en' => 'Subaiya', 'ar' => 'الصبية'];

  return $areas[$country_code] ?? [];
}
