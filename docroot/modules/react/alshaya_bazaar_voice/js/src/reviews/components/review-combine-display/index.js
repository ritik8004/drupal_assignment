import React from 'react';
import IndividualReviewSlider from '../individual-review-slider';
import IndividualReviewStar from '../individual-review-star';

const CombineDisplay = ({
  starSliderCombine,
  secondaryRatingsOrder,
}) => (
  <div className="overall-product-rating">
    <IndividualReviewSlider
      sliderData={starSliderCombine}
      secondaryRatingsOrder={secondaryRatingsOrder}
    />

    <IndividualReviewStar
      customerValue={starSliderCombine}
      secondaryRatingsOrder={secondaryRatingsOrder}
    />
  </div>
);

export default CombineDisplay;
