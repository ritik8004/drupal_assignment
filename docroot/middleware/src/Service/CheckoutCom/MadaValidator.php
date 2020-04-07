<?php

namespace App\Service\CheckoutCom;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class MadaValidator.
 *
 * @package App\Service\CheckoutCom
 */
class MadaValidator {

  // Mada bins file name.
  const MADA_BINS_FILE = 'mada_bins.csv';

  // Mada bins test file name.
  const MADA_BINS_FILE_TEST = 'mada_bins_test.csv';

  /**
   * MadaValidator constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $params
   *   Parameter Bag.
   */
  public function __construct(ParameterBagInterface $params) {
    $this->params = $params;
  }

  /**
   * Checks if the given bin is belong to mada bin.
   *
   * @param bool $is_live
   *   Flag to specify is application is in live mode or test.
   * @param string $bin
   *   The card bin to verify.
   *
   * @return bool
   *   Return true if one of the mada bin, false otherwise.
   */
  public function isMadaBin(bool $is_live, string $bin) {
    // @TODO: Future - replace this with Magento API call.
    // They have developed one for Mobile APP.
    $mada_bin_csv_path = $this->params->get('kernel.project_dir') . '/files/';
    $mada_bin_csv_path .= $is_live ? self::MADA_BINS_FILE : self::MADA_BINS_FILE_TEST;

    // Read CSV rows.
    $mada_bin_csv_file = fopen($mada_bin_csv_path, 'r');

    $mada_bin_csv_data = [];
    while ($mada_bin_csv_row = fgetcsv($mada_bin_csv_file)) {
      $mada_bin_csv_data[] = $mada_bin_csv_row;
    }
    fclose($mada_bin_csv_file);

    // Remove the first row of csv columns.
    unset($mada_bin_csv_data[0]);

    // Build the mada bin array.
    $mada_bin_array = array_map(function ($row) {
      return $row[1];
    }, $mada_bin_csv_data);

    return in_array($bin, $mada_bin_array);
  }

}
