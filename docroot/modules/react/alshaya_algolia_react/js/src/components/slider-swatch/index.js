import React from 'react';
import Slider from 'react-slick';
import { Swatch } from '../swatch';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isDesktop, isMobile } from '../../../../../js/utilities/display';

const SliderSwatch = ({
  swatches,
  url,
  title,
  handleSwatchSelect,
}) => {
  if (!hasValue(swatches)) {
    return null;
  }

  const totalNoOfSwatches = swatches.length;

  // Swatch display slides limit, defaults to desktop - 4.
  // Mobile - 2; Tablets - 3.
  let scrollLimit = parseInt(drupalSettings.reactTeaserView.swatches.swatchPlpLimit, 10);
  let showLimit = scrollLimit;

  if (!isDesktop()) {
    if (isMobile()) {
      scrollLimit -= 2;
      showLimit = scrollLimit + 0.5;
    } else {
      scrollLimit -= 1;
      showLimit = scrollLimit;
    }
  }

  const sliderSettings = {
    infinite: false,
    slidesToShow: showLimit,
    slidesToScroll: scrollLimit,
  };

  let swatchContainer = null;
  if (totalNoOfSwatches > 0) {
    const swatchItems = swatches.map(
      (swatch) => (
        <Swatch
          swatch={swatch}
          key={swatch.child_id}
          url={url}
          title={title}
          handleSwatchSelect={handleSwatchSelect}
        />
      ),
    );
    let classSwatches = 'swatches';
    const hasSliderSwatch = totalNoOfSwatches > showLimit;
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
