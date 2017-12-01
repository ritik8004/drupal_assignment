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
  $areas['kw'][] = ['en' => 'Abbasiyah', 'ar' => 'العباسية'];
  $areas['kw'][] = ['en' => 'Abdali', 'ar' => 'العبدلي'];
  $areas['kw'][] = ['en' => 'Abdulla Al Mubarak Al Sabah', 'ar' => 'عبد الله بن مبارك الصباح'];
  $areas['kw'][] = ['en' => 'Abdulla Al Salem', 'ar' => 'ضاحية عبد الله السالم'];
  $areas['kw'][] = ['en' => 'Abdulla Port', 'ar' => 'ميناء عبدالله'];
  $areas['kw'][] = ['en' => 'Abraq Khaitan', 'ar' => 'أبرق خيطان'];
  $areas['kw'][] = ['en' => 'Abu Fateera', 'ar' => 'أبو فطيرة'];
  $areas['kw'][] = ['en' => 'Abu Halifa', 'ar' => 'أبو حليفة'];
  $areas['kw'][] = ['en' => 'Abu Hasaniya', 'ar' => 'أبو الحصاني'];
  $areas['kw'][] = ['en' => 'Adailiya', 'ar' => 'العديلية'];
  $areas['kw'][] = ['en' => 'Ahmadi', 'ar' => 'الأحمدي'];
  $areas['kw'][] = ['en' => 'Al Adan', 'ar' => 'العدان'];
  $areas['kw'][] = ['en' => 'Al Bidaa', 'ar' => 'البدع'];
  $areas['kw'][] = ['en' => 'Al Hasawi', 'ar' => 'الحساوي'];
  $areas['kw'][] = ['en' => 'Al Jahra', 'ar' => 'الجهراء'];
  $areas['kw'][] = ['en' => 'Al Khiran Pearl City', 'ar' => 'لؤلؤة الخيران'];
  $areas['kw'][] = ['en' => 'Al Metlaa', 'ar' => 'المطلاع '];
  $areas['kw'][] = ['en' => 'Al Mubarakyia', 'ar' => 'المباركية'];
  $areas['kw'][] = ['en' => 'Al Qaysariya', 'ar' => 'القيصرية'];
  $areas['kw'][] = ['en' => 'Al Rabiah', 'ar' => 'الرابية'];
  $areas['kw'][] = ['en' => 'Al Salam', 'ar' => 'السلام'];
  $areas['kw'][] = ['en' => 'Al Salmi', 'ar' => 'السالمي'];
  $areas['kw'][] = ['en' => 'Al Shadadiya', 'ar' => 'الشدادية'];
  $areas['kw'][] = ['en' => 'Al Shuhada', 'ar' => 'الشهداء'];
  $areas['kw'][] = ['en' => 'Al Soor', 'ar' => 'السور'];
  $areas['kw'][] = ['en' => 'Al Wetiyah', 'ar' => 'الوطية'];
  $areas['kw'][] = ['en' => 'Al Zour', 'ar' => 'الزور'];
  $areas['kw'][] = ['en' => 'Ali Sabah Al Salem', 'ar' => 'ضاحية علي صباح السالم'];
  $areas['kw'][] = ['en' => 'Amghara', 'ar' => 'أمغرة'];
  $areas['kw'][] = ['en' => 'Amghara Industrial Area', 'ar' => 'أمغرة المنطقة الصناعية'];
  $areas['kw'][] = ['en' => 'Andalous', 'ar' => 'الأندلس'];
  $areas['kw'][] = ['en' => 'Aqeela', 'ar' => 'العقيلة'];
  $areas['kw'][] = ['en' => 'Ardiya', 'ar' => 'العارضية'];
  $areas['kw'][] = ['en' => 'Ardiya Industrial', 'ar' => 'العارضية الصناعية'];
  $areas['kw'][] = ['en' => 'Ayoun', 'ar' => 'العيون'];
  $areas['kw'][] = ['en' => "A'youn", 'ar' => 'العيون'];
  $areas['kw'][] = ['en' => 'Bayan', 'ar' => 'بيان'];
  $areas['kw'][] = ['en' => 'Beneid Al Gar', 'ar' => 'بنيد القار'];
  $areas['kw'][] = ['en' => 'Bnaider', 'ar' => 'بنيدر'];
  $areas['kw'][] = ['en' => 'Daiya', 'ar' => 'الدعية '];
  $areas['kw'][] = ['en' => 'Dajeej', 'ar' => 'الضجيج'];
  $areas['kw'][] = ['en' => 'Dasma', 'ar' => 'الدسمة'];
  $areas['kw'][] = ['en' => 'Dasman', 'ar' => 'دسمان'];
  $areas['kw'][] = ['en' => 'Dhaher', 'ar' => 'الظهر'];
  $areas['kw'][] = ['en' => 'Dhajeej', 'ar' => 'الضجيج'];
  $areas['kw'][] = ['en' => 'Doha', 'ar' => 'الدوحة'];
  $areas['kw'][] = ['en' => 'Fahad Al Ahmed Area', 'ar' => 'ضاحية فهد الأحمد'];
  $areas['kw'][] = ['en' => 'Fahaheel', 'ar' => 'الفحيحيل'];
  $areas['kw'][] = ['en' => 'Fahaheel Industrial', 'ar' => 'الفحيحيل الصناعية'];
  $areas['kw'][] = ['en' => 'Faiha', 'ar' => 'الفيحاء'];
  $areas['kw'][] = ['en' => 'Farwaniyah', 'ar' => 'الفروانية'];
  $areas['kw'][] = ['en' => 'Fintas', 'ar' => 'الفنطاس'];
  $areas['kw'][] = ['en' => 'Firdous', 'ar' => 'الفردوس'];
  $areas['kw'][] = ['en' => 'Funaitees', 'ar' => 'الفنيطيس'];
  $areas['kw'][] = ['en' => 'Granada', 'ar' => 'غرناطة'];
  $areas['kw'][] = ['en' => 'Hadiya', 'ar' => 'هدية'];
  $areas['kw'][] = ['en' => 'Hateen', 'ar' => 'حطين'];
  $areas['kw'][] = ['en' => 'Hawally', 'ar' => 'حولي'];
  $areas['kw'][] = ['en' => 'Ishbilya', 'ar' => 'اشبيلية'];
  $areas['kw'][] = ['en' => 'Jaber Al Ahmad', 'ar' => 'جابر الأحمد'];
  $areas['kw'][] = ['en' => 'Jaber Al Ali', 'ar' => 'ضاحية جابر العلي'];
  $areas['kw'][] = ['en' => 'Jabir Al Ahmad', 'ar' => 'جابر الأحمد'];
  $areas['kw'][] = ['en' => 'Jabriya', 'ar' => 'الجابرية'];
  $areas['kw'][] = ['en' => 'Jahra Industrial Area', 'ar' => 'الجهراء الصناعية '];
  $areas['kw'][] = ['en' => 'Jaleeb Al-Shyoukh', 'ar' => 'جليب الشيوخ'];
  $areas['kw'][] = ['en' => 'Jeleeb Shoyoukh', 'ar' => 'جليب الشيوخ'];
  $areas['kw'][] = ['en' => 'Julaiya', 'ar' => 'الجليعة'];
  $areas['kw'][] = ['en' => 'Kabd', 'ar' => 'كبد'];
  $areas['kw'][] = ['en' => 'Kaifan', 'ar' => 'كيفان'];
  $areas['kw'][] = ['en' => 'Khairan', 'ar' => 'الخيران'];
  $areas['kw'][] = ['en' => 'Khaitan', 'ar' => 'خيطان'];
  $areas['kw'][] = ['en' => 'Khaldiya', 'ar' => 'الخالدية'];
  $areas['kw'][] = ['en' => 'Khiran City', 'ar' => 'الخيران'];
  $areas['kw'][] = ['en' => 'Kuwait City', 'ar' => 'مدينة الكويت'];
  $areas['kw'][] = ['en' => 'Kuwait International Airport', 'ar' => 'مطار الكويت الدولي'];
  $areas['kw'][] = ['en' => 'Mahboula', 'ar' => 'المهبولة'];
  $areas['kw'][] = ['en' => 'Maidan Hawalliy', 'ar' => 'ميدان حولي'];
  $areas['kw'][] = ['en' => 'Manqaf', 'ar' => 'المنقف'];
  $areas['kw'][] = ['en' => 'Mansouriya', 'ar' => 'المنصورية'];
  $areas['kw'][] = ['en' => 'Masayel', 'ar' => 'المسايل'];
  $areas['kw'][] = ['en' => 'Messila', 'ar' => 'المسيلة'];
  $areas['kw'][] = ['en' => 'Minaa Abdulla', 'ar' => 'ميناء عبدالله'];
  $areas['kw'][] = ['en' => 'Mirqab', 'ar' => 'المرقاب'];
  $areas['kw'][] = ['en' => 'Mishref', 'ar' => 'مشرف'];
  $areas['kw'][] = ['en' => 'Mubarak Al Abdulla Al Jaber Area', 'ar' => 'ضاحية مبارك العبد الله الجابر'];
  $areas['kw'][] = ['en' => 'Mubarak Al kabeer', 'ar' => 'مبارك الكبير'];
  $areas['kw'][] = ['en' => 'Naeem', 'ar' => 'النعيم'];
  $areas['kw'][] = ['en' => 'Nahda', 'ar' => 'النهضة'];
  $areas['kw'][] = ['en' => 'Nassem', 'ar' => 'النسيم'];
  $areas['kw'][] = ['en' => 'North West Sulaibikhat', 'ar' => 'شمال غرب الصليبيخات'];
  $areas['kw'][] = ['en' => 'Nuwaiseeb', 'ar' => 'النويصيب '];
  $areas['kw'][] = ['en' => 'Nuzha', 'ar' => 'النزهة'];
  $areas['kw'][] = ['en' => 'Omariyah', 'ar' => 'العمرية'];
  $areas['kw'][] = ['en' => 'Qadsiya', 'ar' => 'القادسية'];
  $areas['kw'][] = ['en' => 'Qairawan', 'ar' => 'القيروان'];
  $areas['kw'][] = ['en' => 'Qasr', 'ar' => 'القصر'];
  $areas['kw'][] = ['en' => 'Qeirawan', 'ar' => 'القيروان'];
  $areas['kw'][] = ['en' => 'Qiblah', 'ar' => 'القبلة'];
  $areas['kw'][] = ['en' => 'Qublah', 'ar' => 'القبلة'];
  $areas['kw'][] = ['en' => 'Qublah Area', 'ar' => 'منطقة القبلة'];
  $areas['kw'][] = ['en' => 'Qurain', 'ar' => 'القرين'];
  $areas['kw'][] = ['en' => 'Qurtuba', 'ar' => 'قرطبة'];
  $areas['kw'][] = ['en' => 'Qusor', 'ar' => 'القصور'];
  $areas['kw'][] = ['en' => 'Rai', 'ar' => 'الري'];
  $areas['kw'][] = ['en' => 'Rawda', 'ar' => 'الروضة'];
  $areas['kw'][] = ['en' => 'Regiee', 'ar' => 'الرقعي'];
  $areas['kw'][] = ['en' => 'Rehab', 'ar' => 'الرحاب'];
  $areas['kw'][] = ['en' => 'Riqqa', 'ar' => 'الرقة'];
  $areas['kw'][] = ['en' => 'Rumaithiya', 'ar' => 'الرميثية'];
  $areas['kw'][] = ['en' => 'Saad Abdullah City', 'ar' => 'مدينة سعد العبد الله'];
  $areas['kw'][] = ['en' => 'Sabah Al Ahmad', 'ar' => 'مدينة صباح الأحمد السكنية'];
  $areas['kw'][] = ['en' => 'Sabah Al Naser', 'ar' => 'صباح الناصر'];
  $areas['kw'][] = ['en' => 'Sabah Al Salem', 'ar' => 'صباح السالم'];
  $areas['kw'][] = ['en' => 'Sabahiya', 'ar' => 'الصباحية'];
  $areas['kw'][] = ['en' => 'Sabhan', 'ar' => 'صبحان'];
  $areas['kw'][] = ['en' => 'Sabriya', 'ar' => 'الصابرية'];
  $areas['kw'][] = ['en' => 'Safat', 'ar' => 'الصفاة'];
  $areas['kw'][] = ['en' => 'Salhiya', 'ar' => 'الصالحية'];
  $areas['kw'][] = ['en' => 'Salmiya', 'ar' => 'السالمية'];
  $areas['kw'][] = ['en' => 'Salwa', 'ar' => 'سلوى'];
  $areas['kw'][] = ['en' => 'Sawaber', 'ar' => 'الصوابر'];
  $areas['kw'][] = ['en' => "Sha'ab", 'ar' => 'الشعب'];
  $areas['kw'][] = ['en' => 'Shamiyah', 'ar' => 'الشامية'];
  $areas['kw'][] = ['en' => 'Sharq', 'ar' => 'شرق'];
  $areas['kw'][] = ['en' => 'Shuaiba Industrial Area', 'ar' => 'منطقة الشعيبة الصناعية'];
  $areas['kw'][] = ['en' => 'Shuwaikh', 'ar' => 'الشويخ'];
  $areas['kw'][] = ['en' => 'Shuwaikh Education Area', 'ar' => 'الشويخ المنطقة التعليمية'];
  $areas['kw'][] = ['en' => 'Shuwaikh Health Area', 'ar' => 'الشويخ المنطقة الصحية'];
  $areas['kw'][] = ['en' => 'Shuwaikh Industrial Area', 'ar' => 'الشويخ المنطقة الصناعية'];
  $areas['kw'][] = ['en' => 'Shuwaikh Port', 'ar' => 'ميناء الشويخ'];
  $areas['kw'][] = ['en' => 'Sideeq', 'ar' => 'الصديق'];
  $areas['kw'][] = ['en' => 'South Sabahiyah', 'ar' => 'جنوب الصباحية '];
  $areas['kw'][] = ['en' => 'Subiya', 'ar' => 'الصبية'];
  $areas['kw'][] = ['en' => 'Sulaibikhat', 'ar' => 'الصليبيخات'];
  $areas['kw'][] = ['en' => 'Sulaibiyah', 'ar' => 'الصليبية'];
  $areas['kw'][] = ['en' => 'Sulaibiyah Industrial Area', 'ar' => 'منطقة الصليبية الصناعية'];
  $areas['kw'][] = ['en' => 'Surra', 'ar' => 'السرة'];
  $areas['kw'][] = ['en' => 'Taima', 'ar' => 'تيماء'];
  $areas['kw'][] = ['en' => 'Wafra', 'ar' => 'الوفرة'];
  $areas['kw'][] = ['en' => 'Waha', 'ar' => 'الواحة'];
  $areas['kw'][] = ['en' => 'Yarmouk', 'ar' => 'اليرموك'];
  $areas['kw'][] = ['en' => 'Zahra', 'ar' => 'الزهراء'];

  return isset($areas[$country_code]) ? $areas[$country_code] : [];
}
