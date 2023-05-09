import React from 'react';
import InlineRating from '../widgets/InlineRating';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import BvAuthConfirmation from '../../../reviews/components/reviews-full-submit/bv-auth-confirmation';
import {
  getbazaarVoiceSettings,
  getUserDetails,
} from '../../../utilities/api/request';
import ConditionalView from '../../../common/components/conditional-view';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getReviewStats } from '../../../utilities/user_util';
import WriteReviewButton from '../../../reviews/components/reviews-full-submit';

export default class Rating extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      reviewsData: '',
      apiFinished: false,
      bazaarVoiceSettings: getbazaarVoiceSettings(),
      userDetails: {
        productReview: null,
      },
    };
  }

  /**
   * Get Average Overall ratings and total reviews count.
   */
  componentDidMount() {
    showFullScreenLoader();
    const { bazaarVoiceSettings } = this.state;

    // Check reviews setting exist.
    if (Drupal.hasValue(bazaarVoiceSettings)) {
      getReviewStats(bazaarVoiceSettings).then((result) => {
        removeFullScreenLoader();
        if (result !== null) {
          let reviewsData = '';
          if (Drupal.hasValue(result.Includes.Products)
            && Drupal.hasValue(result.Includes.Products[bazaarVoiceSettings.productid])) {
            reviewsData = result.Includes.Products[bazaarVoiceSettings.productid];
          }

          // Update product review data and BV response status.
          this.setState({
            reviewsData,
            apiFinished: true,
          });
        }
      });
    } else {
      removeFullScreenLoader();
    }

    getUserDetails().then((userDetails) => {
      this.setState({ userDetails });
    });
  }

  clickHandler = (e, callbackFn) => {
    if (callbackFn === undefined) {
      smoothScrollTo(e, '#reviews-section');
    } else {
      e.preventDefault();
      callbackFn(e, 'write_review');
    }
  }

  render() {
    const {
      reviewsData,
      bazaarVoiceSettings,
      userDetails,
      apiFinished,
    } = this.state;

    const {
      childClickHandler,
      renderLinkDirectly,
    } = this.props;

    const renderLink = renderLinkDirectly || false;
    const reviewedByCurrentUser = userDetails.productReview || false;
    const isInline = true;

    // Move forward only if BV API has returned successful response.
    if (!apiFinished) {
      return null;
    }

    return (
      <div className="rating-wrapper">
        <ConditionalView condition={reviewsData !== undefined
          && reviewsData !== '' && reviewsData.TotalReviewCount > 0}
        >
          <InlineRating childClickHandler={childClickHandler} reviewsData={reviewsData} />
          <ConditionalView condition={bazaarVoiceSettings.reviews.bv_auth_token !== null}>
            <BvAuthConfirmation bvAuthToken={bazaarVoiceSettings.reviews.bv_auth_token} />
          </ConditionalView>
        </ConditionalView>

        <ConditionalView condition={renderLink
          && userDetails.user.userId > 0 && !reviewedByCurrentUser}
        >
          <div className="button-wrapper">
            <a onClick={(e) => this.clickHandler(e, childClickHandler)} className="write-review-button" href="#">{getStringMessage('write_a_review')}</a>
          </div>
        </ConditionalView>

        <ConditionalView condition={userDetails.user.userId === 0 && renderLink}>
          <WriteReviewButton
            reviewedByCurrentUser={reviewedByCurrentUser}
            newPdp={renderLink}
            isInline={isInline}
          />
        </ConditionalView>
        <ConditionalView condition={reviewedByCurrentUser && renderLink}>
          <WriteReviewButton
            reviewedByCurrentUser={reviewedByCurrentUser}
            newPdp={renderLink}
            isInline={isInline}
          />
        </ConditionalView>
        <ConditionalView condition={!renderLink}>
          <WriteReviewButton
            reviewedByCurrentUser={reviewedByCurrentUser}
            isInline={isInline}
          />
        </ConditionalView>
      </div>
    );
  }
}
