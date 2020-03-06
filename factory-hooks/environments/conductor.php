<?php
// @codingStandardsIgnoreFile

/**
 * List all known Conductor environments keyed by environment machine name.
 */

global $conductors;

$conductors = [
  // Mothercare KW.
  'mckw_dev' => [
    'hmac_id' => '1c981b44daae428fae5fb6b2a3812214',
    'hmac_secret' => 'N2Y0MDEzMWEtZGE4Ny00',
    // We add site id here to be able to invoke organization level ACM APIs.
    // For now we have a utility script to pause ACM queues which uses this.
    // We could have more scripts using this value.
    'site_id' => 152,
  ],
  'mckw_qa' => [
    'hmac_id' => 'a4288d2bc3a14db08f790ce7842cb6b1',
    'hmac_secret' => 'NTA1NTcxM2UtMmY4Mi00',
    'site_id' => 4,
  ],
  'mckw_dev2' => [
    'hmac_id' => '65769d9ba82c4ffd8c26ed830de1e6c2',
    'hmac_secret' => 'Y2MyMTVlYTQtMGQ0Yi00',
    'site_id' => 133,
  ],
  'mckw_uat' => [
    'hmac_id' => '47f687a22e044b6ea273411e0af86d26',
    'hmac_secret' => 'NTZhY2Y4MDktN2NjNi00',
    'site_id' => 34,
  ],
  'mckw_prod' => [
    'hmac_id' => '389c52cfe70d4f81954ea7d751040eef',
    'hmac_secret' => 'OWQyY2NiODAtMmY2YS00',
    'site_id' => 88,
  ],
  // Mothercare SA.
  'mcsa_dev' => [
    'hmac_id' => '0a161c94a6f74716b9f6f72ebf0eff0e',
    'hmac_secret' => 'Zjg1NmM0NzctODhiYy00',
    'site_id' => 138,
  ],
  'mcsa_qa' => [
    'hmac_id' => '57ec2ca3b0d54828a264ab529b7fab39',
    'hmac_secret' => 'M2I1YjMwZjctYzgzNS00',
    'site_id' => 5,
  ],
  'mcsa_uat' => [
    'hmac_id' => 'd5cf13ff42204e4d940d0fe66987f025',
    'hmac_secret' => 'Zjk5ZjhkYzgtYmRmZC00',
    'site_id' => 35,
  ],
  'mcsa_prod' => [
    'hmac_id' => 'da6d8048001a42dcae8fc9a38b2dd670',
    'hmac_secret' => 'ZjkxNzlmNmEtZWMzNy00',
    'site_id' => 89,
  ],
  // Mothercare UAE.
  'mcae_qa' => [
    'hmac_id' => '66764a4f8f9146aaab1352fb66897fad',
    'hmac_secret' => 'YWY2ZmY0MjItZmQ2OS00',
    'site_id' => 6,
  ],
  'mcae_uat' => [
    'hmac_id' => '843bb58e8fff4c37bdfa17675497c80e',
    'hmac_secret' => 'NzYxMzYxNWYtYzc2MS00',
    'site_id' => 36,
  ],
  'mcae_prod' => [
    'hmac_id' => 'e58405c6ba4149b7a483be491aa047f0',
    'hmac_secret' => 'NDI1NTM0NWEtYjMxNS00',
    'site_id' => 90,
  ],
  // H&M KW.
  'hmkw_dev' => [
    'hmac_id' => '03551dee94ad4f91a20bf41459c02bdd',
    'hmac_secret' => 'MWI3NDdjYWQtNGUwMS00',
    'site_id' => 135,
  ],
  'hmkw_dev2' => [
    'hmac_id' => '815274258217410ba58228b5baf9087f',
    'hmac_secret' => 'NDhhYjVhMjAtYjYxNi00',
    'site_id' => 187,
  ],
  'hmkw_qa' => [
    'hmac_id' => 'deb9efe4753344059090b12fc10d387e',
    'hmac_secret' => 'NWE3ZDg1OGYtNmEwYi00',
    'site_id' => 19,
  ],
  'hmkw_uat' => [
    'hmac_id' => '8dc1393f0b12493bbd1a8fa0266db8aa',
    'hmac_secret' => 'NmViNWNjYTgtMTI1ZS00',
    'site_id' => 95,
  ],
  'hmkw_prod' => [
    'hmac_id' => 'f343b7daa231462e82368ab7df3217ff',
    'hmac_secret' => 'YzJiNGQzYWItYzk0Yy00',
    'site_id' => 98,
  ],
  // H&M SA.
  'hmsa_dev2' => [
    'hmac_id' => '9b56f8eedf724a7d86b5684f997611ca',
    'hmac_secret' => 'NmQ5NmI1NjYtZjc0NC00',
    'site_id' => 188,
  ],
  'hmsa_qa' => [
    'hmac_id' => 'd6e8ad54d1b74a9d8153028b42da4e38',
    'hmac_secret' => 'NDNkNDYzOGQtZjEyZC00',
    'site_id' => 20,
  ],
  'hmsa_uat' => [
    'hmac_id' => 'e21aeeab15154ebf90dfc0268cadae58',
    'hmac_secret' => 'ZmU3MDBmMzctMTM0Yy00',
    'site_id' => 96,
  ],
  'hmsa_prod' => [
    'hmac_id' => 'c3334e9728bc4d46a999a31aaf0f3bad',
    'hmac_secret' => 'NTFkM2E4ZmMtZWE5MS00',
    'site_id' => 99,
  ],
  // H&M AE.
  'hmae_dev2' => [
    'hmac_id' => 'c2ab0f35710247ef9a979c979739aa63',
    'hmac_secret' => 'OTdjODZhYjAtMzU0YS00',
    'site_id' => 189,
  ],
  'hmae_qa' => [
    'hmac_id' => '48f2b6197df649f6bf5437312319ab5b',
    'hmac_secret' => 'ZDI1OTgyNDEtNzlkMS00',
    'site_id' => 21,
  ],
  'hmae_uat' => [
    'hmac_id' => 'b566a23145e447029edef7c574782370',
    'hmac_secret' => 'MzY3ZDg1NDEtZTlmYy00',
    'site_id' => 97,
  ],
  'hmae_prod' => [
    'hmac_id' => '31a535578a434459a044b5dda6e6b735',
    'hmac_secret' => 'MWYxYTA0NmItMjM1Yy00',
    'site_id' => 100,
  ],
  // H&M EG.
  'hmeg_dev2' => [
    'hmac_id' => '73ea7dfcd2614c7ea48145caf958ca05',
    'hmac_secret' => 'NTJmNzUwNDItNjY2Ny00',
    'site_id' => 190,
  ],
  'hmeg_qa' => [
    'hmac_id' => '34780fdeb07441048af8fb931a75f788',
    'hmac_secret' => 'YjM1OWNiNTQtYmMzYy00',
    'site_id' => 94,
  ],
  'hmeg_uat' => [
    'hmac_id' => 'cd34bf1fd5c048df9b21fb80c0d9c392',
    'hmac_secret' => 'Njk4NjYxZDItMmIzYS00',
    'site_id' => 104,
  ],
  'hmeg_prod' => [
    'hmac_id' => 'f96e2ac1dc7d4de09d162f6fe816823e',
    'hmac_secret' => 'OWI0YzNmMjMtNmNkOS00',
    'site_id' => 145,
  ],
  // Pottery Barn KW.
  'pbkw_dev2' => [
    'hmac_id' => 'ec5997a86c504abb882545513877c234',
    'hmac_secret' => 'ODQ1MWZlNjQtNTczYS00',
    'site_id' => 203,
  ],
  'pbkw_qa' => [
    'hmac_id' => 'd6d10e56ce03474db0b7fcbdd20ca2ba',
    'hmac_secret' => 'ZWQwZjNiNTYtYTRkOC00',
    'site_id' => 31,
  ],
  'pbkw_uat' => [
    'hmac_id' => 'cf6afd0bd1624636bd21f4581dc5e252',
    'hmac_secret' => 'NDBhYjQ3NjQtNDZmNy00',
    'site_id' => 40,
  ],
  'pbkw_prod' => [
    'hmac_id' => 'ceef8cc78b5b4097908e74298b408fc6',
    'hmac_secret' => 'MjBjZWZmODYtMWNhOS00',
    'site_id' => 55,
  ],
  // Pottery Barn SA.
  'pbsa_qa' => [
    'hmac_id' => '76f67886b7d34f3aad6ae85e75f93550',
    'hmac_secret' => 'NTI1ZjAwMTUtMzE5OS00',
    'site_id' => 32,
  ],
  'pbsa_uat' => [
    'hmac_id' => 'eb9126f28aaf466fab9494405fc6a4fe',
    'hmac_secret' => 'ZTdjNWY1ZTktYzMyMC00',
    'site_id' => 41,
  ],
  'pbsa_prod' => [
    'hmac_id' => '2b0dca2d6ecc4b4caeb5c721cbdf3cf2',
    'hmac_secret' => 'N2M3NTMzMDAtOWRjZC00',
    'site_id' => 56,
  ],
  // Pottery Barn AE.
  'pbae_dev' => [
    'hmac_id' => 'a22d85c970484b17acb2511b4181aa81',
    'hmac_secret' => 'ODczNzNiNGItNmQwMy00',
    'site_id' => 139,
  ],
  'pbae_dev2' => [
    'hmac_id' => 'c1d103b9b2194fa0ba957981e566c0ce',
    'hmac_secret' => 'YWFmOTJmODctYmMwNS00',
    'site_id' => 148,
  ],
  'pbae_qa' => [
    'hmac_id' => '8e9bdd7df11e44c89ece0a5ac94dab2b',
    'hmac_secret' => 'NTdlYTc5OWUtMmEzNC00',
    'site_id' => 33,
  ],
  'pbae_uat' => [
    'hmac_id' => 'f3e36947161c4cb2bbe1448a890eb45c',
    'hmac_secret' => 'NmFlNDgwY2YtNzFjYS00',
    'site_id' => 42,
  ],
  'pbae_prod' => [
    'hmac_id' => '1a48fcdf8a824328b70c98c7d33021ac',
    'hmac_secret' => 'NjhjMjRmNDEtNTJkMS00',
    'site_id' => 57,
  ],
  // BathBodyWorks KW.
  'bbwkw_qa' => [
    'hmac_id' => '0b92904888cd422f8acd2938e199916e',
    'hmac_secret' => 'YWFiMzI4M2QtMzY5ZC00',
    'site_id' => 23,
  ],
  'bbwkw_uat' => [
    'hmac_id' => '83c25968a20d44e7ba99d2d1ba72be4f',
    'hmac_secret' => 'MTM4ZWQ1YTktMjIwNC00',
    'site_id' => 52,
  ],
  'bbwkw_prod' => [
    'hmac_id' => 'be4a24ee6d54480d8bf3f56c80594808',
    'hmac_secret' => 'NGUzYTEwNTgtZDBjZC00',
    'site_id' => 85,
  ],
  // BathBodyWorks SA.
  'bbwsa_dev2' => [
    'hmac_id' => 'b24ef03c73df4b6cac6500eabae86119',
    'hmac_secret' => 'YjBjYTdmN2YtOGE2Ni00',
  ],
  'bbwsa_qa' => [
    'hmac_id' => '52fd839150734f1c8afe9db8c7f688e8',
    'hmac_secret' => 'ZTFjNTIzZTUtYzI3YS00',
    'site_id' => 24,
  ],
  'bbwsa_uat' => [
    'hmac_id' => '70407a4dde2b4373b64b05c2f3690d4c',
    'hmac_secret' => 'MTg3MzVkYTctNjU5ZC00',
    'site_id' => 53,
  ],
  'bbwsa_prod' => [
    'hmac_id' => '622ed915907e428dafef41880e15e872',
    'hmac_secret' => 'YTJmNTA3NDAtYTNmOC00',
    'site_id' => 86,
  ],
  // BathBodyWorks AE.
  'bbwae_dev' => [
    'hmac_id' => 'c87b77ede1b14ac680b1ee0cd156a26f',
    'hmac_secret' => 'OTk3YzBlN2UtOWM0Ny00',
    'site_id' => 132,
  ],
  'bbwae_dev2' => [
    'hmac_id' => 'aeffc79b7568410aafe7cbb8aaf83969',
    'hmac_secret' => 'ZjdjZGU3MGMtYzZiMi00',
    'site_id' => 202,
  ],
  'bbwae_qa' => [
    'hmac_id' => '83a388d79f834d20bef67de19ee177d7',
    'hmac_secret' => 'M2MyOWQ3YjgtNTJiNy00',
    'site_id' => 25,
  ],
  'bbwae_uat' => [
    'hmac_id' => '6a3742c4234547a1ae827a0c402ab217',
    'hmac_secret' => 'ZjM3YzY3NWMtYTU3MC00',
    'site_id' => 54,
  ],
  'bbwae_prod' => [
    'hmac_id' => '84d4fe2e1c144f22a78f855a32acbcd9',
    'hmac_secret' => 'NzJmMDMyN2ItMzRhMC00',
    'site_id' => 87,
  ],
  // VictoriaSecret KW.
  'vskw_qa' => [
    'hmac_id' => 'd00662613f144789831ebfe3d5885e37',
    'hmac_secret' => 'ZjI0NzBhNGUtMjM2Zi00',
    'site_id' => 26,
  ],
  'vskw_uat' => [
    'hmac_id' => '2b264eb0f00c4264b53b2004d94100ab',
    'hmac_secret' => 'NGVjNGEwYzEtMGEwOS00',
    'site_id' => 66,
  ],
  'vskw_prod' => [
    'hmac_id' => '791ddf1c0052485ea3796ec3e9b97874',
    'hmac_secret' => 'ZGZkNzdiMmYtODEzZS00',
    'site_id' => 82,
  ],
  // VictoriaSecret SA.
  'vssa_qa' => [
    'hmac_id' => 'b88bad1f106b439ea3a156bd2085e178',
    'hmac_secret' => 'Yjc1YTRjNTAtNDNkNi00',
    'site_id' => 27,
  ],
  'vssa_uat' => [
    'hmac_id' => 'f7b9f553828249438d1b9c0173052e48',
    'hmac_secret' => 'MmVjMjI4NTItOTk5Ny00',
    'site_id' => 67,
  ],
  'vssa_prod' => [
    'hmac_id' => '89937cd2ec984d1eb3ec2307d5558770',
    'hmac_secret' => 'YzczMzhiZTAtMGUzYS00',
    'site_id' => 83,
  ],
  // VictoriaSecret AE.
  'vsae_dev2' => [
    'hmac_id' => '235f0ce579f6439c97e928b0ffa37224',
    'hmac_secret' => 'MzU0ZWQ5YWYtNjYwNi00',
    'site_id' => 147,
  ],
  'vsae_dev' => [
    'hmac_id' => 'fcf0bc0e491a4d5d8c615973f4cd91ec',
    'hmac_secret' => 'NWJmNjRlODItN2JkZi00',
    'site_id' => 140,
  ],
  'vsae_qa' => [
    'hmac_id' => 'c938cdcc017b44bfb7bc736a0ab8a150',
    'hmac_secret' => 'MjM5Y2Q0OTMtMjczNy00',
    'site_id' => 28,
  ],
  'vsae_uat' => [
    'hmac_id' => '56b8b235bf854030ba2ae71473cf28fd',
    'hmac_secret' => 'ZmU2ZTE0MTUtNGY4MC00',
    'site_id' => 68,
  ],
  'vsae_prod' => [
    'hmac_id' => 'e70b2650aef74d06bb31c163870c212b',
    'hmac_secret' => 'OGRkMjY3MGYtNDExZS00',
    'site_id' => 84,
  ],
  // FootLocker KW.
  'flkw_qa' => [
    'hmac_id' => '5532a53571c74e65b5b0f01e3cf2e791',
    'hmac_secret' => 'YjQ3M2M0ODktNjg3MS00',
    'site_id' => 63,
  ],
  'flkw_uat' => [
    'hmac_id' => 'b7850578d9864538a51eb94bc9632487',
    'hmac_secret' => 'MjU3YjExNDYtYzFmMy00',
    'site_id' => 91,
  ],
  'flkw_prod' => [
    'hmac_id' => '4cc5c57ce5b5413f98fe0661158e4e99',
    'hmac_secret' => 'M2U3NWQxYjAtMDE4Zi00',
    'site_id' => 127,
  ],
  // FootLocker SA.
  'flsa_dev' => [
    'hmac_id' => '1a622d271f7946198e14ceb7eab616b9',
    'hmac_secret' => 'YWNlMTJlOTEtMTg3ZS00',
    'site_id' => 141,
  ],
  'flsa_dev2' => [
    'hmac_id' => 'd551d3b4747440118394bc5ce8c51307',
    'hmac_secret' => 'MDI0NzkxOGQtMzNjYi00',
    'site_id' => 142,
  ],
  'flsa_qa' => [
    'hmac_id' => '31a27139b98448379c08c90bee5e866e',
    'hmac_secret' => 'Njk0ZDgyMTgtNTc3Mi00',
    'site_id' => 64,
  ],
  'flsa_uat' => [
    'hmac_id' => 'b8371f86a2e548ecbe3f7f7fc556596c',
    'hmac_secret' => 'M2RlMTc3YTgtZDA4OS00',
    'site_id' => 92,
  ],
  'flsa_prod' => [
    'hmac_id' => 'e5e05dd4da47426e9da0e9d3cad04ed5',
    'hmac_secret' => 'M2I4YzllMWItZDA3Zi00',
    'site_id' => 128,
  ],
  // FootLocker AE.
  'flae_qa' => [
    'hmac_id' => '61f09f6fe1474ce68cc8057f97188d2f',
    'hmac_secret' => 'MjU3YzZiOGEtNmI1Yi00',
    'site_id' => 65,
  ],
  'flae_uat' => [
    'hmac_id' => '8d8c55c242f947efa44de57bbd84155e',
    'hmac_secret' => 'YWFlYTc2ZDgtYmJjYS00',
    'site_id' => 93,
  ],
  'flae_prod' => [
    'hmac_id' => 'a220ff2d59e846c79426e5b001516063',
    'hmac_secret' => 'ZmJiNzUzOTctMTBiMC00',
    'site_id' => 129,
  ],
  // West Elm.
  'wekw_qa' => [
    'hmac_id' => '2546a170c84c49248207de3a8665a920',
    'hmac_secret' => 'N2Q4ZjhlY2YtMTA1Yy00',
    'site_id' => 165,
  ],
  'wesa_qa' => [
    'hmac_id' => 'f8415a9cef1d46aeb0ffeafb5937682f',
    'hmac_secret' => 'ZjI2YjYzZWItZDYyZi00',
    'site_id' => 166,
  ],
  'weae_qa' => [
    'hmac_id' => '868bd73816b6425987a7d43b96c0ed84',
    'hmac_secret' => 'OGY5ODFiM2MtYTk0Mi00',
    'site_id' => 167,
  ],
  'weae_dev2' => [
    'hmac_id' => '82e9dce9da1e4cc7a25a61658f359576',
    'hmac_secret' => 'YzI0ZjNmNzUtMzgyNS00',
    'site_id' => 159,
  ],
  'wekw_uat' => [
    'hmac_id' => '4d673878644c45a0908049f5e26be993',
    'hmac_secret' => 'NDIzZDBkOTktZGJiNi00',
    'site_id' => 171,
  ],
  'wesa_uat' => [
    'hmac_id' => '0d0a24af21ef4399b7dc5b3eae90bd69',
    'hmac_secret' => 'NjgyMDA1MDMtYzUxOC00',
    'site_id' => 172,
  ],
  'weae_uat' => [
    'hmac_id' => 'cb7192b7d39840f8b72aa5393c6034fe',
    'hmac_secret' => 'NzI5M2M1MjYtMTE5ZS00',
    'site_id' => 173,
  ],
  'wekw_prod' => [
    'site_id' => 183,
  ],
  'wesa_prod' => [
    'site_id' => 184,
  ],
  'weae_prod' => [
    'site_id' => 185,
  ],
  'weeg_prod' => [
    'site_id' => 186,
  ],
  // American Eagle Outfitters.
  'aeokw_dev' => [
    'hmac_id' => '9c9e527db24446d98cde69829ffde832',
    'hmac_secret' => 'N2QzZGUxMzEtZTNjYy00',
    'site_id' => 178,
  ],
  'aeokw_qa' => [
    'hmac_id' => 'e6c4af7406bb4694a0caa108409617d9',
    'hmac_secret' => 'OGU4Yjg1ZDMtNWI5NS00',
    'site_id' => 174,
  ],
  'aeosa_qa' => [
    'hmac_id' => 'e6b1671eda3b442f9119045676199d93',
    'hmac_secret' => 'NTg3N2Q3NWItMjJhOC00',
    'site_id' => 175,
  ],
  'aeoae_qa' => [
    'hmac_id' => '89e42750b2764506b5533ed781d49e87',
    'hmac_secret' => 'MmYxZGQ0NDYtYTQ5MC00',
    'site_id' => 176,
  ],
  'aeoeg_qa' => [
    'hmac_id' => '5e49e396d2ec416f9216a3a287f738fc',
    'hmac_secret' => 'YzdkOTIyYjItNzc3ZS00',
    'site_id' => 177,
  ],
  'aeokw_uat' => [
    'hmac_id' => 'afb8c24dfb574debaebfe6b680a9fc43',
    'hmac_secret' => 'MjU1NmIzMjYtNDc1My00',
    'site_id' => 191,
  ],
  'aeosa_uat' => [
    'hmac_id' => '8d9331c4d4aa4a529ed05055b3ca5e67',
    'hmac_secret' => 'ZGJmMWFiYTktY2JiNS00',
    'site_id' => 192,
  ],
  'aeoae_uat' => [
    'hmac_id' => '05168cbe2e664f4c9d9b676a96d185a4',
    'hmac_secret' => 'YmJmZTAxNzgtMTdhNy00',
    'site_id' => 193,
  ],
  'aeoeg_uat' => [
    'hmac_id' => 'a37e371caae3434ea9372ea053607857',
    'hmac_secret' => 'ZDFmOGM0NDEtNjlmNy00',
    'site_id' => 194,
  ],
];

// Default values for each ACM middleware.
$default_values = [
  'url' => 'https://api.eu-west-1.prod.acm.acquia.io/',
  'api_version' => 'v2',
];

// If key available in specific `ACM` instance and as well in $default_values,
// then the `ACM` keys will take precedence over $default_values keys.
foreach ($conductors as $key => $conductor) {
  $conductors[$key] = array_merge($default_values, $conductor);
}
