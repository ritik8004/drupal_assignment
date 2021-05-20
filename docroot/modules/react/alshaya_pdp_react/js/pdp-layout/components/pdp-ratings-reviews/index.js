import React, { useEffect } from 'react';
import { closeModalHelper } from '../../../utilities/pdp_layout';
import Rating from '../../../../../alshaya_bazaar_voice/js/src/rating/components/rating';
import ReviewSummary from '../../../../../alshaya_bazaar_voice/js/src/reviews/components/review-summary';
import { setStorageInfo } from '../../../../../alshaya_bazaar_voice/js/src/utilities/storage';
import { isOpenWriteReviewForm } from '../../../../../alshaya_bazaar_voice/js/src/utilities/user_util';

const PpdRatingsReviews = (props) => {
  const {
    getPanelData, removePanelData,
  } = props;

  const closeModal = () => {
    document.querySelector('body').classList.remove('ratings-reviews-overlay');
    document.querySelector('body').classList.remove('open-form-modal');
    setTimeout(() => {
      removePanelData();
    }, 400);
    // Disable write review popup on page load.
    setStorageInfo(false, 'openPopup');
  };

  const openModal = () => {
    // to make sure that markup is present in DOM.
    setTimeout(() => {
      document.querySelector('body').classList.add('ratings-reviews-overlay');
    }, 150);
    return (
      <div id="reviews-section" className="magv2-ratings-reviews-popup-container">
        <div className="magv2-ratings-reviews-popup-wrapper">
          <div className="magv2-ratings-reviews-popup-header-wrapper">
            <a className="close" onClick={() => closeModal()}>×</a>
            <div className="magv2-ratings-reviews-popup-title">{Drupal.t('Ratings & Reviews')}</div>
          </div>
          <div className="magv2-ratings-reviews-popup-content-wrapper">
            <ReviewSummary isNewPdpLayout="true" />
          </div>
        </div>
      </div>
    );
  };

  const openRatingsReviewPanel = () => {
    getPanelData(openModal());
  };

  useEffect(() => {
    closeModalHelper('ratings-reviews-overlay', 'reviews-wrapper', closeModal);
    // To open write a review on page load.
    if (isOpenWriteReviewForm()) {
      getPanelData(openModal());
    }
  },
  []);

  return (
    <Rating childClickHandler={openRatingsReviewPanel} />
  );
};

export default PpdRatingsReviews;
