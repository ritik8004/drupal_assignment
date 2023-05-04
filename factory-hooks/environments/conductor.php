<?php
// phpcs:ignoreFile

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
  'mcqa_dev3' => [
    'hmac_id' => 'cb9b2e7d626c485b9f93c959be51c987',
    'hmac_secret' => 'ZDIwMzFiN2MtNzI5OS00',
    'site_id' => 451,
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
  'mcae_dev3' => [
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
  'mceg_dev3' => [
    'hmac_id' => '4a10166874eb479289e2dd270b6db8a6',
    'hmac_secret' => 'ZGJjMDZmYmItMTgyMC00',
    'site_id' => 472,
  ],
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
  'mcbh_dev3' => [
    'hmac_id' => 'd8eff8c6ca25455992cff38e56ee7ce1',
    'hmac_secret' => 'NjU4ZTA3MmQtMmFlZi00',
    'site_id' => 473,
  ],
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
  'hmkw_oms' => [
    'hmac_id' => 'f6f1fae170634642b9b43485a545784c',
    'hmac_secret' => 'OWI3ZDBlZDAtNzk5Zi00',
    'site_id' => 568,
  ],
  'hmsa_oms' => [
    'hmac_id' => '037a4a5c2ba14c1d98337a4b67434cf8',
    'hmac_secret' => 'OGZhZDRhZWYtZGYzOC00',
    'site_id' => 569,
  ],
  'hmae_oms' => [
    'hmac_id' => 'dae4c72ba8024aa58c7c105440a446ff',
    'hmac_secret' => 'NDEzYWJiNDUtOWIwOC00',
    'site_id' => 570,
  ],
  'hmeg_oms' => [
    'hmac_id' => 'd32f8c38a5b84ed4a2dd5cc76b715c94',
    'hmac_secret' => 'ZDdlNTU1YmEtMGY5MS00',
    'site_id' => 571,
  ],
  'hmqa_oms' => [
    'hmac_id' => '57136dbbd8664fef94dc569ecd52e877',
    'hmac_secret' => 'OWNjOTA5OTctYWM2ZC00',
    'site_id' => 573,
  ],
  'hmkw_dev3' => [
    'hmac_id' => '1b56d617c277492597e27391f2752aed',
    'hmac_secret' => 'MjVmNTNmNTgtY2Q0My00',
    'site_id' => 448,
  ],
  'hmqa_dev3' => [
    'hmac_id' => 'b889465ce682411489be50183778a7c2',
    'hmac_secret' => 'MGE0NDFiZTctYzcwMi00',
    'site_id' => 471,
  ],
  'hmkw_qa2' => [
    'hmac_id' => 'ad3866d497c34d529235f020461ed4af',
    'hmac_secret' => 'OTk1ODcyZTItYmVmNC00',
    'site_id' => 432,
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
  'hmsa_qa2' => [
    'hmac_id' => '78f2d639d1c040859763c88f14b8e2cf',
    'hmac_secret' => 'NjYyMTBmZjktYmQ1OS00',
    'site_id' => 435,
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
  'hmbh_qa' => [
    'hmac_id' => '05ed627d33c94c0598b08a6a5bc269df',
    'hmac_secret' => 'MmQyYWFiYWYtMDAwOC00',
    'site_id' => 412,
  ],
  'hmbh_uat' => [
    'hmac_id' => '8fda8750ba7940f4ab1b27c7219b9f0f',
    'hmac_secret' => 'MmRjZjczY2QtN2U4OC00',
    'site_id' => 354,
  ],
  'hmbh_prod' => [
    'site_id' => 356,
  ],
  // H&M QA.
  'hmqa_qa' => [
    'hmac_id' => '037c238e9b0e474aa5cb501a24bde29d',
    'hmac_secret' => 'MzNlZDk0NjgtOWUzOC00',
    'site_id' => 413,
  ],
  'hmqa_qa2' => [
    'hmac_id' => '7b87c3916f0c445b8e50642a2114e947',
    'hmac_secret' => 'ZDBjZGQxMjEtYjQzOC00',
    'site_id' => 433,
  ],
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
  'pbkw_dev3' => [
    'hmac_id' => '1dd964a4d2bb43d3829c3c819701eb26',
    'hmac_secret' => 'NWQ4ZGQwMWItYTA1Mi00',
    'site_id' => 566,
  ],
  'pbkw_qa' => [
    'hmac_id' => '60d7080b75034cf6864adddb614eddbd',
    'hmac_secret' => 'Y2FjOGUwOTYtNzVmMS00',
    'site_id' => 599,
  ],
  'pbkw_oms_qa' => [
    'hmac_id' => '868dfb3ba7f34aacba753364996016ef',
    'hmac_secret' => 'ZjA1NzgzYTktNzhhMi00',
    'site_id' => 639,
  ],
  'pbsa_oms_qa' => [
    'hmac_id' => '8ca3f28e17284534a3b80260d4fefc73',
    'hmac_secret' => 'YzhjN2JiZWEtY2FmYS00',
    'site_id' => 640,
  ],
  'pbae_oms_qa' => [
    'hmac_id' => 'e6b523f31edd4ef4ae042715f4413d48',
    'hmac_secret' => 'MzllNGI3YjMtMzQ1OC00',
    'site_id' => 641,
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
    'hmac_id' => '112dd3509073446e8f9b8429e4187f03',
    'hmac_secret' => 'NmY3YmIyNmUtZDRlOC00',
    'site_id' => 600,
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
  'pbae_dev2' => [
    'hmac_id' => 'c1d103b9b2194fa0ba957981e566c0ce',
    'hmac_secret' => 'YWFmOTJmODctYmMwNS00',
    'site_id' => 148,
  ],
  'pbae_qa' => [
    'hmac_id' => 'df604021ad054309a326e28f91fce8de',
    'hmac_secret' => 'MjQ3ZTE3MjEtMTUyNS00',
    'site_id' => 601,
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
  'pbkkw_test' => [
    'hmac_id' => 'e5d0b9f7aa5e4dc7829276076e94ad94',
    'hmac_secret' => 'NTQzYjlmZGUtMDM2MS00',
    'site_id' => 536,
  ],
  'pbkkw_oms_qa' => [
    'hmac_id' => '8fc7eac0c7fb43d5b45641f9974aab5e',
    'hmac_secret' => 'ZTcyYWZmMGUtNDM0Mi00',
    'site_id' => 650,
  ],
  'pbkkw_uat' => [
    'hmac_id' => '5e02ce58f5664cef9165601a64f53bc8',
    'hmac_secret' => 'NWJiOTRmNjQtMDdkZS00',
    'site_id' => 242,
  ],
  // Pottery Barn Kids AE.
  'pbksa_test' => [
    'hmac_id' => '7d52c9c6ccd6453180986683000e833e',
    'hmac_secret' => 'MTgyYjM0ZTItZmMwZi00',
    'site_id' => 537,
  ],
  'pbkae_oms_qa' => [
    'hmac_id' => '25d193ba42194040bb320fad1392728b',
    'hmac_secret' => 'ZDcwMDdmN2MtZTM0My00',
    'site_id' => 652,
  ],
  'pbkae_uat' => [
    'hmac_id' => 'c7760836e4334c0584f80d13e3c21315',
    'hmac_secret' => 'OTU5MGYwMzktNDVjMy00',
    'site_id' => 244,
  ],
  // Pottery Barn Kids SA.
  'pbkae_test' => [
    'hmac_id' => 'acf9903307ca497296f8033769e6f942',
    'hmac_secret' => 'MjczM2QzMzItZGJmYS00',
    'site_id' => 538,
  ],
  'pbksa_oms_qa' => [
    'hmac_id' => '4fa2011582f0463880ce3509e332beaf',
    'hmac_secret' => 'YjFhYzVkNWEtZGFkMi00',
    'site_id' => 651,
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
  'bbwae_dev3' => [
    'hmac_id' => '31a760b8c9064e10b4ee793f40b77495',
    'hmac_secret' => 'MmU3MjJjZjktMjZlMy00',
    'site_id' => 382,
  ],
  'bbwqa_dev3' => [
    'hmac_id' => '62f6829609e74172a2516893536870f3',
    'hmac_secret' => 'ZTZjOTZkMTQtMWFmNC00',
    'site_id' => 450,
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
  // BathBodyWorks JO.
  'bbwjo_dev' => [
    'hmac_id' => '03499c078b15440896a2c37a33ed5e31',
    'hmac_secret' => 'ZGE5MWYzMjQtMzU2Yi00',
    'site_id' => 410,
  ],
  'bbwjo_qa' => [
    'hmac_id' => 'f04500e8d72a4bb6b46127d333a23574',
    'hmac_secret' => 'ZTNlZjk3NTEtZDM4Yi00',
    'site_id' => 411,
  ],
  'bbwjo_uat' => [
    'hmac_id' => 'bc66cec14b3c44cba6556e0740d0aed0',
    'hmac_secret' => 'MmIyMWMxMDMtOTE3Zi00',
    'site_id' => 414,
  ],
  'bbwjo_prod' => [
    'site_id' => 415,
  ],
  // VictoriaSecret KW.
  'vskw_dev2' => [
    'hmac_id' => '20da17770cbc441193ca24a21b464cda',
    'hmac_secret' => 'MzNlMDc3NzYtZDdlZC00',
    'site_id' => 458,
  ],
  'vskw_dev' => [
    'hmac_id' => '2dd34810e1a840f9bee414a28551c192',
    'hmac_secret' => 'MWY3YjVlMmYtMTg4Ni00',
    'site_id' => 618,
  ],
  'vskw_qa' => [
    'hmac_id' => '3e75d53631114bb58165633212411114',
    'hmac_secret' => 'YzVkNTc5MjgtY2NjMS00',
    'site_id' => 590,
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
  'vssa_dev2' => [
    'hmac_id' => 'c6aaa4bab56447118149f3f28ae51536',
    'hmac_secret' => 'NDMxMWRmY2EtNWU5Zi00',
    'site_id' => 459,
  ],
  'vssa_qa' => [
    'hmac_id' => '7d0e8c5a084f4e4a8e2324d4dd7cadd4',
    'hmac_secret' => 'ZjMwYWY3NDUtMTg5OC00',
    'site_id' => 591,
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
    'hmac_id' => 'b626adcb9fc24eafb2ff861f04601f00',
    'hmac_secret' => 'Nzc4NDY4Y2MtMDM4OS00',
    'site_id' => 592,
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
    'hmac_id' => 'c605d9b12be641b5bb7d06755ecbbef1',
    'hmac_secret' => 'MjQ0NDYxZjgtZTJkYy00',
    'site_id' => 593,
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
    'hmac_id' => '99907edeb07f4a6c970fe151ffcbd673',
    'hmac_secret' => 'OTNlYjQyZmQtY2IxNi00',
    'site_id' => 594,
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
    'hmac_id' => '8852cb39bbd4475a8966aeed6d4fe66a',
    'hmac_secret' => 'ZTZhZWZmYjAtZjFmMy00',
    'site_id' => 595,
  ],
  'vsqa_dev3' => [
    'hmac_id' => 'b2932dd328a24195a1b768cd3629d8fa',
    'hmac_secret' => 'MGQ0ZGExODQtYTFiOC00',
    'site_id' => 489,
  ],
  'vsqa_uat' => [
    'hmac_id' => '3f2195e623f244c1b0c090f17b0ac7b5',
    'hmac_secret' => 'ZTFmZWVjOTUtMmEyNS00',
    'site_id' => 378,
  ],
  'vsqa_prod' => [
    'site_id' => 381,
  ],
  // VictoriaSecret XB.
  'vsxb_uat' => [
    'hmac_id' => 'bec1f01c7f914a4d8ab56bc46a8fac49',
    'hmac_secret' => 'N2RjZjM1NDUtYzYxYS00',
    'site_id' => 653,
  ],
  'vsxb_dev2' => [
    'hmac_id' => 'ec523c6073f84401b295081086607eed',
    'hmac_secret' => 'NzY0OGE0YTYtMzg4ZS00',
    'site_id' => 658,
  ],
  'vsxb_dev3' => [
    'hmac_id' => '7d5de94cfc6e452b8e6b9d071a522444',
    'hmac_secret' => 'ZWZjMmUwMzktYzBkMS00',
    'site_id' => 657,
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
  'flkw_test' => [
    'hmac_id' => '94d26432910f43a893a81e94c69ef682',
    'hmac_secret' => 'ZjM3MjI2ZDgtZmNmMC00',
    'site_id' => 629,
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
  'flqa_dev3' => [
    'hmac_id' => 'be6fb67cc76e4f03bd2be7c1ad772ea3',
    'hmac_secret' => 'YTY1ZTM4ZDItZGMxOC00',
    'site_id' => 449,
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
  'wekw_test' => [
    'hmac_id' => '9315f51e11574167969b31a4c9f2bee5',
    'hmac_secret' => 'OWYzZjM4Y2YtMGI4Ny00',
    'site_id' => 546,
  ],
  'wesa_test' => [
    'hmac_id' => 'c377083c4ef54387baeffd9a465df814',
    'hmac_secret' => 'NTZmMDc5MWUtYTM0Yy00',
    'site_id' => 547,
  ],
  'weae_test' => [
    'hmac_id' => 'b2d1ccfa0dd845c881d790bf425264d5',
    'hmac_secret' => 'MGQ5Yjc1YzQtNjNhZi00',
    'site_id' => 548,
  ],
  'weeg_test' => [
    'hmac_id' => 'e884b17c61ae444185e7fb808c28a0fe',
    'hmac_secret' => 'ZGRlMjc5ZTEtOTdhZS00',
    'site_id' => 549,
  ],
  'webh_test' => [
    'hmac_id' => '4b6847c93b684aa2bfb5820a1f3e7134',
    'hmac_secret' => 'MTk2ZmExY2QtMzQ1Mi00',
    'site_id' => 550,
  ],
  'weqa_test' => [
    'hmac_id' => '2aa8b363613c4438b0d9a4e9ddd935d8',
    'hmac_secret' => 'MGMzNWNjODYtMzc5Mi00',
    'site_id' => 551,
  ],
  'wekw_dev2' => [
    'hmac_id' => 'b6647568fe094f4887ff1d961da5728b',
    'hmac_secret' => 'NmZiNGJmNDYtNjM2NS00',
    'site_id' => 295,
  ],
  'wekw_dev' => [
    'hmac_id' => '43549272788d4aea8536a9274c994930',
    'hmac_secret' => 'MzAwNGI2NmMtNzBjYy00',
    'site_id' => 622,
  ],
  'wekw_sit_dev2' => [
    'hmac_id' => '61e74d85683045679e0230ec2945dbc3',
    'hmac_secret' => 'YjMxY2YwZGYtNjRkZC00',
    'site_id' => 314,
  ],
  'wekw_training_dev2' => [
    'hmac_id' => '121e08e51bfd4dd7ba92ec4a34654251',
    'hmac_secret' => 'M2MzMGZkN2UtMzZhMS00',
    'site_id' => 460,
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
  'wesa_training_dev2' => [
    'hmac_id' => 'c98b5ac51ee249ddbb37de8685a06814',
    'hmac_secret' => 'OTNlNDdiMTgtYTc3ZS00',
    'site_id' => 461,
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
  'weae_training_dev2' => [
    'hmac_id' => '9ecdebb477db4edbbb8a6c85fe3a5e8c',
    'hmac_secret' => 'YWMwYzkyOWItNjViMC00',
    'site_id' => 462,
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
  // American Eagle Outfitters AE.
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
  'aeoae_qa' => [
    'hmac_id' => '89e42750b2764506b5533ed781d49e87',
    'hmac_secret' => 'MmYxZGQ0NDYtYTQ5MC00',
    'site_id' => 176,
  ],
  'aeoae_uat' => [
    'hmac_id' => '05168cbe2e664f4c9d9b676a96d185a4',
    'hmac_secret' => 'YmJmZTAxNzgtMTdhNy00',
    'site_id' => 193,
  ],
  'aeoae_prod' => [
    'site_id' => 207,
  ],
  // American Eagle Outfitters BH.
  'aeobh_dev2' => [
    'hmac_id' => 'dc1c3020114540378b51112f70780d19',
    'hmac_secret' => 'YjY5NDcwOTctZjU3Ny00',
    'site_id' => 369,
  ],
  'aeobh_dev3' => [
    'hmac_id' => 'fb06f5110b5a4510aa921f53e28a1df4',
    'hmac_secret' => 'Zjk3Yjc0ZmEtZGJlMS00',
    'site_id' => 475,
  ],
  'aeobh_training_dev2' => [
    'hmac_id' => 'c7b7b2cc5d6349b79572b4e2976811ff',
    'hmac_secret' => 'YzlhYThhOGYtMmU1ZS00',
    'site_id' => 485,
  ],
  'aeobh_qa' => [
    'hmac_id' => 'f14b7223639249f085dc42ca7944ad46',
    'hmac_secret' => 'NjllNDg4NmItMzUyNi00',
    'site_id' => 343,
  ],
  'aeobh_uat' => [
    'hmac_id' => 'a53bf12f73ca4a05b0e8d0ad33399237',
    'hmac_secret' => 'NDNlYmI2Y2ItMmI2Mi00',
    'site_id' => 302,
  ],
  'aeobh_prod' => [
    'site_id' => 293,
  ],
  // American Eagle Outfitters EQ.
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
  'aeoeg_dev3' => [
    'hmac_id' => '9d3f00920f3e444caf422fe41b90dd7f',
    'hmac_secret' => 'M2JjOGYwOWMtZjdlMi00',
    'site_id' => 474,
  ],
  'aeoeg_qa' => [
    'hmac_id' => '5e49e396d2ec416f9216a3a287f738fc',
    'hmac_secret' => 'YzdkOTIyYjItNzc3ZS00',
    'site_id' => 177,
  ],
  'aeoeg_uat' => [
    'hmac_id' => 'a37e371caae3434ea9372ea053607857',
    'hmac_secret' => 'ZDFmOGM0NDEtNjlmNy00',
    'site_id' => 194,
  ],
  'aeoeg_prod' => [
    'site_id' => 208,
  ],
  // American Eagle Outfitters JO.
  'aeojo_dev2' => [
    'hmac_id' => '76d58cceeb4b4682a2b0d624f83d799c',
    'hmac_secret' => 'ZDRkODYzOTItM2QzMy00',
    'site_id' => 559,
  ],
  'aeojo_training_dev2' => [
    'hmac_id' => 'b83950c1bbdf4c0ba17e23365bacfbd7',
    'hmac_secret' => 'NGFkODI4NTctNDE3Mi00',
    'site_id' => 487,
  ],
  'aeojo_qa' => [
    'hmac_id' => '788a0f0311c64f0e9196d4fdea5a1c71',
    'hmac_secret' => 'OTVjMzhlNjUtN2VlYy00',
    'site_id' => 425,
  ],
  'aeojo_uat' => [
    'hmac_id' => '3f2842c61c4e464180340b8e3bdc277b',
    'hmac_secret' => 'YjNjMjNhY2YtMGM3MC00',
    'site_id' => 427,
  ],
  'aeojo_prod' => [
    'site_id' => 428,
  ],
  // American Eagle Outfitters KW.
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
  'aeokw_qa' => [
    'hmac_id' => 'e6c4af7406bb4694a0caa108409617d9',
    'hmac_secret' => 'OGU4Yjg1ZDMtNWI5NS00',
    'site_id' => 174,
  ],
  'aeokw_uat' => [
    'hmac_id' => 'afb8c24dfb574debaebfe6b680a9fc43',
    'hmac_secret' => 'MjU1NmIzMjYtNDc1My00',
    'site_id' => 191,
  ],
  'aeokw_prod' => [
    'site_id' => 205,
  ],
  // American Eagle Outfitters QA.
  'aeoqa_dev3' => [
    'hmac_id' => 'ecd62d5ab65a4a4aa1f11321460445b3',
    'hmac_secret' => 'ODNkNDA0MjctZTkyYy00',
    'site_id' => 457,
  ],
  'aeoqa_training_dev2' => [
    'hmac_id' => 'fb3728b1ba9d4fdcbc55492be69e48c5',
    'hmac_secret' => 'YmU1YzM4Y2YtMDY2Yi00',
    'site_id' => 486,
  ],
  'aeoqa_qa' => [
    'hmac_id' => '7203b241292f4d2db96141d4fbd16adc',
    'hmac_secret' => 'NzM4YjQ1NDctMDU0Yy00',
    'site_id' => 344,
  ],
  'aeoqa_dev2' => [
    'hmac_id' => '05fc397d271048bfa482c12a54d20d98',
    'hmac_secret' => 'MDFlYTlhYzUtNGVmMy00',
    'site_id' => 370,
  ],
  'aeoqa_uat' => [
    'hmac_id' => '713844a9cc5c4b00b4fe0a21c32362ba',
    'hmac_secret' => 'MTY3MjFjN2EtY2UwOS00',
    'site_id' => 303,
  ],
  'aeoqa_prod' => [
    'site_id' => 294,
  ],
  // American Eagle Outfitters SA.
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
  'aeosa_qa' => [
    'hmac_id' => 'e6b1671eda3b442f9119045676199d93',
    'hmac_secret' => 'NTg3N2Q3NWItMjJhOC00',
    'site_id' => 175,
  ],
  'aeosa_uat' => [
    'hmac_id' => '8d9331c4d4aa4a529ed05055b3ca5e67',
    'hmac_secret' => 'ZGJmMWFiYTktY2JiNS00',
    'site_id' => 192,
  ],
  'aeosa_prod' => [
    'site_id' => 206,
  ],
  // American Eagle Outfitters XB.
  'aeoxb_dev3' => [
    'hmac_id' => '880da77391f0495db0fde6c5ba223ed4',
    'hmac_secret' => 'YzI3YzlmOTgtMDA2OC00',
    'site_id' => 574,
  ],
  'aeoxb_test' => [
    'hmac_id' => '1529e8ec8e2b4cb0a2813d88208ce033',
    'hmac_secret' => 'ZjA4NTc0MWUtYTNiNi00',
    'site_id' => 575,
  ],
  'aeoxb_uat' => [
    'hmac_id' => 'ad8b871b3ea747978b4d16bde2abf3ae',
    'hmac_secret' => 'NzJlNzVkOWUtYmQwZi00',
    'site_id' => 617,
  ],
  'aeoxb_pprod' => [
    'hmac_id' => '5eeea48df9a4446c8ca94f932b49d610',
    'hmac_secret' => 'YjQxY2FiODYtZDE2ZC00',
    'site_id' => 621,
  ],
  'aeoxb_prod' => [
    'site_id' => 620,
  ],
  // Muji
  'muae_dev' => [
    'hmac_id' => 'fa9f4aee73dd4acc93bb4f8457cc8486',
    'hmac_secret' => 'NzE5MDQ2NmYtNTg3My00',
    'site_id' => 240,
  ],
  'muqa_dev3' => [
    'hmac_id' => 'b97775529e1942578f1d1aeec095ccc5',
    'hmac_secret' => 'YTlmZTk4NmMtYzgzYS00',
    'site_id' => 504,
  ],
  'mukw_qa' => [
    'hmac_id' => '4574965065ca4b828121e43a3c39d56d',
    'hmac_secret' => 'ZDk0ODMyZjYtNTI0OC00',
    'site_id' => 577,
  ],
  'musa_qa' => [
    'hmac_id' => '144375ad19c74c59a1819925b98dad2c',
    'hmac_secret' => 'ZDM1YmUyYmQtN2ExNC00',
    'site_id' => 578,
  ],
  'muae_qa' => [
    'hmac_id' => 'cb9ae3c76d62408b937bf0466dd1f77f',
    'hmac_secret' => 'OGFjNjhkODktYTg2ZS00',
    'site_id' => 579,
  ],
  'mueg_qa' => [
    'hmac_id' => '56b0c8589b2f420b9f18ce3306a837c1',
    'hmac_secret' => 'MWU1OGFkODktNDY1Mi00',
    'site_id' => 580,
  ],
  'muqa_qa' => [
    'hmac_id' => 'cf00dfc95ea94d7487647e10699548c5',
    'hmac_secret' => 'MWUwOGEwYjMtYTQyNy00',
    'site_id' => 581,
  ],
  'mubh_qa' => [
    'hmac_id' => 'f24020c52b2a4f30bfb835d367781680',
    'hmac_secret' => 'ODdlZTc5YzItYTBmNC00',
    'site_id' => 582,
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
  'bpbh_dev' => [
    'hmac_id' => 'f8c6c51510f64fbb867b700851e4c135',
    'hmac_secret' => 'ZTdmODYwYzEtODdjNC00',
    'site_id' => 463,
  ],
  'bpqa_dev' => [
    'hmac_id' => '35ff77c373814d0681fde6696cfdbbd0',
    'hmac_secret' => 'YjQ2YWM3MWYtNjE1MC00',
    'site_id' => 464,
  ],
  'bpqa_dev3' => [
    'hmac_id' => 'efec0de4b51146fea9309e409002fd7b',
    'hmac_secret' => 'NGM3ZDcxMWYtYzE5MS00',
    'site_id' => 488,
  ],
  'bpae_qa2' => [
    'hmac_id' => '3a0a7949099b4370a5af0fb0ddc06398',
    'hmac_secret' => 'ZTI2MzAwYWMtNDVjNi00',
    'site_id' => 431,
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
  'tbsae_dev' => [
    'hmac_id' => '6e1da4b4ebf848d0bd9a509588eea524',
    'hmac_secret' => 'OTFmM2MyMzQtMWIzOC00',
    'site_id' => 417,
  ],
  'tbsae_qa' => [
    'hmac_id' => '42b2b7d9dcf040468474bd7f9cab6a3e',
    'hmac_secret' => 'ODgzZTQwOTctYWIzNi00',
    'site_id' => 416,
  ],
  'tbskw_dev' => [
    'hmac_id' => 'abfb1e03840d440593a34d9e178c167c',
    'hmac_secret' => 'OGQ4YmFiNDUtOTg2Ni00',
    'site_id' => 418,
  ],
  'tbskw_qa' => [
    'hmac_id' => 'fdd20ee3159541e084e01ed0e0f44917',
    'hmac_secret' => 'MDVlNTVhZWQtMjQ1Yy00',
    'site_id' => 419,
  ],
  'tbssa_qa' => [
    'hmac_id' => 'c93ef02e4cd5476492e3107026c1da17',
    'hmac_secret' => 'ZTY0ZGVlMjctMGQxZS00',
    'site_id' => 452,
  ],
  'tbseg_qa' => [
    'hmac_id' => '98a3a7e553c4401e89833b536599f766',
    'hmac_secret' => 'NWMwYjBkZjgtOWY4Yi00',
    'site_id' => 453,
  ],
  'tbsbh_qa' => [
    'hmac_id' => 'e10ee5b7d9464901a5abe3409ce95203',
    'hmac_secret' => 'OThlNTM3MjctY2NjNC00',
    'site_id' => 454,
  ],
  'tbsqa_qa' => [
    'hmac_id' => '724f03c6febd436c94e49a00635b6ed0',
    'hmac_secret' => 'MThmYjc5ZDEtOTAwNS00',
    'site_id' => 455,
  ],
  'tbsjo_qa' => [
    'hmac_id' => 'e728bedef5b34a0dba02818960876e7a',
    'hmac_secret' => 'NWNkMjljMmYtN2Y4Zi00',
    'site_id' => 456,
  ],
  'tbskw_test' => [
    'hmac_id' => '1b342040550c4d2fad859d152e983f9d',
    'hmac_secret' => 'N2VkYTJkNjktMWZjYy00',
    'site_id' => 529,
  ],
  'tbssa_test' => [
    'hmac_id' => '2ccc1992b6bf4b0b8e7f17a6de33ebd6',
    'hmac_secret' => 'NTMzNGM1YjUtNDAzNC00',
    'site_id' => 530,
  ],
  'tbsae_test' => [
    'hmac_id' => '710c8a05f6274c10a43d80805ed32462',
    'hmac_secret' => 'ZmI3OGVlYjAtMjhhZS00',
    'site_id' => 531,
  ],
  'tbseg_test' => [
    'hmac_id' => '2620eebf85b84583af8f8807877454ca',
    'hmac_secret' => 'ZDE3MzE2YWYtOWI4ZC00',
    'site_id' => 532,
  ],
  'tbsbh_test' => [
    'hmac_id' => 'ec84a65636784d5689bc7f29cafbfa0e',
    'hmac_secret' => 'MzI4M2E4MjEtZGNiNy00',
    'site_id' => 533,
  ],
  'tbsqa_test' => [
    'hmac_id' => 'e23e15b037714753a0419245ac1544a2',
    'hmac_secret' => 'Y2FhZWUzNmEtYjU3My00',
    'site_id' => 534,
  ],
  'tbsjo_test' => [
    'hmac_id' => '692b7fd03fde4416ae7d9426ead0db9a',
    'hmac_secret' => 'ZmYxMmZkMDYtYjRlMy00',
    'site_id' => 535,
  ],
  'tbskw_uat' => [
    'hmac_id' => 'cbe72e31734d430c9d38749329f2d68d',
    'hmac_secret' => 'OTliMTUzZmMtZWUzNi00',
    'site_id' => 420,
  ],
  'tbssa_uat' => [
    'hmac_id' => '16004459a2de430ebe8345e5350a1a32',
    'hmac_secret' => 'NDdmODllYjAtODI5Mi00',
    'site_id' => 436,
  ],
  'tbsae_uat' => [
    'hmac_id' => 'e2207bcfb2d2439ba73941401a2324ce',
    'hmac_secret' => 'YzllNjUxMTMtNjZhNS00',
    'site_id' => 437,
  ],
  'tbseg_uat' => [
    'hmac_id' => '7d7e86906cc24a039c36e0c6fb38f89b',
    'hmac_secret' => 'MTQyNjM0ZTMtMTUzNS00',
    'site_id' => 438,
  ],
  'tbsbh_uat' => [
    'hmac_id' => '019c9c4842ce4559aee260e5fabc8610',
    'hmac_secret' => 'ZThlNjA0MTMtZDk1NS00',
    'site_id' => 439,
  ],
  'tbsqa_uat' => [
    'hmac_id' => '5117c76ea9d14862a0a8ae3e9dd605fe',
    'hmac_secret' => 'NWExOTk5MzUtYTU0Zi00',
    'site_id' => 440,
  ],
  'tbsjo_uat' => [
    'hmac_id' => '8a02d84d513f48949dbf4f5f826bcaab',
    'hmac_secret' => 'ZmE2Y2UzZWEtMGUxMi00',
    'site_id' => 441,
  ],
  'tbskw_prod' => [
    'site_id' => 421,
  ],
  'tbssa_prod' => [
    'site_id' => 442,
  ],
  'tbsae_prod' => [
    'site_id' => 443,
  ],
  'tbseg_prod' => [
    'site_id' => 444,
  ],
  'tbsbh_prod' => [
    'site_id' => 445,
  ],
  'tbsqa_prod' => [
    'site_id' => 446,
  ],
  'tbsjo_prod' => [
    'site_id' => 447,
  ],
  'coskw_qa2' => [
    'hmac_id' => '5f86efc3a4034542a66ef2f4ab6ca44b',
    'hmac_secret' => 'MjJhYzlhNjUtZjk4OC00',
    'site_id' => 465,
  ],
  'cossa_qa2' => [
    'hmac_id' => '77459323245344598d6e771a238dcee3',
    'hmac_secret' => 'YmNmYzIwOGItYzNlZC00',
    'site_id' => 466,
  ],
  'cosae_qa2' => [
    'hmac_id' => 'ce5ce2adf272456a9a61af18ba6a77c9',
    'hmac_secret' => 'ODZmZDZmY2MtMWE2ZC00',
    'site_id' => 467,
  ],
  'coseg_qa2' => [
    'hmac_id' => 'c25fe14ca27846a79b7ebd6e921a4db8',
    'hmac_secret' => 'MWMyYTAzNzctMmE2MS00',
    'site_id' => 468,
  ],
  'cosbh_qa2' => [
    'hmac_id' => '199e202dee0346dda2d195f6937bb402',
    'hmac_secret' => 'N2Y5YTM5OWYtMzc1ZS00',
    'site_id' => 469,
  ],
  'cosqa_qa2' => [
    'hmac_id' => 'ac141132650249e99ceb581e96d4ad5e',
    'hmac_secret' => 'ZTZhYmM4ZmEtYTdkMC00',
    'site_id' => 470,
  ],
  'coskw_dev' => [
    'hmac_id' => 'ee759d2ed757406c8bff89d966108a96',
    'hmac_secret' => 'ODlkMWMzM2ItMGFlZi00',
    'site_id' => 476,
  ],
  'cossa_dev' => [
    'hmac_id' => '29f08b29a9ba421b90f5d956dd72c45d',
    'hmac_secret' => 'YzgyMmVmNDQtZTY3Ni00',
    'site_id' => 477,
  ],
  'cosae_dev' => [
    'hmac_id' => '89525fe8ad1548bd9f7802a74834f76b',
    'hmac_secret' => 'ZGQ2NTQ1ODktZWVlYi00',
    'site_id' => 478,
  ],
  'coseg_dev' => [
    'hmac_id' => '5d986f5559424e0893946f840ff7b0f2',
    'hmac_secret' => 'ZTgxYzUxNzgtMDNkNy00',
    'site_id' => 479,
  ],
  'cosbh_dev' => [
    'hmac_id' => 'e43f5565eede40a0b6553fff0c58d281',
    'hmac_secret' => 'YmE3ZGFiMzQtNThmYS00',
    'site_id' => 480,
  ],
  'cosqa_dev' => [
    'hmac_id' => 'dcecf7d0b2a74511bbf65681325abf50',
    'hmac_secret' => 'N2VhNGQwYzAtZGVmOS00',
    'site_id' => 481,
  ],
  'coskw_test' => [
    'hmac_id' => '2315be0692424a799d9710c3e662549b',
    'hmac_secret' => 'MTAwNzQ0MDEtOTM3Ni00',
    'site_id' => 560,
  ],
  'cossa_test' => [
    'hmac_id' => '24c54750492e43d09b7111bbefa4d08d',
    'hmac_secret' => 'ZWQ2MmM5MjktYTVjMC00',
    'site_id' => 561,
  ],
  'cosae_test' => [
    'hmac_id' => '6f2ba459bb64457eb0dd41470276fedd',
    'hmac_secret' => 'ZWMxYjI0ZGYtY2IyMy00',
    'site_id' => 562,
  ],
  'coseg_test' => [
    'hmac_id' => 'ab0111bc27414707855a7bd4a14a4f8e',
    'hmac_secret' => 'YTY5OGI1OWEtODFmMC00',
    'site_id' => 563,
  ],
  'cosbh_test' => [
    'hmac_id' => '42d2b2b884f14afab7399dd1aee5e428',
    'hmac_secret' => 'MGU1MTg2YWEtOWZlYy00',
    'site_id' => 564,
  ],
  'cosqa_test' => [
    'hmac_id' => '065ed7137ca949ad8ee2ac298b83ce0e',
    'hmac_secret' => 'NWMxMzQ0ZWUtN2E1NC00',
    'site_id' => 565,
  ],
  'coskw_uat' => [
    'hmac_id' => 'c3b4274eeb21451387723cc29799c749',
    'hmac_secret' => 'MDRkOGRkOWQtZDYyMy00',
    'site_id' => 490,
  ],
  'cossa_uat' => [
    'hmac_id' => '4b818394dea44850a0f751faf1118161',
    'hmac_secret' => 'MTdjMWJmZTktOWE1MS00',
    'site_id' => 491,
  ],
  'cosae_uat' => [
    'hmac_id' => '84803bb1fd764846a021f0a1a7d2619d',
    'hmac_secret' => 'ZTIyZDliZjktODQxYi00',
    'site_id' => 492,
  ],
  'coseg_uat' => [
    'hmac_id' => '80e344c3cdde4725b8b330ad1d7dcfef',
    'hmac_secret' => 'YzFjM2ZiNWUtZjU0Mi00',
    'site_id' => 493,
  ],
  'cosbh_uat' => [
    'hmac_id' => '7a1b8ccc4ada4d82b0b5fb38c33c6be5',
    'hmac_secret' => 'NWNhM2IyMTQtZDI1OS00',
    'site_id' => 494,
  ],
  'cosqa_uat' => [
    'hmac_id' => '2ba30adf80584a44b99d832de959cf7e',
    'hmac_secret' => 'NWM3YWEwMjQtOGFmNi00',
    'site_id' => 495,
  ],
  'coskw_prod' => [
    'site_id' => 497,
  ],
  'cossa_prod' => [
    'site_id' => 498,
  ],
  'cosae_prod' => [
    'site_id' => 499,
  ],
  'coseg_prod' => [
    'site_id' => 500,
  ],
  'cosbh_prod' => [
    'site_id' => 501,
  ],
  'cosqa_prod' => [
    'site_id' => 502,
  ],
  'cosjo_prod' => [
    'site_id' => 503,
  ],
  'dhkw_dev' => [
    'hmac_id' => '638f920998d848a29c35c0b943778b78',
    'hmac_secret' => 'ZDgyNDBiYTQtZjI2Yi00',
    'site_id' => 512,
  ],
  'dhsa_dev' => [
    'hmac_id' => '0ea10f093feb465fa8e654521ee8d4bd',
    'hmac_secret' => 'ZmQ2YTlhMTItNzc1YS00',
    'site_id' => 513,
  ],
  'dhae_dev' => [
    'hmac_id' => 'c76f0bef22764605ae8bdaa6036c25c8',
    'hmac_secret' => 'MGQzNjlhZDYtMTYxNS00',
    'site_id' => 514,
  ],
  'dheg_dev' => [
    'hmac_id' => '55d26e5023ec47dbb41892ff20f68d8e',
    'hmac_secret' => 'MjY4N2NiNGItZTIzOS00',
    'site_id' => 515,
  ],
  'dhbh_dev' => [
    'hmac_id' => '68797fb595b945a681362a5192417ffc',
    'hmac_secret' => 'ZjlkNGFiMGUtNGVmOS00',
    'site_id' => 516,
  ],
  'dhqa_dev' => [
    'hmac_id' => '97ed61cf86cc437785342156093439dc',
    'hmac_secret' => 'YzI1Y2FhMGEtZTRhYy00',
    'site_id' => 517,
  ],
  'dhjo_dev' => [
    'hmac_id' => '4fad6d9a131b40a9bd96535bed7e8d8c',
    'hmac_secret' => 'ZDU1MzA3MTYtZmFkMy00',
    'site_id' => 518,
  ],
  'dhkw_uat' => [
    'hmac_id' => '1e2c383e786c4ffd941120507d1478c7',
    'hmac_secret' => 'MGUyMTlkNzgtYTFlMC00',
    'site_id' => 505,
  ],
  'dhsa_uat' => [
    'hmac_id' => '99e41c22663c42509aac04b658aa5ba5',
    'hmac_secret' => 'N2JkYWRlN2ItMjM1My00',
    'site_id' => 506,
  ],
  'dhae_uat' => [
    'hmac_id' => '81c7c0d7854746eb8442818a2881d101',
    'hmac_secret' => 'NmRhNjk2NDEtMzU3MS00',
    'site_id' => 507,
  ],
  'dheg_uat' => [
    'hmac_id' => '3ca3b7d6963741f8809908d370290452',
    'hmac_secret' => 'YjIyNjRmNjctOTdkNS00',
    'site_id' => 508,
  ],
  'dhbh_uat' => [
    'hmac_id' => 'eb464fce240b4d0396ad895cb7a1bc27',
    'hmac_secret' => 'Njg2ZGFjNmMtOWYwNS00',
    'site_id' => 509,
  ],
  'dhqa_uat' => [
    'hmac_id' => '346c5692c04b4f8fadd4c554a06faa05',
    'hmac_secret' => 'NDRmNTU1Y2UtMjkwNC00',
    'site_id' => 510,
  ],
  'dhjo_uat' => [
    'hmac_id' => 'd7b914cce4b34fe99e48ca1871bb25d5',
    'hmac_secret' => 'OTc5OGM2ZTgtZDk0MC00',
    'site_id' => 511,
  ],
  'dhkw_prod' => [
    'site_id' => 519,
  ],
  'dhsa_prod' => [
    'site_id' => 520,
  ],
  'dhae_prod' => [
    'site_id' => 521,
  ],
  'dheg_prod' => [
    'site_id' => 522,
  ],
  'dhbh_prod' => [
    'site_id' => 523,
  ],
  'dhqa_prod' => [
    'site_id' => 524,
  ],
  'dhjo_prod' => [
    'site_id' => 525,
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
