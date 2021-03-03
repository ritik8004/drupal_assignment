import React from 'react';
import Popup from 'reactjs-popup';
import ClosedReviewSubmit from './closed-review-submit';
import WithModal from './with-modal';
import WriteReviewForm from './WriteReviewForm';
import { getbazaarVoiceSettings } from '../../../utilities/api/request';

export default class WriteReviewButton extends React.Component {
  openModal = (callback) => {
    callback();
  }

  closeModal = (callback) => {
    callback();
  };

  render() {
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    if (bazaarVoiceSettings.reviews.user.user_id === 0
      && bazaarVoiceSettings.reviews.bazaar_voice.write_review_submission) {
      return (
        <ClosedReviewSubmit destination={bazaarVoiceSettings.reviews.product.url} />
      );
    }
    return (
      <WithModal>
        {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
          <div className="button-wrapper">
            <div onClick={() => this.openModal(triggerOpenModal)} className="write-review-button">
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
