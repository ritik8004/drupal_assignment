import React from 'react';
import Slider from 'react-slick';
import { Swatch } from '../swatch';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const SliderSwatch = ({ swatches, url }) => {
  if (!hasValue(swatches)) {
    return null;
  }

  // Number of swatches.
  const totalNoOfSwatches = swatches.length;
  // Display the configured number of swatches.
  const limit = drupalSettings.reactTeaserView.swatches.swatchPlpLimit;

  const sliderSettings = {
    infinite: false,
    slidesToShow: limit,
    slidesToScroll: limit,
  };

  let swatchContainer = null;
  if (totalNoOfSwatches > 0) {
    const swatchItems = swatches.map(
      (swatch) => <Swatch swatch={swatch} key={swatch.child_id} url={url} />,
    );
    swatchContainer = (
      <div className="swatches">
        {totalNoOfSwatches > 4
          ? (
            <Slider {...sliderSettings} className="search-lightSlider">
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
