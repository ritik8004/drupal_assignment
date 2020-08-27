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
  'mcae_dev2' => [
    'hmac_id' => '3063b30c2b3e401bafe0dfe0f1611da5',
    'hmac_secret' => 'NzY3NDg4NjctOTk2Mi00',
    'site_id' => 221,
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
    'hmac_id' => '875cffda19304dfe88ae6aaadb065909',
    'hmac_secret' => 'NTNiZTAwYTQtYzNkNi00',
    'site_id' => 199,
  ],
  'hmkw_dev2' => [
    'hmac_id' => 'bb8596447880451fa689e65e53506429',
    'hmac_secret' => 'NjE4NDYzYmMtNWRmNy00',
    'site_id' => 109,
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
    'hmac_id' => '1b6702ad12f244388b39c788794a88bb',
    'hmac_secret' => 'ZmMzZWFlZTItM2ViZi00',
    'site_id' => 200,
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
    'hmac_id' => '206ce89993554896924cdeefd32135c3',
    'hmac_secret' => 'MWFjOTYzNjctNzYzOS00',
    'site_id' => 201,
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
    'site_id' => 153,
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
  // BathBodyWorks Egypt.
  'bbweg_qa' => [
    'hmac_id' => '4d7962f023c54bbdb9ea5b6811cbb5af',
    'hmac_secret' => 'MWRmZGI4ZTMtYzM0MS00',
    'site_id' => 281,
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
