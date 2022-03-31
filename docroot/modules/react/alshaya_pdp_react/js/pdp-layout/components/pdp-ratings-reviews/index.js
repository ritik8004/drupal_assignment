import React, { useEffect } from 'react';
import Rating from '../../../../../alshaya_bazaar_voice/js/src/rating/components/rating';
import ReviewSummary from '../../../../../alshaya_bazaar_voice/js/src/reviews/components/review-summary';
import { isOpenWriteReviewForm } from '../../../../../alshaya_bazaar_voice/js/src/utilities/user_util';
import { trackPassiveAnalytics } from '../../../../../alshaya_bazaar_voice/js/src/utilities/analytics';

const PpdRatingsReviews = (props) => {
  const {
    getPanelData, removePanelData,
  } = props;

  const closeModal = () => {
    document.querySelector('body').classList.remove('ratings-reviews-overlay', 'open-form-modal');
    setTimeout(() => {
      removePanelData();
    }, 400);
    // Disable write review popup on page load.
    Drupal.addItemInLocalStorage('openPopup', false);
  };

  const openModal = (isWriteReview) => {
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
            <ReviewSummary isNewPdpLayout="true" isWriteReview={isWriteReview} />
          </div>
        </div>
      </div>
    );
  };

  const openRatingsReviewPanel = (e, form) => {
    e.preventDefault();
    if (form === 'write_review') {
      getPanelData(openModal(true));
    } else {
      getPanelData(openModal(false));
    }
  };

  useEffect(() => {
    // To open write a review on page load.
    isOpenWriteReviewForm().then((status) => {
      if (status) {
        getPanelData(openModal());
      }
    });
  },
  []);

  // Track passive impression for dynamic layout on pdp.
  trackPassiveAnalytics();

  return (
    <Rating childClickHandler={openRatingsReviewPanel} renderLinkDirectly="true" />
  );
};

export default PpdRatingsReviews;
