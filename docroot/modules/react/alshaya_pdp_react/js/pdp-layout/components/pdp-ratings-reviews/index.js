import React, { useEffect } from 'react';
import { closeModalHelper } from '../../../utilities/pdp_layout';
import Rating from '../../../../../alshaya_bazaar_voice/js/src/rating/components/rating';
import ReviewSummary from '../../../../../alshaya_bazaar_voice/js/src/reviews/components/review-summary'

const PpdRatingsReviews = (props) => {
  const {
    getPanelData,
  } = props;

  const closeModal = () => {
    document.querySelector('body').classList.remove('ratings-reviews-overlay');
    setTimeout(() => {
      removePanelData();
    }, 400);
  };

  const openModal = () => {
    // to make sure that markup is present in DOM.
    setTimeout(() => {
      document.querySelector('body').classList.add('ratings-reviews-overlay');
    }, 150);
    return (
      <div id="reviews-section">
        <ReviewSummary />
      </div>
    );
  };

  const myFn = (e) => {
    getPanelData(openModal());
  }

  useEffect(() => {
    closeModalHelper('ratings-reviews-overlay', 'reviews-wrapper', closeModal);
  },
  []);

  return (
    <Rating childClickHandler={myFn}/>
  );
};

export default PpdRatingsReviews;
