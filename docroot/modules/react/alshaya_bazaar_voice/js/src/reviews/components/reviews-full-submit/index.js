import React from 'react';
import Popup from 'reactjs-popup';
import WriteReviewForm from './WriteReviewForm';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import ClosedReviewSubmit from './closed-review-submit';
import { getbazaarVoiceSettings, getUserDetails } from '../../../utilities/api/request';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getEmailFromTokenParams, isOpenWriteReviewForm } from '../../../utilities/user_util';
import ConditionalView from '../../../common/components/conditional-view';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import PostReviewMessage from './post-review-message';
import { trackFeaturedAnalytics } from '../../../utilities/analytics';

export default class WriteReviewButton extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isModelOpen: false,
      myAccountReview: '',
      buttonClass: 'write_review',
      validateCurrentEmail: false,
    };
  }

  componentDidMount() {
    const { productId, reviewedByCurrentUser } = this.props;
    // To open write a review on page load.
    if (isOpenWriteReviewForm(productId)) {
      this.setState({
        isModelOpen: true,
      });
    }

    const path = decodeURIComponent(window.location.search);
    const params = new URLSearchParams(path);
    if ((params.get('messageType') === 'PIE' || params.get('messageType') === 'PIE_FOLLOWUP') && reviewedByCurrentUser) {
      this.setState({
        buttonClass: 'myaccount_review',
      });
    }
    if (params.get('userToken') !== null) {
      const currentEmail = getEmailFromTokenParams(params);
      const userDetails = getUserDetails(productId);
      if (userDetails.user.userId !== 0 && userDetails.user.emailId !== currentEmail) {
        this.setState({
          validateCurrentEmail: true,
          buttonClass: 'myaccount_review',
        });
      }
    }
  }

  openModal = (e) => {
    e.preventDefault();
    document.body.classList.add('open-form-modal');

    this.setState({
      isModelOpen: true,
    });
    // Process write review click data as user clicks on button.
    const analyticsData = {
      type: 'Used',
      name: 'write',
      detail1: 'review',
      detail2: 'PrimaryRatingSummary',
    };
    trackFeaturedAnalytics(analyticsData);
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
    } else if (context === 'myaccount' && e.detail === 1) {
      this.setState({
        myAccountReview: '',
        buttonClass: 'write_review',
      });
    }
  };

  render() {
    const {
      isModelOpen,
      myAccountReview,
      buttonClass,
      validateCurrentEmail,
    } = this.state;
    const {
      reviewedByCurrentUser, productId, context, newPdp,
    } = this.props;
    const bazaarVoiceSettings = getbazaarVoiceSettings(productId);
    const userDetails = getUserDetails(productId);
    const userStorage = getStorageInfo(`bvuser_${userDetails.user.userId}`);
    if (userDetails.user.userId === 0
      && bazaarVoiceSettings.reviews.bazaar_voice.write_review_submission
      && userStorage.uasToken === null) {
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
                <ConditionalView condition={myAccountReview === '' && !validateCurrentEmail}>
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
        <ConditionalView condition={reviewedByCurrentUser && buttonClass === 'myaccount_review'}>
          <Popup
            open={isModelOpen}
            className={buttonClass}
            closeOnDocumentClick={false}
            closeOnEscape={false}
          >
            <>
              <div className="write-review-form">
                <div className="title-block">
                  <a className="close-modal" onClick={(e) => this.closeModal(e)} />
                </div>
                <div className="already-reviewed-text">{getStringMessage('already_reviewed_message')}</div>
              </div>
            </>
          </Popup>
        </ConditionalView>
        <ConditionalView condition={validateCurrentEmail && buttonClass === 'myaccount_review'}>
          <Popup
            open={isModelOpen}
            className={buttonClass}
            closeOnDocumentClick={false}
            closeOnEscape={false}
          >
            <>
              <div className="write-review-form">
                <div className="title-block">
                  <a className="close-modal" onClick={(e) => this.closeModal(e)} />
                </div>
                <div className="already-reviewed-text">{getStringMessage('invalid_pie_user_details')}</div>
              </div>
            </>
          </Popup>
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
