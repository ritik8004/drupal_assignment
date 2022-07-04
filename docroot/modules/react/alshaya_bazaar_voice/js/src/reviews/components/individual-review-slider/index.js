import React from 'react';
import DynamicDot from '../dynamic-dot';
import ConditionalView from '../../../common/components/conditional-view';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

const IndividualReviewSlider = ({
  sliderData,
  secondaryRatingsOrder,
}) => {
  if (sliderData === null) {
    return null;
  }
  return (
    <>
      {secondaryRatingsOrder.map((item) => (
        <ConditionalView key={sliderData[item].Id} condition={sliderData[item].DisplayType === 'SLIDER'}>
          <div className="attribute-list">
            <div className="slider-header">
              <span>
                {sliderData[item].Label}
              </span>
              <span className="slider-header-label">
                <ConditionalView condition={!hasValue(sliderData[item].AverageRating)}>
                  :
                  {' '}
                  {sliderData[item].ValueLabel}
                </ConditionalView>
                <ConditionalView condition={hasValue(sliderData[item].AverageRating)}>
                  {(sliderData[item].ValueLabel)
                    ? `: ${sliderData[item].ValueLabel[(Math.round(sliderData[item].AverageRating) - 1)]}`
                    : ''}
                </ConditionalView>
              </span>
            </div>
            <div className="display-slider">
              <div className="slider-label">{sliderData[item].MinLabel}</div>
              <div className="slide-dot-container">
                {
                  (sliderData[item].ValueRange) === 3
                    ? (
                      <>
                        <div className={`slide-dot slide-range-${sliderData[item].ValueRange}`} />
                        <div className={`slide-dot slide-range-${sliderData[item].ValueRange}`} />
                      </>
                    )
                    : (
                      <>
                        <div className="slide-dot" />
                        <div className="slide-dot" />
                        <div className="slide-dot" />
                        <div className="slide-dot" />
                      </>
                    )
                }
                <DynamicDot
                  sliderValue={sliderData[item].Value}
                  sliderValueRange={sliderData[item].ValueRange}
                  sliderAverageRating={sliderData[item].AverageRating}
                />
              </div>
              <div className="slider-label">{sliderData[item].MaxLabel}</div>
            </div>
          </div>
        </ConditionalView>
      ))}
    </>
  );
};

export default IndividualReviewSlider;
