import React from 'react';
import isRTL from '../../../utilities/rtl';
import getStringMessage from '../../../../../../js/utilities/strings';
import ConditionalView from '../../../common/components/conditional-view';

const IndividualReviewSlider = ({
  sliderData,
  secondaryRatingsOrder,
}) => {
  const direction = isRTL() === true ? 'rtl' : 'ltr';
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
                {
                  (direction === 'rtl')
                    ? (
                      <div
                        className="dynamic-dot"
                        style={{ right: `${((((sliderData[item].Value > 0) ? sliderData[item].Value : sliderData[item].AverageRating) / sliderData[item].ValueRange).toFixed(1)) * 100}%` }}
                      />
                    )
                    : (
                      <div
                        className="dynamic-dot"
                        style={{ left: `${((((sliderData[item].Value > 0) ? sliderData[item].Value : sliderData[item].AverageRating) / sliderData[item].ValueRange).toFixed(1)) * 100}%` }}
                      />
                    )
                }
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
