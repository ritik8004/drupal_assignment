import React from 'react';
import IndividualReviewSlider from '../individual-review-slider';
import IndividualReviewStar from '../individual-review-star';

const CombineDisplay = ({
  starSliderCombine,
}) => {
  return (
    <div className="overall-product-rating">
      <IndividualReviewSlider
        sliderData={starSliderCombine}
      />

      <IndividualReviewStar
        customerValue={starSliderCombine}
      />
    </div>
  );
}

export default CombineDisplay;
