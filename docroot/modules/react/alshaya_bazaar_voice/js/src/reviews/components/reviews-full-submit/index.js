import React from 'react';
import Popup from 'reactjs-popup';
import ClosedReviewSubmit from './closed-review-submit';
import WithModal from './with-modal';
import WriteReviewForm from './WriteReviewForm';

export default class WriteReviewButton extends React.Component {
  openModal = (callback) => {
    callback();
  }

  closeModal = (callback) => {
    callback();
  };

  render() {
    if (drupalSettings.user.user_id === 0
      && drupalSettings.bazaar_voice.write_review_submission) {
      return (
        <ClosedReviewSubmit destination={drupalSettings.product.url} />
      );
    }
    return (
      <WithModal>
        {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
          <div className="pdp-write-review">
            <div onClick={() => this.openModal(triggerOpenModal)} className="pdp-write-review-text">
              {Drupal.t('Write a review')}
            </div>
            <Popup
              open={isModalOpen}
              className="write_review"
              closeOnEscape={false}
              closeOnDocumentClick={false}
            >
              <WriteReviewForm
                closeModal={() => this.closeModal(triggerCloseModal)}
              />
            </Popup>
          </div>
        )}
      </WithModal>
    );
  }
}
