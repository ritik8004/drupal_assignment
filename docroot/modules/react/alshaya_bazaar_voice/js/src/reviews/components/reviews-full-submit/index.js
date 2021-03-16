import React from 'react';
import Popup from 'reactjs-popup';
import WriteReviewForm from './WriteReviewForm';
import smoothScrollTo from '../../../utilities/smoothScroll';
import ClosedReviewSubmit from './closed-review-submit';
import { getbazaarVoiceSettings } from '../../../utilities/api/request';

export default class WriteReviewButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
    };
  }

  openModal = (e) => {
    e.preventDefault();

    this.setState({
      isModelOpen: true,
    });
  };

  closeModal = (e) => {
    e.preventDefault();

    this.setState({
      isModelOpen: false,
    });

    if (e.detail.HasErrors !== undefined && !e.detail.HasErrors) {
      smoothScrollTo(e, '#post-review-message');
    }
  };

  render() {
    const {
      isModelOpen,
    } = this.state;

    const bazaarVoiceSettings = getbazaarVoiceSettings();
    if (bazaarVoiceSettings.reviews.user.user_id === 0
      && bazaarVoiceSettings.reviews.bazaar_voice.write_review_submission) {
      return (
        <ClosedReviewSubmit destination={bazaarVoiceSettings.reviews.product.url} />
      );
    }

    return (
      <div className="button-wrapper">
        <div onClick={(e) => this.openModal(e)} className="write-review-button">
          {Drupal.t('Write a review')}
        </div>
        <Popup
          open={isModelOpen}
          className="write_review"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <WriteReviewForm
            closeModal={(e) => this.closeModal(e)}
          />
        </Popup>
      </div>
    );
  }
}
