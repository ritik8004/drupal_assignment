import React from 'react';
import isRTL from '../../../utilities/rtl';

const DynamicDot = ({
  sliderValue,
  sliderValueRange,
  sliderAverageRating,
}) => (
  <>
    {isRTL()
      ? (
        <div
          className={`dynamic-dot slide-range-${sliderValueRange} ${(sliderValue > 0) ? `dot-${sliderValue}` : ''}`}
          style={{ right: `${(sliderAverageRating > 0) ? (sliderAverageRating / sliderValueRange) * 100 : null}%` }}
        />
      )
      : (
        <div
          className={`dynamic-dot slide-range-${sliderValueRange} ${(sliderValue > 0) ? `dot-${sliderValue}` : ''}`}
          style={{ left: `${(sliderAverageRating > 0) ? (sliderAverageRating / sliderValueRange) * 100 : null}%` }}
        />
      )}
  </>
);

export default DynamicDot;
