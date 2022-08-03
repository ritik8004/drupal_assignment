<?php

namespace App\Helper;

/**
 * Helper class for handling appointments.
 *
 * @package App\Helper
 */
class Helper {

  /**
   * Group Store timings like: Sunday - Wednesday (10 AM - 10 PM) etc..
   *
   * @param array $weeklySchedules
   *   Array of store schedule.
   *
   * @return array
   *   Array of grouped store timing.
   */
  public function groupStoreTimings(array $weeklySchedules) {
    $weeklySchedulesData = $firstDay = $lastDay = [];

    foreach ($weeklySchedules as $weeklySchedule) {
      $weekDay = $weeklySchedule->weekDay ?? '';
      $startTime = $weeklySchedule->localStartTime ?? '';
      $endTime = $weeklySchedule->localEndTime ?? '';
      // 24-hour time to 12-hour time
      $timeSlot = date("g:i a", strtotime($startTime)) . ' - ' . date("g:i a", strtotime($endTime));
      $schedule = [
        'day' => $weekDay,
        'time' => $timeSlot,
      ];

      // Group Store timings.
      if (empty($firstDay)) {
        $firstDay = $schedule;
      }
      elseif (empty($lastDay)) {
        $lastDay = $schedule;
      }
      elseif ($timeSlot === $lastDay['time']) {
        $lastDay = $schedule;
      }
      else {
        // Current timeslot is different so store the first and
        // last day schedule in main array and create a new first and last day.
        $weeklySchedulesData[] = [
          'day' => $firstDay['day'] . ' - ' . $lastDay['day'],
          'timeSlot' => $firstDay['time'],
        ];
        $firstDay = $schedule;
        $lastDay = [];
      }
    }

    // Store the last value of firstDay and lastDay in the
    // main array of store schedule.
    if (!empty($firstDay)) {
      if (!empty($lastDay)) {
        $weeklySchedulesData[] = [
          'day' => $firstDay['day'] . ' - ' . $lastDay['day'],
          'timeSlot' => $firstDay['time'],
        ];
      }
      else {
        $weeklySchedulesData[] = [
          'day' => $firstDay['day'],
          'timeSlot' => $firstDay['time'],
        ];
      }
    }

    return $weeklySchedulesData;
  }

  /**
   * Calculate distance between 2 coordinates.
   *
   * @param string $lat1
   *   Latitude 1.
   * @param string $lon1
   *   Longitude 1.
   * @param string $lat2
   *   Latitude 2.
   * @param string $lon2
   *   Longitude 2.
   * @param string $unit
   *   Unit of the distance.
   *
   * @return string
   *   Distance (Default distance unit is miles).
   */
  public function distance($lat1, $lon1, $lat2, $lon2, $unit) {
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
      return 0;
    }
    else {
      $latFrom = deg2rad($lat1);
      $lonFrom = deg2rad($lon1);
      $latTo = deg2rad($lat2);
      $lonTo = deg2rad($lon2);

      $latDelta = $latTo - $latFrom;
      $lonDelta = $lonTo - $lonFrom;

      $angle = 2 * asin(sqrt(sin($latDelta / 2) ** 2 +
        cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2));
      $distance = $angle * 3959;

      if ($unit === "kilometers") {
        $distance *= 1.609344;
      }

      return number_format((float) $distance, 2, '.', '');
    }
  }

}
