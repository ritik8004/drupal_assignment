<?php
// @codingStandardsIgnoreFile

/**
 * List all known Conductor environments keyed by environment machine name.
 */

global $conductors;

$conductors = [
  // Mothercare KW.
  'mckw_qa' => [
    'hmac_id' => 'a4288d2bc3a14db08f790ce7842cb6b1',
    'hmac_secret' => 'NTA1NTcxM2UtMmY4Mi00',
  ],
  'mckw_uat' => [
    'hmac_id' => '47f687a22e044b6ea273411e0af86d26',
    'hmac_secret' => 'NTZhY2Y4MDktN2NjNi00',
  ],
  'mckw_prod' => [
    'hmac_id' => '389c52cfe70d4f81954ea7d751040eef',
    'hmac_secret' => 'OWQyY2NiODAtMmY2YS00',
  ],
  // Mothercare SA.
  'mcsa_dev' => [
    'hmac_id' => '0a161c94a6f74716b9f6f72ebf0eff0e',
    'hmac_secret' => 'Zjg1NmM0NzctODhiYy00',
  ],
  'mcsa_qa' => [
    'hmac_id' => '57ec2ca3b0d54828a264ab529b7fab39',
    'hmac_secret' => 'M2I1YjMwZjctYzgzNS00',
  ],
  'mcsa_uat' => [
    'hmac_id' => 'd5cf13ff42204e4d940d0fe66987f025',
    'hmac_secret' => 'Zjk5ZjhkYzgtYmRmZC00',
  ],
  'mcsa_prod' => [
    'hmac_id' => 'da6d8048001a42dcae8fc9a38b2dd670',
    'hmac_secret' => 'ZjkxNzlmNmEtZWMzNy00',
  ],
  // Mothercare UAE.
  'mcae_qa' => [
    'hmac_id' => '66764a4f8f9146aaab1352fb66897fad',
    'hmac_secret' => 'YWY2ZmY0MjItZmQ2OS00',
  ],
  'mcae_uat' => [
    'hmac_id' => '843bb58e8fff4c37bdfa17675497c80e',
    'hmac_secret' => 'NzYxMzYxNWYtYzc2MS00',
  ],
  'mcae_prod' => [
    'hmac_id' => 'e58405c6ba4149b7a483be491aa047f0',
    'hmac_secret' => 'NDI1NTM0NWEtYjMxNS00',
  ],
  // H&M KW.
  'hmkw_dev' => [
    'hmac_id' => '03551dee94ad4f91a20bf41459c02bdd',
    'hmac_secret' => 'MWI3NDdjYWQtNGUwMS00',
  ],
  'hmkw_qa' => [
    'hmac_id' => 'deb9efe4753344059090b12fc10d387e',
    'hmac_secret' => 'NWE3ZDg1OGYtNmEwYi00',
  ],
  'hmkw_uat' => [
    'hmac_id' => '8dc1393f0b12493bbd1a8fa0266db8aa',
    'hmac_secret' => 'NmViNWNjYTgtMTI1ZS00',
  ],
  'hmkw_prod' => [
    'hmac_id' => 'f343b7daa231462e82368ab7df3217ff',
    'hmac_secret' => 'YzJiNGQzYWItYzk0Yy00',
  ],
  'hmkw_mapp' => [
    'hmac_id' => 'd42e1e4ee7354200891b4e13fcd5a06b',
    'hmac_secret' => 'ZGMzYmYzMWYtNDAyOS00',
  ],
  // H&M SA.
  'hmsa_qa' => [
    'hmac_id' => 'd6e8ad54d1b74a9d8153028b42da4e38',
    'hmac_secret' => 'NDNkNDYzOGQtZjEyZC00',
  ],
  'hmsa_uat' => [
    'hmac_id' => 'e21aeeab15154ebf90dfc0268cadae58',
    'hmac_secret' => 'ZmU3MDBmMzctMTM0Yy00',
  ],
  'hmsa_prod' => [
    'hmac_id' => 'c3334e9728bc4d46a999a31aaf0f3bad',
    'hmac_secret' => 'NTFkM2E4ZmMtZWE5MS00',
  ],
  'hmsa_mapp' => [
    'hmac_id' => '3fede9458f664760bdcf74e136af78df',
    'hmac_secret' => 'OGE2ZTUzZjItMjE0YS00',
  ],
  // H&M AE.
  'hmae_qa' => [
    'hmac_id' => '48f2b6197df649f6bf5437312319ab5b',
    'hmac_secret' => 'ZDI1OTgyNDEtNzlkMS00',
  ],
  'hmae_uat' => [
    'hmac_id' => 'b566a23145e447029edef7c574782370',
    'hmac_secret' => 'MzY3ZDg1NDEtZTlmYy00',
  ],
  'hmae_prod' => [
    'hmac_id' => '31a535578a434459a044b5dda6e6b735',
    'hmac_secret' => 'MWYxYTA0NmItMjM1Yy00',
  ],
  'hmae_mapp' => [
    'hmac_id' => '57331ba72f7a45a1823e38c69d0aab44',
    'hmac_secret' => 'YjMyYzdkMjMtMzk3Mi00',
  ],
  // H&M EG.
  'hmeg_qa' => [
    'hmac_id' => '34780fdeb07441048af8fb931a75f788',
    'hmac_secret' => 'YjM1OWNiNTQtYmMzYy00',
  ],
  'hmeg_uat' => [
    'hmac_id' => 'cd34bf1fd5c048df9b21fb80c0d9c392',
    'hmac_secret' => 'Njk4NjYxZDItMmIzYS00',
  ],
  // Pottery Barn KW.
  'pbkw_qa' => [
    'hmac_id' => 'd6d10e56ce03474db0b7fcbdd20ca2ba',
    'hmac_secret' => 'ZWQwZjNiNTYtYTRkOC00',
  ],
  'pbkw_uat' => [
    'hmac_id' => 'cf6afd0bd1624636bd21f4581dc5e252',
    'hmac_secret' => 'NDBhYjQ3NjQtNDZmNy00',
  ],
  'pbkw_prod' => [
    'hmac_id' => 'ceef8cc78b5b4097908e74298b408fc6',
    'hmac_secret' => 'MjBjZWZmODYtMWNhOS00',
  ],
  // Pottery Barn SA.
  'pbsa_qa' => [
    'hmac_id' => '76f67886b7d34f3aad6ae85e75f93550',
    'hmac_secret' => 'NTI1ZjAwMTUtMzE5OS00',
  ],
  'pbsa_uat' => [
    'hmac_id' => 'eb9126f28aaf466fab9494405fc6a4fe',
    'hmac_secret' => 'ZTdjNWY1ZTktYzMyMC00',
  ],
  'pbsa_prod' => [
    'hmac_id' => '2b0dca2d6ecc4b4caeb5c721cbdf3cf2',
    'hmac_secret' => 'N2M3NTMzMDAtOWRjZC00',
  ],
  // Pottery Barn AE.
  'pbae_dev' => [
    'hmac_id' => 'a22d85c970484b17acb2511b4181aa81',
    'hmac_secret' => 'ODczNzNiNGItNmQwMy00',
  ],
  'pbae_qa' => [
    'hmac_id' => '8e9bdd7df11e44c89ece0a5ac94dab2b',
    'hmac_secret' => 'NTdlYTc5OWUtMmEzNC00',
  ],
  'pbae_uat' => [
    'hmac_id' => 'f3e36947161c4cb2bbe1448a890eb45c',
    'hmac_secret' => 'NmFlNDgwY2YtNzFjYS00',
  ],
  'pbae_prod' => [
    'hmac_id' => '1a48fcdf8a824328b70c98c7d33021ac',
    'hmac_secret' => 'NjhjMjRmNDEtNTJkMS00',
  ],
  // BathBodyWorks KW.
  'bbwkw_qa' => [
    'hmac_id' => '0b92904888cd422f8acd2938e199916e',
    'hmac_secret' => 'YWFiMzI4M2QtMzY5ZC00',
  ],
  'bbwkw_uat' => [
    'hmac_id' => '83c25968a20d44e7ba99d2d1ba72be4f',
    'hmac_secret' => 'MTM4ZWQ1YTktMjIwNC00',
  ],
  'bbwkw_prod' => [
    'hmac_id' => 'be4a24ee6d54480d8bf3f56c80594808',
    'hmac_secret' => 'NGUzYTEwNTgtZDBjZC00',
  ],
  // BathBodyWorks SA.
  'bbwsa_qa' => [
    'hmac_id' => '52fd839150734f1c8afe9db8c7f688e8',
    'hmac_secret' => 'ZTFjNTIzZTUtYzI3YS00',
  ],
  'bbwsa_uat' => [
    'hmac_id' => '70407a4dde2b4373b64b05c2f3690d4c',
    'hmac_secret' => 'MTg3MzVkYTctNjU5ZC00',
  ],
  'bbwsa_prod' => [
    'hmac_id' => '622ed915907e428dafef41880e15e872',
    'hmac_secret' => 'YTJmNTA3NDAtYTNmOC00',
  ],
  // BathBodyWorks AE.
  'bbwae_dev' => [
    'hmac_id' => 'c87b77ede1b14ac680b1ee0cd156a26f',
    'hmac_secret' => 'OTk3YzBlN2UtOWM0Ny00',
  ],
  'bbwae_qa' => [
    'hmac_id' => '83a388d79f834d20bef67de19ee177d7',
    'hmac_secret' => 'M2MyOWQ3YjgtNTJiNy00',
  ],
  'bbwae_uat' => [
    'hmac_id' => '6a3742c4234547a1ae827a0c402ab217',
    'hmac_secret' => 'ZjM3YzY3NWMtYTU3MC00',
  ],
  'bbwae_prod' => [
    'hmac_id' => '84d4fe2e1c144f22a78f855a32acbcd9',
    'hmac_secret' => 'NzJmMDMyN2ItMzRhMC00',
  ],
  // VictoriaSecret KW.
  'vskw_qa' => [
    'hmac_id' => 'd00662613f144789831ebfe3d5885e37',
    'hmac_secret' => 'ZjI0NzBhNGUtMjM2Zi00',
  ],
  'vskw_uat' => [
    'hmac_id' => '2b264eb0f00c4264b53b2004d94100ab',
    'hmac_secret' => 'NGVjNGEwYzEtMGEwOS00',
  ],
  'vskw_prod' => [
    'hmac_id' => '791ddf1c0052485ea3796ec3e9b97874',
    'hmac_secret' => 'ZGZkNzdiMmYtODEzZS00',
  ],
  // VictoriaSecret SA.
  'vssa_qa' => [
    'hmac_id' => 'b88bad1f106b439ea3a156bd2085e178',
    'hmac_secret' => 'Yjc1YTRjNTAtNDNkNi00',
  ],
  'vssa_uat' => [
    'hmac_id' => 'f7b9f553828249438d1b9c0173052e48',
    'hmac_secret' => 'MmVjMjI4NTItOTk5Ny00',
  ],
  'vssa_prod' => [
    'hmac_id' => '89937cd2ec984d1eb3ec2307d5558770',
    'hmac_secret' => 'YzczMzhiZTAtMGUzYS00',
  ],
  // VictoriaSecret AE.
  'vsae_dev' => [
    'hmac_id' => 'fcf0bc0e491a4d5d8c615973f4cd91ec',
    'hmac_secret' => 'NWJmNjRlODItN2JkZi00',
  ],
  'vsae_qa' => [
    'hmac_id' => 'c938cdcc017b44bfb7bc736a0ab8a150',
    'hmac_secret' => 'MjM5Y2Q0OTMtMjczNy00',
  ],
  'vsae_uat' => [
    'hmac_id' => '56b8b235bf854030ba2ae71473cf28fd',
    'hmac_secret' => 'ZmU2ZTE0MTUtNGY4MC00',
  ],
  'vsae_prod' => [
    'hmac_id' => 'e70b2650aef74d06bb31c163870c212b',
    'hmac_secret' => 'OGRkMjY3MGYtNDExZS00',
  ],
  // FootLocker KW.
  'flkw_qa' => [
    'hmac_id' => '5532a53571c74e65b5b0f01e3cf2e791',
    'hmac_secret' => 'YjQ3M2M0ODktNjg3MS00',
  ],
  'flkw_uat' => [
    'hmac_id' => 'b7850578d9864538a51eb94bc9632487',
    'hmac_secret' => 'MjU3YjExNDYtYzFmMy00',
  ],
  'flkw_prod' => [
    'hmac_id' => '4cc5c57ce5b5413f98fe0661158e4e99',
    'hmac_secret' => 'M2U3NWQxYjAtMDE4Zi00',
  ],
  // FootLocker SA.
  'flsa_dev' => [
    'hmac_id' => '1a622d271f7946198e14ceb7eab616b9',
    'hmac_secret' => 'YWNlMTJlOTEtMTg3ZS00',
  ],
  'flsa_qa' => [
    'hmac_id' => '31a27139b98448379c08c90bee5e866e',
    'hmac_secret' => 'Njk0ZDgyMTgtNTc3Mi00',
  ],
  'flsa_uat' => [
    'hmac_id' => 'b8371f86a2e548ecbe3f7f7fc556596c',
    'hmac_secret' => 'M2RlMTc3YTgtZDA4OS00',
  ],
  'flsa_prod' => [
    'hmac_id' => 'e5e05dd4da47426e9da0e9d3cad04ed5',
    'hmac_secret' => 'M2I4YzllMWItZDA3Zi00',
  ],
  // FootLocker AE.
  'flae_qa' => [
    'hmac_id' => '61f09f6fe1474ce68cc8057f97188d2f',
    'hmac_secret' => 'MjU3YzZiOGEtNmI1Yi00',
  ],
  'flae_uat' => [
    'hmac_id' => '8d8c55c242f947efa44de57bbd84155e',
    'hmac_secret' => 'YWFlYTc2ZDgtYmJjYS00',
  ],
  'flae_prod' => [
    'hmac_id' => 'a220ff2d59e846c79426e5b001516063',
    'hmac_secret' => 'ZmJiNzUzOTctMTBiMC00',
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
