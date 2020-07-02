<?php

namespace App\Helper;

/**
 * Class Helper.
 *
 * @package App\Helper
 */
class Helper {

  /**
   * Group Store timings like: Sunday - Wednesday (10 AM - 10 PM) etc..
   *
   * @param string $weeklySchedules
   *   Array of store schedule.
   *
   * @return array
   *   Array of grouped store timing.
   */
  public function groupStoreTimings($weeklySchedules) {
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

}
