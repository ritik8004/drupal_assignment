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
import { getProductReviewStats } from '../../../utilities/user_util';
import WriteReviewButton from '../../../reviews/components/reviews-full-submit';
import { hasValue }
  from '../../../../../../js/utilities/conditionsUtility';

export default class Rating extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      reviewsData: '',
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
    const bazaarVoiceConfig = getbazaarVoiceSettings();

    this.setState({
      bazaarVoiceSettings: bazaarVoiceConfig,
    });

    const { bazaarVoiceSettings } = this.state;

    // Check reviews setting exist.
    if (Drupal.hasValue(bazaarVoiceSettings)) {
      getProductReviewStats(bazaarVoiceSettings.productid).then((result) => {
        removeFullScreenLoader();
        if (result !== null) {
          this.setState({
            reviewsData: result.productData,
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
    const { reviewsData, bazaarVoiceSettings, userDetails } = this.state;

    const {
      childClickHandler,
      renderLinkDirectly,
    } = this.props;

    const renderLink = renderLinkDirectly || false;
    const reviewedByCurrentUser = userDetails.productReview || false;
    const isInline = true;

    // Reviews data is emtpy.
    if (!hasValue(reviewsData) || reviewsData === '') {
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
