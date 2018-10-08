<?php

/**
 * List all known Conductor environments keyed by environment machine name.
 */

global $conductors;

$conductors = [
  // Mothercare KW.
  'mckw_dev' => [
    'url' => 'https://alshaya-mckw-dev.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'c37e4ed2d937425db29385d08491d53a',
    'hmac_secret' => 'dZWSbz_TyTbyaJoBmIyNcA',
  ],
  'mckw_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'a4288d2bc3a14db08f790ce7842cb6b1',
    'hmac_secret' => 'NTA1NTcxM2UtMmY4Mi00',
    'api_version' => 'v2',
  ],
  'mckw_uat_v1' => [
    'url' => 'https://alshaya-mckw-uat.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'ec11fb2f54d34b2f9d35ec1d3575b89e',
    'hmac_secret' => 'gpW7PQFKKDU-qPrcIgaYNQ',
  ],
  'mckw_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '47f687a22e044b6ea273411e0af86d26',
    'hmac_secret' => 'NTZhY2Y4MDktN2NjNi00',
    'api_version' => 'v2',
  ],
  'mckw_pprod' => [
    'url' => 'https://alshaya-mckw-pprod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '676f2059d53d407791472c31783ae32c',
    'hmac_secret' => '-2Ok7ywndwcpsraYIIZ__w',
  ],
  'mckw_prod_v1' => [
    'url' => 'https://alshaya-mckw-prod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '3d136846d24040099a7eed6c1f4e80b9',
    'hmac_secret' => 'zUt1psyEWi5xO-glHlH_tw',
  ],
  'mckw_prod' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '389c52cfe70d4f81954ea7d751040eef',
    'hmac_secret' => 'OWQyY2NiODAtMmY2YS00',
    'api_version' => 'v2',
  ],
  // Mothercare SA.
  'mcsa_dev' => [
    'url' => 'https://alshaya-mcksa-dev.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '9575450fa913452ba46526463ad56edb',
    'hmac_secret' => '5pUmey7AhYgFhowkWpzEHA',
  ],
  'mcsa_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '57ec2ca3b0d54828a264ab529b7fab39',
    'hmac_secret' => 'M2I1YjMwZjctYzgzNS00',
    'api_version' => 'v2',
  ],
  'mcsa_uat_v1' => [
    'url' => 'https://alshaya-mcksa-uat.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '6686b9362f9c48789c08782dc0f85b59',
    'hmac_secret' => 'QIMU6nVtVUVJ3NtSErE5PA',
  ],
  'mcsa_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'd5cf13ff42204e4d940d0fe66987f025',
    'hmac_secret' => 'Zjk5ZjhkYzgtYmRmZC00',
    'api_version' => 'v2',
  ],
  'mcsa_pprod' => [
    'url' => 'https://alshaya-mcksa-pprod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '08bf3c91aeba4b18b78354b38e0ef566',
    'hmac_secret' => 'FY6YF9sOQzj2sTBS9hWFKA',
  ],
  'mcsa_prod_v1' => [
    'url' => 'https://alshaya-mcksa-prod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'd6b5bf8e0ea74347aaeb10dd5548c827',
    'hmac_secret' => 'nSAHBiZvuTiiJa-HC9xdnw',
  ],
  'mcsa_prod' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'da6d8048001a42dcae8fc9a38b2dd670',
    'hmac_secret' => 'ZjkxNzlmNmEtZWMzNy00',
    'api_version' => 'v2',
  ],
  // Mothercare UAE.
  'mcae_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '66764a4f8f9146aaab1352fb66897fad',
    'hmac_secret' => 'YWY2ZmY0MjItZmQ2OS00',
    'api_version' => 'v2',
  ],
  'mcae_uat_v1' => [
    'url' => 'https://alshaya-mcae-uat.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'c47e33167b1d4031a18acbf8c9fb9aa8',
    'hmac_secret' => 'QK1RFjIn1Subjk7m-1A2hw',
  ],
  'mcae_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '843bb58e8fff4c37bdfa17675497c80e',
    'hmac_secret' => 'NzYxMzYxNWYtYzc2MS00',
    'api_version' => 'v2',
  ],
  'mcae_prod_v1' => [
    'url' => 'https://alshaya-mcae-prod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'f755609b5006476e85acf942f54da3a0',
    'hmac_secret' => 'GOvi4TncWFS-kqU9H6p4vg',
  ],
  'mcae_prod' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'e58405c6ba4149b7a483be491aa047f0',
    'hmac_secret' => 'NDI1NTM0NWEtYjMxNS00',
    'api_version' => 'v2',
  ],
  // H&M KW.
  'hmkw_dev' => [
    'url' => 'https://alshaya-hm-dev.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '0a38353bdb364be589fc56ac45084778',
    'hmac_secret' => '8xl_BQ1bDa8jAcYZ8smX-A',
  ],
  'hmkw_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'deb9efe4753344059090b12fc10d387e',
    'hmac_secret' => 'NWE3ZDg1OGYtNmEwYi00',
    'api_version' => 'v2',
  ],
  'hmkw_uat' => [
    'url' => 'https://alshaya-hm-uat.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'b23acd31fa2543a0986d057194175312',
    'hmac_secret' => 'ADBateHrtvFQSrradB8CNg',
  ],
  'hmkw_pprod' => [
    'url' => 'https://alshaya-hm-pprod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'd57a1fdc153946afbc3b4b821f2029e1',
    'hmac_secret' => 'c0VSHfPxVXuJM0ioX_4thA',
  ],
  'hmkw_prod' => [
    'url' => 'https://alshaya-hm-prod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '3844f45d61d7467d9a671f3e1cf4bea8',
    'hmac_secret' => 'ngRQUE3QBP9zL-0-TC9XhQ',
  ],
  // H&M SA.
  'hmsa_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'd6e8ad54d1b74a9d8153028b42da4e38',
    'hmac_secret' => 'NDNkNDYzOGQtZjEyZC00',
    'api_version' => 'v2',
  ],
  'hmsa_uat' => [
    'url' => 'https://alshaya-hmsa-uat.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'c0c16680158b4caa91a0756e39f98d2f',
    'hmac_secret' => 'HmPdJJdF_UAqNlWYiWsmdw',
  ],
  'hmsa_prod' => [
    'url' => 'https://alshaya-hmsa-prod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '046a4e9c6ef8467da0e29ca8f3b1793f',
    'hmac_secret' => 'hGeJbwVRoDK-H336mE3BFg',
  ],
  // H&M AE.
  'hmae_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '48f2b6197df649f6bf5437312319ab5b',
    'hmac_secret' => 'ZDI1OTgyNDEtNzlkMS00',
    'api_version' => 'v2',
  ],
  'hmae_uat' => [
    'url' => 'https://alshaya-hmae-uat.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '01d9fafe1f5f4fc6b3d53e3badc61915',
    'hmac_secret' => 'iO1OtAiXLrxbDzLVEEJSrg',
  ],
  'hmae_prod' => [
    'url' => 'https://alshaya-hmae-prod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '7640a5cfcaef4efc815230419a0c5b06',
    'hmac_secret' => 'wOSnsIw9eRfSpkjOSaXTAw',
  ],
  // Pottery Barn KW.
  'pbkw_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'd6d10e56ce03474db0b7fcbdd20ca2ba',
    'hmac_secret' => 'ZWQwZjNiNTYtYTRkOC00',
    'api_version' => 'v2',
  ],
  'pbkw_dev2' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'cfe59305b0354659bb9ba1bf07361ad7',
    'hmac_secret' => 'ZjE2NjNiMWQtYTAwOS00',
    'api_version' => 'v2',
  ],
  'pbkw_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'cf6afd0bd1624636bd21f4581dc5e252',
    'hmac_secret' => 'NDBhYjQ3NjQtNDZmNy00',
    'api_version' => 'v2',
  ],
  'pbkw_prod' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'ceef8cc78b5b4097908e74298b408fc6',
    'hmac_secret' => 'MjBjZWZmODYtMWNhOS00',
    'api_version' => 'v2',
  ],
  // Pottery Barn SA.
  'pbsa_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '76f67886b7d34f3aad6ae85e75f93550',
    'hmac_secret' => 'NTI1ZjAwMTUtMzE5OS00',
    'api_version' => 'v2',
  ],
  'pbsa_dev2' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '2f619f435eb8427f9444492d65a0820d',
    'hmac_secret' => 'MzY5ZjA2MmMtMTQzYi00',
    'api_version' => 'v2',
  ],
  'pbsa_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'eb9126f28aaf466fab9494405fc6a4fe',
    'hmac_secret' => 'ZTdjNWY1ZTktYzMyMC00',
    'api_version' => 'v2',
  ],
  'pbsa_prod' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '2b0dca2d6ecc4b4caeb5c721cbdf3cf2',
    'hmac_secret' => 'N2M3NTMzMDAtOWRjZC00',
    'api_version' => 'v2',
  ],
  // Pottery Barn AE.
  'pbae_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '8e9bdd7df11e44c89ece0a5ac94dab2b',
    'hmac_secret' => 'NTdlYTc5OWUtMmEzNC00',
    'api_version' => 'v2',
  ],
  'pbae_dev2' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'd638da4e7c5545f9b15b302bcc77f049',
    'hmac_secret' => 'MGFhMmQyMTAtN2FkYS00',
    'api_version' => 'v2',
  ],
  'pbae_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'f3e36947161c4cb2bbe1448a890eb45c',
    'hmac_secret' => 'NmFlNDgwY2YtNzFjYS00',
    'api_version' => 'v2',
  ],
  'pbae_prod' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '1a48fcdf8a824328b70c98c7d33021ac',
    'hmac_secret' => 'NjhjMjRmNDEtNTJkMS00',
    'api_version' => 'v2',
  ],
  // BathBodyWorks KW.
  'bbwkw_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '0b92904888cd422f8acd2938e199916e',
    'hmac_secret' => 'YWFiMzI4M2QtMzY5ZC00',
    'api_version' => 'v2',
  ],
  'bbwkw_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '83c25968a20d44e7ba99d2d1ba72be4f',
    'hmac_secret' => 'MTM4ZWQ1YTktMjIwNC00',
    'api_version' => 'v2',
  ],
  'bbwkw_prod' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'be4a24ee6d54480d8bf3f56c80594808',
    'hmac_secret' => 'NGUzYTEwNTgtZDBjZC00',
    'api_version' => 'v2',
  ],
  // BathBodyWorks SA.
  'bbwsa_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '52fd839150734f1c8afe9db8c7f688e8',
    'hmac_secret' => 'ZTFjNTIzZTUtYzI3YS00',
    'api_version' => 'v2',
  ],
  'bbwsa_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '70407a4dde2b4373b64b05c2f3690d4c',
    'hmac_secret' => 'MTg3MzVkYTctNjU5ZC00',
    'api_version' => 'v2',
  ],
  'bbwsa_prod' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '622ed915907e428dafef41880e15e872',
    'hmac_secret' => 'YTJmNTA3NDAtYTNmOC00',
    'api_version' => 'v2',
  ],
  // BathBodyWorks AE.
  'bbwae_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '83a388d79f834d20bef67de19ee177d7',
    'hmac_secret' => 'M2MyOWQ3YjgtNTJiNy00',
    'api_version' => 'v2',
  ],
  'bbwae_uat_v1' => [
    'url' => 'https://alshaya-bbwae-uat.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '358452cd036a4c2a9a782eeb1ee8fb25',
    'hmac_secret' => 'm9DwvS0XwtPVpeiNLNk6hQ',
  ],
  'bbwae_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '6a3742c4234547a1ae827a0c402ab217',
    'hmac_secret' => 'ZjM3YzY3NWMtYTU3MC00',
    'api_version' => 'v2',
  ],
  'bbwae_prod' => [
    'url' => 'https://alshaya-bbwae-prod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '0272644d5be84efb9dd246478367e947',
    'hmac_secret' => 'a0EQYgy8zDRm6aNW7FyPrQ',
  ],
  'bbwae_prod_v2' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '84d4fe2e1c144f22a78f855a32acbcd9',
    'hmac_secret' => 'NzJmMDMyN2ItMzRhMC00',
    'api_version' => 'v2',
  ],
  // VictoriaSecret KW.
  'vskw_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'd00662613f144789831ebfe3d5885e37',
    'hmac_secret' => 'ZjI0NzBhNGUtMjM2Zi00',
    'api_version' => 'v2',
  ],
  'vskw_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '2b264eb0f00c4264b53b2004d94100ab',
    'hmac_secret' => 'NGVjNGEwYzEtMGEwOS00',
    'api_version' => 'v2',
  ],
  'vskw_prod' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '791ddf1c0052485ea3796ec3e9b97874',
    'hmac_secret' => 'ZGZkNzdiMmYtODEzZS00',
    'api_version' => 'v2',
  ],
  // VictoriaSecret SA.
  'vssa_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'b88bad1f106b439ea3a156bd2085e178',
    'hmac_secret' => 'Yjc1YTRjNTAtNDNkNi00',
    'api_version' => 'v2',
  ],
  'vssa_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'f7b9f553828249438d1b9c0173052e48',
    'hmac_secret' => 'MmVjMjI4NTItOTk5Ny00',
    'api_version' => 'v2',
  ],
  'vssa_prod' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '89937cd2ec984d1eb3ec2307d5558770',
    'hmac_secret' => 'YzczMzhiZTAtMGUzYS00',
    'api_version' => 'v2',
  ],
  // VictoriaSecret AE.
  'vsae_test' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'c938cdcc017b44bfb7bc736a0ab8a150',
    'hmac_secret' => 'MjM5Y2Q0OTMtMjczNy00',
    'api_version' => 'v2',
  ],
  'vsae_uat_v1' => [
    'url' => 'https://alshaya-vsae-uat.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '3ac23a0b214f45a1a756db7092d315dd',
    'hmac_secret' => 'RpbFWVOiVjltkXrTbmv2Ng',
  ],
  'vsae_uat' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '56b8b235bf854030ba2ae71473cf28fd',
    'hmac_secret' => 'ZmU2ZTE0MTUtNGY4MC00',
    'api_version' => 'v2',
  ],
  'vsae_prod' => [
    'url' => 'https://alshaya-vsae-prod.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => '0272644d5be84efb9dd246478367e947',
    'hmac_secret' => 'a0EQYgy8zDRm6aNW7FyPrQ',
  ],
  'vsae_prod_v2' => [
    'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
    'hmac_id' => 'e70b2650aef74d06bb31c163870c212b',
    'hmac_secret' => 'OGRkMjY3MGYtNDExZS00',
    'api_version' => 'v2',
  ],
];

$conductor_settings_file = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . 'settings/conductor.php';
if (file_exists($conductor_settings_file)) {
  require_once $conductor_settings_file;
}
