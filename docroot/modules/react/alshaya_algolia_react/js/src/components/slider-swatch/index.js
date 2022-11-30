import React from 'react';
import Slider from 'react-slick';
import { Swatch } from '../swatch';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isDesktop, isMobile } from '../../../../../js/utilities/display';

const SliderSwatch = ({ swatches, url, title }) => {
  if (!hasValue(swatches)) {
    return null;
  }

  const totalNoOfSwatches = swatches.length;
  const { currentLanguage } = drupalSettings.path;

  // Swatch display slides limit, defaults to desktop - 4.
  // Mobile - 2; Tablets - 3.
  let limit = parseInt(drupalSettings.reactTeaserView.swatches.swatchPlpLimit, 10);
  if (!isDesktop()) {
    limit -= isMobile() ? 2 : 1;
  }

  const sliderSettings = {
    infinite: false,
    slidesToShow: limit,
    slidesToScroll: limit,
    rtl: (currentLanguage === 'ar'),
  };

  let swatchContainer = null;
  if (totalNoOfSwatches > 0) {
    const swatchItems = swatches.map(
      (swatch) => <Swatch swatch={swatch} key={swatch.child_id} url={url} title={title} />,
    );
    let classSwatches = 'swatches';
    const hasSliderSwatch = totalNoOfSwatches > limit;
    if (hasSliderSwatch) {
      classSwatches += ' slider-swatches';
    }

    swatchContainer = (
      <div className={classSwatches}>
        {hasSliderSwatch
          ? (
            <Slider {...sliderSettings} className="swatch-slider">
              { swatchItems }
            </Slider>
          ) : swatchItems }
      </div>
    );
  }

  return (swatchContainer
    ? (
      <>
        {swatchContainer}
      </>
    )
    : null
  );
};

export default SliderSwatch;
