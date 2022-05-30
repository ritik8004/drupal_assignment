/**
 * Get the memberId with required format.
 */
const getFormatedMemberId = (memberId) => memberId.replace(/(\d{4})(\d{4})(\d{4})(\d{4})/, '$1 $2 $3 $4');

/**
 * Get the full name.
 */
const getFullName = (firsName, lastName) => `${Drupal.t('Hi')} ${firsName} ${lastName}`;

/**
 * Get percentage value for gathered points to show progress bar.
 */
const getPercentage = (memberPointsInfo) => {
  let getPointsPlus = '';
  let pointsGathered = '';
  memberPointsInfo.forEach((points) => {
    if (points.code === 'GET_PLUS') {
      getPointsPlus = points.value;
    } else if (points.code === 'POINTS_GATHERED') {
      pointsGathered = points.value;
    }
  });
  const percentVal = (pointsGathered / getPointsPlus) * 100;

  return percentVal.toFixed(2);
};

export {
  getFormatedMemberId,
  getFullName,
  getPercentage,
};
