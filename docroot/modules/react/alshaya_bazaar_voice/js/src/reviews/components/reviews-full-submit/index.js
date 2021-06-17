import React from 'react';
import Popup from 'reactjs-popup';
import WriteReviewForm from './WriteReviewForm';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import ClosedReviewSubmit from './closed-review-submit';
import { getbazaarVoiceSettings, getUserDetails } from '../../../utilities/api/request';
import getStringMessage from '../../../../../../js/utilities/strings';
import { isOpenWriteReviewForm } from '../../../utilities/user_util';
import ConditionalView from '../../../common/components/conditional-view';
import { setStorageInfo } from '../../../utilities/storage';
import PostReviewMessage from './post-review-message';
import dispatchCustomEvent from '../../../../../../js/utilities/events';

export default class WriteReviewButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
      myAccountReview: '',
      buttonClass: 'write_review',
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
    // Dispatching click event to record analytics.
    const analyticsData = { detail1: 'review', detail2: 'PrimaryRatingSummary' };
    dispatchCustomEvent('bvWriteReviewClick', analyticsData);
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

    if (e.detail.HasErrors !== undefined && !e.detail.HasErrors) {
      if (context !== 'myaccount') {
        smoothScrollTo(e, '#post-review-message');
      } else {
        this.setState({
          isModelOpen: true,
          myAccountReview: e.detail,
          buttonClass: 'myaccount_review',
        });
      }
    }
  };

  render() {
    const {
      isModelOpen,
      myAccountReview,
      buttonClass,
    } = this.state;
    const {
      reviewedByCurrentUser, productId, context, newPdp,
    } = this.props;
    const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
    const userDetails = getUserDetails(productId);
    if (userDetails.user.userId === 0
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
              className={buttonClass}
              closeOnDocumentClick={false}
              closeOnEscape={false}
            >
              <>
                <ConditionalView condition={myAccountReview !== ''}>
                  <div className="write-review-form">
                    <div className="title-block">
                      <a className="close-modal" onClick={(e) => this.closeModal(e)} />
                    </div>
                    <PostReviewMessage postReviewData={myAccountReview} />
                  </div>
                </ConditionalView>
                <ConditionalView condition={myAccountReview === ''}>
                  <WriteReviewForm
                    closeModal={(e) => this.closeModal(e)}
                    productId={productId}
                    context={context}
                    newPdp={newPdp}
                  />
                </ConditionalView>
              </>
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
