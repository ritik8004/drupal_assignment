/**
 * Set up accordion container height.
 */
const setupAccordionHeight = (ref) => {
  if (ref.current !== null) {
    const element = ref.current;
    element.style.maxHeight = `${ref.current.offsetHeight}px`;
  }
};

export default setupAccordionHeight;

/**
 * Update the memberId with required format (4343 6443 6554 2322).
 */
const getFormatedMemberId = (memberId) => memberId.replace(/(\d{4})(\d{4})(\d{4})(\d{4})/, '$1 $2 $3 $4');

/**
 * Get the points data to be used in point summary block.
 */
const getPointsData = (currentTier, memberPointsInfo) => {
  let totalPoints = 0;
  let getPlusPoints = 0;
  let newVoucherPoints = 0;
  const pointsData = {};
  // Get points based on points code.
  memberPointsInfo.forEach((points) => {
    if (points.code === 'GET_PLUS') {
      getPlusPoints = parseInt(points.value, 10);
    } else if (points.code === 'POINTS_GATHERED') {
      pointsData.pointsGathered = parseInt(points.value, 10);
    } else if (points.code === 'NEW_VOUCHER') {
      newVoucherPoints = parseInt(points.value, 10);
    }
  });
  // Get total/reached points based on current tier type
  // to calculate percentage for points bar.
  if (currentTier === 'Hello') {
    totalPoints = getPlusPoints + pointsData.pointsGathered;
  } else if (currentTier === 'Plus') {
    totalPoints = newVoucherPoints * 50;
  }

  pointsData.pointsGatheredInPercent = ((pointsData.pointsGathered / totalPoints) * 100).toFixed(2);

  return pointsData;
};

export {
  getFormatedMemberId,
  getPointsData,
};
