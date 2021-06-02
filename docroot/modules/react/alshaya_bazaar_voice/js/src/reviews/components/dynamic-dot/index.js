import React from 'react';
import isRTL from '../../../utilities/rtl';
import { getPercentVal } from '../../../utilities/validate';

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
          style={{ right: `${(sliderAverageRating > 0) ? getPercentVal(sliderAverageRating, sliderValueRange) : null}%` }}
        />
      )
      : (
        <div
          className={`dynamic-dot slide-range-${sliderValueRange} ${(sliderValue > 0) ? `dot-${sliderValue}` : ''}`}
          style={{ left: `${(sliderAverageRating > 0) ? getPercentVal(sliderAverageRating, sliderValueRange) : null}%` }}
        />
      )}
  </>
);

export default DynamicDot;
