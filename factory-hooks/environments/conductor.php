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
  'mckw_sit_dev2' => [
    'hmac_id' => '0fd84c1e2c1e4bd3916eca286ba49ca2',
    'hmac_secret' => 'YTkyYWRkMmQtOWYwOC00',
    'site_id' => 317,
  ],
  'mckw_oms_dev2' => [
    'hmac_id' => 'f785aa4e9ad44be0b66cdc81b3cbbdf5',
    'hmac_secret' => 'ZDk0ZWZkZWItOTI1ZC00',
    'site_id' => 326,
  ],
  'mckw_training_dev2' => [
    'hmac_id' => '1ac051388031460d87f37a8fe468adab',
    'hmac_secret' => 'MzMxZGI2ZGEtMmUwYS00',
    'site_id' => 336,
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
  'mckw_oms' => [
    'hmac_id' => '5cd7600592e1449baa4f55bc0a44a783',
    'hmac_secret' => 'ZDc2MmZhMDMtMmZhMS00',
    'site_id' => 320,
  ],
  // Mothercare SA.
  'mcsa_dev' => [
    'hmac_id' => '0a161c94a6f74716b9f6f72ebf0eff0e',
    'hmac_secret' => 'Zjg1NmM0NzctODhiYy00',
    'site_id' => 138,
  ],
  'mcsa_sit_dev2' => [
    'hmac_id' => '7a1f4fda1a0147d9bd106890b7ea37b8',
    'hmac_secret' => 'YmFiNmI2MjctMWI2MS00',
    'site_id' => 318,
  ],
  'mcsa_oms_dev2' => [
    'hmac_id' => '1adc0d83a22c403fbc88ccd70f0fd49a',
    'hmac_secret' => 'ZTgyYzgyNjgtMTA2OC00',
    'site_id' => 327,
  ],
  'mcsa_training_dev2' => [
    'hmac_id' => 'c00010d08dc0461ebfd26ebc13ff11ea',
    'hmac_secret' => 'OTlhMDZiYTQtYjZiMS00',
    'site_id' => 337,
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
  'mcsa_oms' => [
    'hmac_id' => '35553c2ad0ed40969cbf2e165aa5f8ff',
    'hmac_secret' => 'NjA2YjU1NDEtMmUyYi00',
    'site_id' => 321,
  ],
  // Mothercare UAE.
  'mcae_qa' => [
    'hmac_id' => '66764a4f8f9146aaab1352fb66897fad',
    'hmac_secret' => 'YWY2ZmY0MjItZmQ2OS00',
    'site_id' => 6,
  ],
  'mcae_dev2' => [
    'hmac_id' => '3063b30c2b3e401bafe0dfe0f1611da5',
    'hmac_secret' => 'NzY3NDg4NjctOTk2Mi00',
    'site_id' => 221,
  ],
  'mcae_sit_dev2' => [
    'hmac_id' => 'ef4ca8af47c1456fa88c314273f14142',
    'hmac_secret' => 'ZDg1NDkxNTctMGE3YS00',
    'site_id' => 319,
  ],
  'mcae_oms_dev2' => [
    'hmac_id' => '8a3e004cd7cb4faabf62f732c4d1af26',
    'hmac_secret' => 'ZTEwMDM3OTctM2M5Ni00',
    'site_id' => 328,
  ],
  'mcae_training_dev2' => [
    'hmac_id' => '06a406bdf7004c39b678ca7fbbbda29b',
    'hmac_secret' => 'ZmIwMzI0MDMtZGY4OC00',
    'site_id' => 338,
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
  'mcae_oms' => [
    'hmac_id' => '67a8312d6718495998731b6c4ab6b4b9',
    'hmac_secret' => 'ZDA5OWNhYWItNGE3ZS00',
    'site_id' => 322,
  ],
  // Mothercare EG
  'mceg_oms_dev2' => [
    'hmac_id' => '16608f2f16a04296bef2687d60400a86',
    'hmac_secret' => 'OTM3YmNmMTgtYmMwMy00',
    'site_id' => 373,
  ],
  'mceg_qa' => [
    'hmac_id' => '8e3ed7928ee9496b85ffa3a751c09b1d',
    'hmac_secret' => 'MWJiYTk5ZmItYzIwMC00',
    'site_id' => 329,
  ],
  'mceg_training_dev2' => [
    'hmac_id' => '5d133f709fee409fb6e778b2ed42979f',
    'hmac_secret' => 'OWY2MTI0ZmUtYjkxYS00',
    'site_id' => 407,
  ],
  'mceg_uat' => [
    'hmac_id' => '875016aa86534444912555dfd067c5b8',
    'hmac_secret' => 'NmRlNDE4ZjItMDc5My00',
    'site_id' => 333,
  ],
  'mceg_prod' => [
    'site_id' => 332,
  ],
  // Mothercare BH
  'mcbh_oms_dev2' => [
    'hmac_id' => 'c2c5c02b010a471e9209e6070b9f3055',
    'hmac_secret' => 'ZDU0Mzk4ZjgtYmE2ZC00',
    'site_id' => 374,
  ],
  'mcbh_qa' => [
    'hmac_id' => '474c019c19324a5dbcd873bcb1ea049f',
    'hmac_secret' => 'YzY5YTBkYTgtODc5OC00',
    'site_id' => 330,
  ],
  'mcbh_training_dev2' => [
    'hmac_id' => 'b85fbf51221946d48c085b7169c23d23',
    'hmac_secret' => 'M2VhYWQzNjMtNzc2Mi00',
    'site_id' => 408,
  ],
  'mcbh_uat' => [
    'hmac_id' => '6295db5028ac4bac8f6f380f81e15a02',
    'hmac_secret' => 'NmFiYjBlYmMtYjg5YS00',
    'site_id' => 334,
  ],
  'mcbh_prod' => [
    'site_id' => 363,
  ],
  // Mothercare QA
  'mcqa_oms_dev2' => [
    'hmac_id' => '3072ed6cda674ceebb7c63fd12eb9a1d',
    'hmac_secret' => 'YTFhODYxYzQtMjIyZS00',
    'site_id' => 375,
  ],
  'mcqa_qa' => [
    'hmac_id' => 'c2fa8f89aa554f778aeeec073ab89810',
    'hmac_secret' => 'ODllNzYwNjAtNzk2My00',
    'site_id' => 331,
  ],
  'mcqa_training_dev2' => [
    'hmac_id' => '2dec65193f1d46dea88b05ae802a2f09',
    'hmac_secret' => 'ZTAyNGUxZWUtM2ZhZS00',
    'site_id' => 409,
  ],
  'mcqa_uat' => [
    'hmac_id' => 'bfae4a74e1d449ed8e8f0ecbd637b1d4',
    'hmac_secret' => 'YTNlNjZhOGMtYTBmOS00',
    'site_id' => 335,
  ],
  'mcqa_prod' => [
    'site_id' => 364,
  ],
  // H&M KW.
  'hmkw_dev' => [
    'hmac_id' => '03551dee94ad4f91a20bf41459c02bdd',
    'hmac_secret' => 'MWI3NDdjYWQtNGUwMS00',
    'site_id' => 135,
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
  'hmsa_dev' => [
    'hmac_id' => 'aa9714724c894af59aafdd60a2bfd09a',
    'hmac_secret' => 'YjQxNWM0MmItODM0Ny00',
    'site_id' => 136,
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
  'hmae_dev' => [
    'hmac_id' => 'c988092f47f54bb7bcc14fa2fd4fd950',
    'hmac_secret' => 'ZTkwODE2YTEtYTE2Zi00',
    'site_id' => 137,
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
    'hmac_id' => 'ef24328464034ff481bc508053f48e9a',
    'hmac_secret' => 'YjZhMjg1YjQtYjg0NC00',
    'site_id' => 204,
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
  // H&M BH.
  'hmbh_uat' => [
    'hmac_id' => '8fda8750ba7940f4ab1b27c7219b9f0f',
    'hmac_secret' => 'MmRjZjczY2QtN2U4OC00',
    'site_id' => 354,
  ],
  'hmbh_prod' => [
    'site_id' => 356,
  ],
  // H&M QA.
  'hmqa_uat' => [
    'hmac_id' => 'cfcd0d13ce9d4a28941a37d7481b980d',
    'hmac_secret' => 'OTZjMDBiYzUtNmE3MS00',
    'site_id' => 355,
  ],
  'hmqa_prod' => [
    'site_id' => 357,
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
  // Pottery Barn Kids KW.
  'pbkkw_qa' => [
    'hmac_id' => '7b1ac8753c5c46dda4d43e6343cc9921',
    'hmac_secret' => 'OTU1MDJmOWMtZGYyOS00',
    'site_id' => 226,
  ],
  'pbkkw_uat' => [
    'hmac_id' => '5e02ce58f5664cef9165601a64f53bc8',
    'hmac_secret' => 'NWJiOTRmNjQtMDdkZS00',
    'site_id' => 242,
  ],
  // Pottery Barn Kids AE.
  'pbkae_qa' => [
    'hmac_id' => '727104fbad4240eea0d5bda04da0927f',
    'hmac_secret' => 'NzY3OWU3YjItNmZiNS00',
    'site_id' => 228,
  ],
  'pbkae_uat' => [
    'hmac_id' => 'c7760836e4334c0584f80d13e3c21315',
    'hmac_secret' => 'OTU5MGYwMzktNDVjMy00',
    'site_id' => 244,
  ],
  // Pottery Barn Kids SA.
  'pbksa_qa' => [
    'hmac_id' => 'db1681e298b8408294834b3cb9f83a5e',
    'hmac_secret' => 'ODk1N2E1YTEtZmFhNS00',
    'site_id' => 227,
  ],
  'pbksa_uat' => [
    'hmac_id' => '046bc57011364079b4e4d94edd1fdc6b',
    'hmac_secret' => 'YjcyYTdhMWItMzVhZi00',
    'site_id' => 243,
  ],
  'pbkkw_prod' => [
    'site_id' => 249,
  ],
  'pbksa_prod' => [
    'site_id' => 250,
  ],
  'pbkae_prod' => [
    'site_id' => 251,
  ],
  'pbkeg_prod' => [
    'site_id' => 252,
  ],
  'pbkbh_prod' => [
    'site_id' => 253,
  ],
  'pbkqa_prod' => [
    'site_id' => 254,
  ],
  // BathBodyWorks KW.
  'bbwkw_dev3' => [
    'hmac_id' => '59ad4bcc83f44f47953abe4101d7584c',
    'hmac_secret' => 'NDE1ODgwYTItYWUxOS00',
    'site_id' => 286,
  ],
  'bbwkw_qa' => [
    'hmac_id' => '0b92904888cd422f8acd2938e199916e',
    'hmac_secret' => 'YWFiMzI4M2QtMzY5ZC00',
    'site_id' => 23,
  ],
  'bbwkw_sit_dev2' => [
    'hmac_id' => '97178cd378894e4282ddb9032f3fbe94',
    'hmac_secret' => 'OGQyMzYzNTEtZmE2Ni00',
    'site_id' => 391,
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
    'site_id' => 153,
  ],
  'bbwsa_qa' => [
    'hmac_id' => '52fd839150734f1c8afe9db8c7f688e8',
    'hmac_secret' => 'ZTFjNTIzZTUtYzI3YS00',
    'site_id' => 24,
  ],
  'bbwsa_sit_dev2' => [
    'hmac_id' => 'ceeabf88ea884eb396282267e0c42011',
    'hmac_secret' => 'ODJmZjkzYjgtNTE4OS00',
    'site_id' => 392,
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
  'bbwae_sit_dev2' => [
    'hmac_id' => '1a2892ecbe7c408fa319784a5015b217',
    'hmac_secret' => 'ZjJiNWFjZjUtYTdiOS00',
    'site_id' => 393,
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
  // BathBodyWorks Egypt.
  'bbweg_dev' => [
    'hmac_id' => '253eac3a6dc9430f96a93a264204719b',
    'hmac_secret' => 'M2YyMjJkNjUtYTBmYy00',
    'site_id' => 353,
  ],
  'bbweg_qa' => [
    'hmac_id' => '4d7962f023c54bbdb9ea5b6811cbb5af',
    'hmac_secret' => 'MWRmZGI4ZTMtYzM0MS00',
    'site_id' => 281,
  ],
  'bbweg_sit_dev2' => [
    'hmac_id' => 'f2b5fe12c0d644b98c7eb6effedd2ac9',
    'hmac_secret' => 'ZmM3OWUxMTctOWQ4Mi00',
    'site_id' => 394,
  ],
  'bbweg_uat' => [
    'hmac_id' => '9617776441fb41468e602b6c2022117e',
    'hmac_secret' => 'ODM0NTc2NzktNzBiYS00',
    'site_id' => 277,
  ],
  'bbweg_prod' => [
    'site_id' => 273,
  ],
  // BathBodyWorks BH.
  'bbwbh_dev' => [
    'hmac_id' => '6a9541a52b1444a7a9805fe811297ac3',
    'hmac_secret' => 'MDgzN2M2OWItNDFhMy00',
    'site_id' => 219,
  ],
  'bbwbh_qa' => [
    'hmac_id' => '6da3dc85d548408a92257b97dbb7a5c5',
    'hmac_secret' => 'ZTQ0Y2VlZWQtOGQwNS00',
    'site_id' => 217,
  ],
  'bbwbh_sit_dev2' => [
    'hmac_id' => '4d8b4dffb3f74f51a9ad601b93ce0ee5',
    'hmac_secret' => 'OWY2M2Q1ZWItMzYwZC00',
    'site_id' => 395,
  ],
  'bbwbh_uat' => [
    'hmac_id' => '0a6ddf6ca9d54b199aea494947dc6fc9',
    'hmac_secret' => 'MWExMGE0NDQtZGY5OS00',
    'site_id' => 222,
  ],
  'bbwbh_prod' => [
    'site_id' => 224,
  ],
  // BathBodyWorks QA.
  'bbwqa_dev' => [
    'hmac_id' => 'aca0a08c946347a6b796ff622baf2cfc',
    'hmac_secret' => 'YmVhODM4NjYtZmUyOC00',
    'site_id' => 220,
  ],
  'bbwqa_qa' => [
    'hmac_id' => '956ca0edd8b140b18aa06acf1432c995',
    'hmac_secret' => 'NmM2NjE0MDMtNjdhNC00',
    'site_id' => 218,
  ],
  'bbwqa_sit_dev2' => [
    'hmac_id' => '9490374180c24d58b5a42e51cfcb08c4',
    'hmac_secret' => 'MDA5OTYwMWEtMjBiNy00',
    'site_id' => 396,
  ],
  'bbwqa_uat' => [
    'hmac_id' => '623cf547b8154a849e2eefe03cb94978',
    'hmac_secret' => 'Nzg2ZWZjN2MtMmRjNi00',
    'site_id' => 223,
  ],
  'bbwqa_prod' => [
    'site_id' => 225,
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
  // VictoriaSecret EG.
  'vseg_qa' => [
    'hmac_id' => '2024c11d8b3c4a789a45430676be1a4b',
    'hmac_secret' => 'Y2E1ZjA5NzItNDExOC00',
    'site_id' => 383,
  ],
  'vseg_uat' => [
    'hmac_id' => 'd4b6f2f3070848bba00656a582f9bb48',
    'hmac_secret' => 'ODE2MjliMWEtY2YzOS00',
    'site_id' => 376,
  ],
  'vseg_prod' => [
    'site_id' => 379,
  ],
  // VictoriaSecret BH.
  'vsbh_qa' => [
    'hmac_id' => '41959011408f489696d78b00dcbd2ee3',
    'hmac_secret' => 'ZDU5YTM5MWEtMTlmOC00',
    'site_id' => 384,
  ],
  'vsbh_uat' => [
    'hmac_id' => '495335ce724c4e1b940e003d77234bd6',
    'hmac_secret' => 'NWJkMGZiOTEtYTlkNy00',
    'site_id' => 377,
  ],
  'vsbh_prod' => [
    'site_id' => 380,
  ],
  // VictoriaSecret QA.
  'vsqa_qa' => [
    'hmac_id' => '6fb1a9c4003141f9aa2ec04cd8a2234a',
    'hmac_secret' => 'NTlkNDg3ZjgtNDFlNy00',
    'site_id' => 385,
  ],
  'vsqa_uat' => [
    'hmac_id' => '3f2195e623f244c1b0c090f17b0ac7b5',
    'hmac_secret' => 'ZTFmZWVjOTUtMmEyNS00',
    'site_id' => 378,
  ],
  'vsqa_prod' => [
    'site_id' => 381,
  ],
  // FootLocker KW.
  'flkw_dev' => [
    'hmac_id' => '582a73e8a0534b77970fa38e525f62a1',
    'hmac_secret' => 'MTU5MWJiODYtNTEwZS00',
    'site_id' => 229,
  ],
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
  'flae_dev' => [
    'hmac_id' => 'ddd6506aa778421fa2b0c95a90c057d2',
    'hmac_secret' => 'YTQ0MzYwMmMtNDI0ZS00',
    'site_id' => 386,
  ],
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
  // FootLocker EG.
  'fleg_qa' => [
    'hmac_id' => '363afc0253cc45689121d797deb20009',
    'hmac_secret' => 'YmUzMmZhODAtYTViYy00',
    'site_id' => 282,
  ],
  'fleg_uat' => [
    'hmac_id' => '3818ebc0773e46869c712507c4792441',
    'hmac_secret' => 'YjhlYjE4YTctMTAwYS00',
    'site_id' => 280,
  ],
  'fleg_prod' => [
    'site_id' => 274,
  ],
  // FootLocker BH.
  'flbh_qa' => [
    'hmac_id' => 'a49ad25bbc7a404b81377c036852f7c3',
    'hmac_secret' => 'YjhlYzc5Y2UtMmZlMC00',
    'site_id' => 283,
  ],
  'flbh_uat' => [
    'hmac_id' => 'd42ceb5607494e1a88513ce19532d999',
    'hmac_secret' => 'MzcyMGEzYzYtZjI3ZS00',
    'site_id' => 278,
  ],
  'flbh_prod' => [
    'site_id' => 275,
  ],
  // FootLocker QA.
  'flqa_qa' => [
    'hmac_id' => '730329239f924161bfcb7e74c56210d0',
    'hmac_secret' => 'NWZjNmJmYjYtMjk1ZC00',
    'site_id' => 284,
  ],
  'flqa_uat' => [
    'hmac_id' => '1153c84771ef4bdab90659551c2b56fc',
    'hmac_secret' => 'ZWMyZWQxN2EtODU1Ny00',
    'site_id' => 279,
  ],
  'flqa_prod' => [
    'site_id' => 276,
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
  'weeg_qa' => [
    'hmac_id' => '34ceed5aed864c8ab3367f466fae93aa',
    'hmac_secret' => 'NzhhMmNmMmItMGIxZS00',
    'site_id' => 345,
  ],
  'webh_qa' => [
    'hmac_id' => '033b60e2454b469798668b09d486a866',
    'hmac_secret' => 'ZTViZGZiOTItMTFmZC00',
    'site_id' => 346,
  ],
  'weqa_qa' => [
    'hmac_id' => '5e7345f06ebc4014b3db0b4fc67d5775',
    'hmac_secret' => 'ODMwYWQ4ZjctOGM5ZS00',
    'site_id' => 347,
  ],
  'wekw_dev2' => [
    'hmac_id' => 'b6647568fe094f4887ff1d961da5728b',
    'hmac_secret' => 'NmZiNGJmNDYtNjM2NS00',
    'site_id' => 295,
  ],
  'wekw_sit_dev2' => [
    'hmac_id' => '61e74d85683045679e0230ec2945dbc3',
    'hmac_secret' => 'YjMxY2YwZGYtNjRkZC00',
    'site_id' => 314,
  ],
  'wesa_dev2' => [
    'hmac_id' => '4f030a3ce23242ee8a3d9694a8d6d3ec',
    'hmac_secret' => 'ZjE5NWRlNzQtN2VjZS00',
    'site_id' => 296,
  ],
  'wesa_sit_dev2' => [
    'hmac_id' => 'dfed154d45174625aef111605b12c618',
    'hmac_secret' => 'YjYyNTgxN2MtOGM3Yi00',
    'site_id' => 315,
  ],
  'weae_dev2' => [
    'hmac_id' => '0b96abc3da824ea582779c4fe95838bd',
    'hmac_secret' => 'ODNmOWMzOTQtZGEzYy00',
    'site_id' => 297,
  ],
  'weae_sit_dev2' => [
    'hmac_id' => '5378fec5b22741b9aa6f99b8ee2f2f13',
    'hmac_secret' => 'MzUzNjhiN2QtN2Q4My00',
    'site_id' => 316,
  ],
  'wekw_uat' => [
    'hmac_id' => '4d673878644c45a0908049f5e26be993',
    'hmac_secret' => 'NDIzZDBkOTktZGJiNi00',
    'site_id' => 171,
  ],
  'wekw_oms' => [
    'hmac_id' => '0ab6fc44e79a4ee39ac965f7cf1b7cba',
    'hmac_secret' => 'ODZhOTEyY2UtNzIzZC00',
    'site_id' => 323,
  ],
  'wesa_uat' => [
    'hmac_id' => '0d0a24af21ef4399b7dc5b3eae90bd69',
    'hmac_secret' => 'NjgyMDA1MDMtYzUxOC00',
    'site_id' => 172,
  ],
  'wesa_oms' => [
    'hmac_id' => '02d762b8337c4eb89d7467fe3cef4616',
    'hmac_secret' => 'M2Y3NWY3YzgtOWM2OS00',
    'site_id' => 324,
  ],
  'weae_uat' => [
    'hmac_id' => 'cb7192b7d39840f8b72aa5393c6034fe',
    'hmac_secret' => 'NzI5M2M1MjYtMTE5ZS00',
    'site_id' => 173,
  ],
  'weae_oms' => [
    'hmac_id' => '991ef51e0a30492c9533836222567286',
    'hmac_secret' => 'MTU1NmJiZTItM2M1ZC00',
    'site_id' => 325,
  ],
  'weeg_uat' => [
    'hmac_id' => '6b29131cfb114895ab0bedd65411b11f',
    'hmac_secret' => 'NDM5Mzg2MDctNWFhNy00',
    'site_id' => 360,
  ],
  'webh_uat' => [
    'hmac_id' => 'b81b50e0e69f4bf1a91ae7f8d953664c',
    'hmac_secret' => 'MTUxYjdmZTMtNWQ3MS00',
    'site_id' => 358,
  ],
  'weqa_uat' => [
    'hmac_id' => '3942d2d6cd8e47e2bf00f78048e6b445',
    'hmac_secret' => 'OGFlMzc1NGYtZjdmNi00',
    'site_id' => 359,
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
  'webh_prod' => [
    'site_id' => 361,
  ],
  'weqa_prod' => [
    'site_id' => 362,
  ],
  // American Eagle Outfitters.
  'aeokw_dev' => [
    'hmac_id' => '9c9e527db24446d98cde69829ffde832',
    'hmac_secret' => 'N2QzZGUxMzEtZTNjYy00',
    'site_id' => 178,
  ],
  'aeokw_dev2' => [
    'hmac_id' => 'cdd7fe54d06941b682fe52636036d8cc',
    'hmac_secret' => 'NTlmNDJlNTQtMmZiOS00',
    'site_id' => 298,
  ],
  'aeokw_training_dev2' => [
    'hmac_id' => '19ca57995e874f5ca12d4c7d2903b146',
    'hmac_secret' => 'OTk1MDI2NWEtNmEzMC00',
    'site_id' => 339,
  ],
  'aeosa_dev' => [
    'hmac_id' => '8d434c6d92f649d085e519e59b820628',
    'hmac_secret' => 'NzE2MTRkMjQtNjFkZS00',
    'site_id' => 348,
  ],
  'aeosa_dev2' => [
    'hmac_id' => '2ecf6e3ec6584060a042ae52c473ad75',
    'hmac_secret' => 'NTJiNGQxNGUtNjU4Yi00',
    'site_id' => 299,
  ],
  'aeosa_training_dev2' => [
    'hmac_id' => '3a70375559eb421cbe12e432ae400f39',
    'hmac_secret' => 'N2Y5Yjk5NmUtM2Y4Zi00',
    'site_id' => 340,
  ],
  'aeoae_dev' => [
    'hmac_id' => 'b6c7a3cb01ab44e7b3e84c667e745865',
    'hmac_secret' => 'MjczMDQzMzEtZDk3Ni00',
    'site_id' => 349,
  ],
  'aeoae_dev2' => [
    'hmac_id' => 'ead40d01c41f40beb52d8a7f76d516f3',
    'hmac_secret' => 'ODNiY2UyZTAtZDNiYy00',
    'site_id' => 300,
  ],
  'aeoae_training_dev2' => [
    'hmac_id' => '0b09f76c1bad4589a2c7d4ab5cf5132f',
    'hmac_secret' => 'MDdjZjI5OTUtYTgwZC00',
    'site_id' => 341,
  ],
  'aeoeg_dev' => [
    'hmac_id' => 'ac4f9df7e8e5494c9cc37daa5864db31',
    'hmac_secret' => 'YTdjNzhmZTUtNmJkOC00',
    'site_id' => 350,
  ],
  'aeoeg_dev2' => [
    'hmac_id' => '2c884817dc954e7194165a06f54144ae',
    'hmac_secret' => 'YWY2MDBmNjEtZGE2OS00',
    'site_id' => 301,
  ],
  'aeoeg_training_dev2' => [
    'hmac_id' => 'cc9a92e6ca6d43fa9e9bf5dc4dd1563d',
    'hmac_secret' => 'YTNkZTc0Y2UtNDVmNS00',
    'site_id' => 342,
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
  'aeobh_dev' => [
    'hmac_id' => '8ef32b6f28354c7c911063154c2eb6fa',
    'hmac_secret' => 'ZjFkYjU2ZTMtMjRiNi00',
    'site_id' => 351,
  ],
  'aeobh_dev2' => [
    'hmac_id' => 'dc1c3020114540378b51112f70780d19',
    'hmac_secret' => 'YjY5NDcwOTctZjU3Ny00',
    'site_id' => 369,
  ],
  'aeobh_qa' => [
    'hmac_id' => 'f14b7223639249f085dc42ca7944ad46',
    'hmac_secret' => 'NjllNDg4NmItMzUyNi00',
    'site_id' => 343,
  ],
  'aeoqa_dev' => [
    'hmac_id' => '1fb0a82ce141405bb695be86542dcc68',
    'hmac_secret' => 'N2I3OGU4NTctNGI3My00',
    'site_id' => 352,
  ],
  'aeoqa_dev2' => [
    'hmac_id' => '05fc397d271048bfa482c12a54d20d98',
    'hmac_secret' => 'MDFlYTlhYzUtNGVmMy00',
    'site_id' => 370,
  ],
  'aeoqa_qa' => [
    'hmac_id' => '7203b241292f4d2db96141d4fbd16adc',
    'hmac_secret' => 'NzM4YjQ1NDctMDU0Yy00',
    'site_id' => 344,
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
  'aeobh_uat' => [
    'hmac_id' => 'a53bf12f73ca4a05b0e8d0ad33399237',
    'hmac_secret' => 'NDNlYmI2Y2ItMmI2Mi00',
    'site_id' => 302,
  ],
  'aeoqa_uat' => [
    'hmac_id' => '713844a9cc5c4b00b4fe0a21c32362ba',
    'hmac_secret' => 'MTY3MjFjN2EtY2UwOS00',
    'site_id' => 303,
  ],
  'aeokw_prod' => [
    'site_id' => 205,
  ],
  'aeosa_prod' => [
    'site_id' => 206,
  ],
  'aeoae_prod' => [
    'site_id' => 207,
  ],
  'aeoeg_prod' => [
    'site_id' => 208,
  ],
  'aeobh_prod' => [
    'site_id' => 293,
  ],
  'aeoqa_prod' => [
    'site_id' => 294,
  ],
  // Muji
  'muae_dev' => [
    'hmac_id' => 'fa9f4aee73dd4acc93bb4f8457cc8486',
    'hmac_secret' => 'NzE5MDQ2NmYtNTg3My00',
    'site_id' => 240,
  ],
  'mukw_qa' => [
    'hmac_id' => '64c8be065a6142e59b408db05439332d',
    'hmac_secret' => 'MmIxOWM3OTgtOGQ1OS00',
    'site_id' => 234,
  ],
  'musa_qa' => [
    'hmac_id' => 'f7e2dd7a78ef4dd1a361d3bdb048714a',
    'hmac_secret' => 'MTU4OTY3NDgtOGM3ZS00',
    'site_id' => 235,
  ],
  'muae_qa' => [
    'hmac_id' => '45e8107d0344489a9dfe4323ae53e586',
    'hmac_secret' => 'YTg1NWQ1NzktZWZkYi00',
    'site_id' => 236,
  ],
  'mueg_qa' => [
    'hmac_id' => 'c89dcfb3aa9146ccb84545aff3597a66',
    'hmac_secret' => 'YTQ0NjE0NzEtYTExNS00',
    'site_id' => 237,
  ],
  'mukw_uat' => [
    'hmac_id' => 'e8dc16861254494e80c36dd3255aab5d',
    'hmac_secret' => 'OTY0OTRjZTYtYzMyZC00',
    'site_id' => 245,
  ],
  'musa_uat' => [
    'hmac_id' => '030218663ebc492a83427a8a2f6eaa37',
    'hmac_secret' => 'ZWRiNThjYTEtNzEzNy00',
    'site_id' => 246,
  ],
  'muae_uat' => [
    'hmac_id' => '73cf818bcd7b47ce975acf336d831206',
    'hmac_secret' => 'ZWNlYjZhYjUtOWJiMi00',
    'site_id' => 247,
  ],
  'mueg_uat' => [
    'hmac_id' => 'd5cb6eba6f1d4987a0934685e1f87bd5',
    'hmac_secret' => 'YmNkYjJkMTgtZGU4Mi00',
    'site_id' => 248,
  ],
  'mubh_uat' => [
    'hmac_id' => '6f7c8b72bafe47bf9be8d3d9cca40cc2',
    'hmac_secret' => 'YTlkNjEwOTAtZTNjZS00',
    'site_id' => 306,
  ],
  'muqa_uat' => [
    'hmac_id' => 'f9a2c0741224406295333b16c87ebc7c',
    'hmac_secret' => 'NWRkZDI1YmItOWMwNS00',
    'site_id' => 307,
  ],
  'mukw_prod' => [
    'site_id' => 267,
  ],
  'musa_prod' => [
    'site_id' => 268,
  ],
  'muae_prod' => [
    'site_id' => 269,
  ],
  'mueg_prod' => [
    'site_id' => 270,
  ],
  'mubh_prod' => [
    'site_id' => 271,
  ],
  'muqa_prod' => [
    'site_id' => 272,
  ],
  // Boots pharmacy.
  'bpkw_dev' => [
    'hmac_id' => 'd498a56fab204489ad5c3de9840d8e98',
    'hmac_secret' => 'YjM5Mjc1M2QtNDhkNC00',
    'site_id' => 209,
  ],
  'bpsa_dev' => [
    'hmac_id' => '114ffb670cf6403ebaa8a7b600bf85dd',
    'hmac_secret' => 'NDQxMjc0MzUtOWM5MS00',
    'site_id' => 210,
  ],
  'bpae_dev' => [
    'hmac_id' => 'ecd6332722a145c79aea9e4bbb465458',
    'hmac_secret' => 'ZTQxZWY1ZTItNzUzNC00',
    'site_id' => 211,
  ],
  'bpeg_dev' => [
    'hmac_id' => 'be1cd47e81154f5ab61de7862fc8dcda',
    'hmac_secret' => 'OTAyNWRhZDctZDk0Zi00',
    'site_id' => 212,
  ],
  'bpae_dev2' => [
    'hmac_id' => '1dbe65d50909412e8e55d6afbd7c1e85',
    'hmac_secret' => 'NzA2MGUyNjEtMTNiNy00',
    'site_id' => 285,
  ],
  'bpkw_qa' => [
    'hmac_id' => '602caba2dc7a4ab3801169a1e8c5eb77',
    'hmac_secret' => 'NTA1MGIzNWQtYTc3My00',
    'site_id' => 213,
  ],
  'bpsa_qa' => [
    'hmac_id' => '95d0b54224a444cfb48f922c56055df3',
    'hmac_secret' => 'OGJlN2QyZjUtZmVlYS00',
    'site_id' => 214,
  ],
  'bpae_qa' => [
    'hmac_id' => '2bd4976d93e743de8374ff38e8b45a53',
    'hmac_secret' => 'YTUzMjBjNmYtY2RmZi00',
    'site_id' => 215,
  ],
  'bpeg_qa' => [
    'hmac_id' => 'c2615c9507594034a76a2dc4ff5f10e2',
    'hmac_secret' => 'MDkyNjMzNmQtOWI4NC00',
    'site_id' => 216,
  ],
  'bpbh_qa' => [
    'hmac_id' => 'b89a5fc044f64c779c498cef2aad6fc7',
    'hmac_secret' => 'NTA1YjFlYjctNjZmOC00',
    'site_id' => 367,
  ],
  'bpqa_qa' => [
    'hmac_id' => '67ffe1e893d04f44a62308ac25f2cb3a',
    'hmac_secret' => 'MjdlYTFkMmMtN2QwMy00',
    'site_id' => 368,
  ],
  'bpkw_uat' => [
    'hmac_id' => 'dc770c85101f4acca8039cf1e5247bb9',
    'hmac_secret' => 'Nzc2MmU0MWMtOTA3OS00',
    'site_id' => 230,
  ],
  'bpsa_uat' => [
    'hmac_id' => '76b35126e3a941c596d8b5c05fc726e0',
    'hmac_secret' => 'YTU5MTg3YzYtOTNkMy00',
    'site_id' => 231,
  ],
  'bpae_uat' => [
    'hmac_id' => '5507e850be8c433e8664467243e43327',
    'hmac_secret' => 'YzVhMjRiNmYtYTIxOS00',
    'site_id' => 232,
  ],
  'bpeg_uat' => [
    'hmac_id' => '259fe24b36404c65a1b5037804673664',
    'hmac_secret' => 'YTY3YzYwNjgtMmRlOS00',
    'site_id' => 233,
  ],
  'bpbh_uat' => [
    'hmac_id' => '70f9b01c8bcd4153aa66238816a247b0',
    'hmac_secret' => 'M2FiYTYwMzctYzA1Mi00',
    'site_id' => 365,
  ],
  'bpqa_uat' => [
    'hmac_id' => '82317c1f6941405aaee7f0748775d867',
    'hmac_secret' => 'M2VhOWMwNjktNGM4Yi00',
    'site_id' => 366,
  ],
  'bpkw_prod' => [
    'site_id' => 261,
  ],
  'bpsa_prod' => [
    'site_id' => 262,
  ],
  'bpae_prod' => [
    'site_id' => 263,
  ],
  'bpeg_prod' => [
    'site_id' => 264,
  ],
  'bpbh_prod' => [
    'site_id' => 265,
  ],
  'bpqa_prod' => [
    'site_id' => 266,
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
