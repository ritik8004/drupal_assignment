import React from 'react';
import isRTL from '../../../utilities/rtl';

const IndividualReviewSlider = ({
  sliderData,
}) => {
  const direction = isRTL() === true ? 'rtl' : 'ltr';
  if (sliderData === null) {
    return null;
  }
  return (
    <>
      {Object.keys(sliderData).map((item) => (((sliderData[item].DisplayType) === 'SLIDER') === true
        ? (
          <div className="attribute-list" key={item}>
            <div className="slider-header">
              <span>
                {sliderData[item].Label}
                :
                {' '}
              </span>
              <span className="slider-header-label">
                {
                  !(sliderData[item].ValueLabel) === true
                    ? Drupal.t('True to size')
                    : sliderData[item].ValueLabel
                }
              </span>
            </div>
            <div className="display-slider">
              <div className="slider-label">{sliderData[item].MinLabel}</div>
              <div className="slide-dot-container">
                <div className="slide-dot" />
                <div className="slide-dot" />
                <div className="slide-dot" />
                <div className="slide-dot" />
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
        )
        : null))}
    </>
  );
};

export default IndividualReviewSlider;
