import React from 'react';
import InlineRating from '../widgets/InlineRating';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import { smoothScrollTo } from '../../../utilities/smoothScroll';
import BvAuthConfirmation from '../../../reviews/components/reviews-full-submit/bv-auth-confirmation';
import {
  getbazaarVoiceSettings,
  getBazaarVoiceSettingsFromMdc,
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
  componentDidMount = async () => {
    showFullScreenLoader();

    // Call MDC for bazaarvoice settings.
    const bazaarVoiceConfig = await getBazaarVoiceSettingsFromMdc();

    if (typeof bazaarVoiceConfig === 'undefined' || bazaarVoiceConfig === null) {
      return;
    }

    const { bazaarVoiceSettings } = this.state;

    // Intialize bazaarvoice settings from MDC response.
    bazaarVoiceSettings.reviews.bazaar_voice.error_messages = {};
    bazaarVoiceSettings.reviews.bazaar_voice.sorting_options = {};
    bazaarVoiceSettings.reviews.bazaar_voice.filter_options = {};

    // Add basic configurations from MDC response.
    Object.assign(
      bazaarVoiceSettings.reviews.bazaar_voice,
      bazaarVoiceConfig.basic,
    );

    // Add error messages configurations from MDC response.
    Object.assign(
      bazaarVoiceSettings.reviews.bazaar_voice.error_messages,
      bazaarVoiceConfig.bv_error_messages,
    );

    // Add sorting options configurations from MDC response.
    Object.assign(
      bazaarVoiceSettings.reviews.bazaar_voice.sorting_options,
      bazaarVoiceConfig.sorting_options,
    );

    // Add filter options configurations from MDC response.
    Object.assign(
      bazaarVoiceSettings.reviews.bazaar_voice.filter_options,
      bazaarVoiceConfig.pdp_filter_options,
    );

    this.setState({
      bazaarVoiceSettings,
    });

    // Check reviews setting exist.
    if (bazaarVoiceSettings.reviews !== undefined) {
      getProductReviewStats(bazaarVoiceSettings.productid).then((result) => {
        removeFullScreenLoader();
        if (result !== null) {
          this.setState({
            reviewsData: result.productData,
          });
        }
      });
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

    // Return empty if reviews settings unavailable.
    if (typeof bazaarVoiceSettings.reviews.bazaar_voice.Include === 'undefined') {
      return null;
    }

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
