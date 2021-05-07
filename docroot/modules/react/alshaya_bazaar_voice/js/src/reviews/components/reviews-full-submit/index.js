import React from 'react';
import Popup from 'reactjs-popup';
import WriteReviewForm from './WriteReviewForm';
import smoothScrollTo from '../../../utilities/smoothScroll';
import ClosedReviewSubmit from './closed-review-submit';
import { getbazaarVoiceSettings } from '../../../utilities/api/request';
import getStringMessage from '../../../../../../js/utilities/strings';
import ConditionalView from '../../../common/components/conditional-view';
import { getStorageInfo, setStorageInfo } from '../../../utilities/storage';

export default class WriteReviewButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
    };
  }

  componentDidMount() {
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    const { reviewedByCurrentUser } = this.props;
    const query = new URLSearchParams(document.referrer);
    const openPopup = query.get('openPopup');
    // To display write review popup on page load.
    if (bazaarVoiceSettings.reviews.user.user_id > 0
       && getStorageInfo('openPopup')
       && !reviewedByCurrentUser
       && openPopup !== null) {
      this.setState({
        isModelOpen: true,
      });
    }
  }

  openModal = (e) => {
    e.preventDefault();
    document.body.classList.add('open-form-modal');

    this.setState({
      isModelOpen: true,
    });
  };

  closeModal = (e) => {
    e.preventDefault();
    document.body.classList.remove('open-form-modal');

    this.setState({
      isModelOpen: false,
    });
    // Disable write review popup onload.
    setStorageInfo(false, 'openPopup');

    if (e.detail.HasErrors !== undefined && !e.detail.HasErrors) {
      smoothScrollTo(e, '#post-review-message');
    }
  };

  render() {
    const {
      isModelOpen,
    } = this.state;
    const { reviewedByCurrentUser } = this.props;
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    if (bazaarVoiceSettings.reviews.user.user_id === 0
      && bazaarVoiceSettings.reviews.bazaar_voice.write_review_submission) {
      return (
        <ClosedReviewSubmit destination={bazaarVoiceSettings.reviews.product.url} />
      );
    }

    return (
      <>
        <ConditionalView condition={!reviewedByCurrentUser}>
          <div className="button-wrapper">
            <div onClick={(e) => this.openModal(e)} className="write-review-button">
              {getStringMessage('write_a_review')}
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
        </ConditionalView>
        <ConditionalView condition={reviewedByCurrentUser}>
          <div className="button-wrapper">
            <div className="write-review-button disabled">
              {getStringMessage('write_a_review')}
            </div>
          </div>
          <div className="already-reviewed-text">{getStringMessage('already_reviewed_message')}</div>
        </ConditionalView>
      </>
    );
  }
}
