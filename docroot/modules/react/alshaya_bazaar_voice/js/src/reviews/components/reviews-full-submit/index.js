import React from 'react';
import Popup from 'reactjs-popup';
import WriteReviewForm from './WriteReviewForm';
import smoothScrollTo from '../../../utilities/smoothScroll';
import ClosedReviewSubmit from './closed-review-submit';
import { getbazaarVoiceSettings, getUserDetails } from '../../../utilities/api/request';
import getStringMessage from '../../../../../../js/utilities/strings';
import { isOpenWriteReviewForm } from '../../../utilities/user_util';
import ConditionalView from '../../../common/components/conditional-view';
import { setStorageInfo } from '../../../utilities/storage';

export default class WriteReviewButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
    };
  }

  componentDidMount() {
    const { productId } = this.props;
    // To open write a review on page load.
    if (isOpenWriteReviewForm(productId)) {
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
    const { context } = this.props;

    this.setState({
      isModelOpen: false,
    });
    // Disable write review popup onload.
    setStorageInfo(false, 'openPopup');

    if (e.detail.HasErrors !== undefined && !e.detail.HasErrors && context !== 'myaccount') {
      smoothScrollTo(e, '#post-review-message');
    }
  };

  render() {
    const {
      isModelOpen,
    } = this.state;
    const { reviewedByCurrentUser, productId, context } = this.props;
    const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
    const userDetails = getUserDetails(productId);
    if (userDetails.user.webUserID === 0
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
                productId={productId}
                context={context}
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
