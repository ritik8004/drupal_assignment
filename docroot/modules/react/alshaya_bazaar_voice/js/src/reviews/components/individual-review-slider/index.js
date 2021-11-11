import React from 'react';
import DynamicDot from '../dynamic-dot';
import getStringMessage from '../../../../../../js/utilities/strings';
import ConditionalView from '../../../common/components/conditional-view';

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
                :
                {' '}
              </span>
              <span className="slider-header-label">
                {
                  !(sliderData[item].ValueLabel) === true
                    ? getStringMessage('true_to_size')
                    : sliderData[item].ValueLabel
                }
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
