import React from 'react';
import { fetchAPIData } from '../../../utilities/api/apiData';
import InlineRating from '../widgets/InlineRating';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import smoothScrollTo from '../../../utilities/smoothScroll';
import BvAuthConfirmation from '../../../reviews/components/reviews-full-submit/bv-auth-confirmation';
import { getbazaarVoiceSettings } from '../../../utilities/api/request';
import ConditionalView from '../../../common/components/conditional-view';
import getStringMessage from '../../../../../../js/utilities/strings';

export default class Rating extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      reviewsData: '',
      bazaarVoiceSettings: getbazaarVoiceSettings(),
    };
  }

  /**
   * Get Average Overall ratings and total reviews count.
   */
  componentDidMount() {
    showFullScreenLoader();
    const { bazaarVoiceSettings } = this.state;
    // Check reviews setting exist.
    if (bazaarVoiceSettings.reviews !== undefined) {
      const apiUri = '/data/products.json';
      const params = `&filter=id:${bazaarVoiceSettings.productid}&stats=${bazaarVoiceSettings.reviews.bazaar_voice.stats}`;
      const apiData = fetchAPIData(apiUri, params);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            removeFullScreenLoader();
            this.setState({
              reviewsData: result.data.Results[0],
            });
          } else {
            removeFullScreenLoader();
            Drupal.logJavascriptError('review-statistics', result.error);
          }
        });
      }
    }
  }

  clickHandler = (e, callbackFn) => {
    if (callbackFn === undefined) {
      smoothScrollTo(e, '#reviews-section');
    } else {
      e.preventDefault();
      callbackFn(e);
    }
  }

  render() {
    const { reviewsData, bazaarVoiceSettings } = this.state;
    // Return empty if reviews settings unavailable.
    if (bazaarVoiceSettings.reviews === undefined) {
      return null;
    }
    const { childClickHandler } = this.props;

    // Reviews data is emtpy.
    if (reviewsData === '') {
      return null;
    }

    if (reviewsData !== undefined
      && reviewsData !== ''
      && reviewsData.TotalReviewCount > 0) {
      return (
        <div className="rating-wrapper">
          <InlineRating childClickHandler={childClickHandler} reviewsData={reviewsData} />
          <ConditionalView condition={bazaarVoiceSettings.reviews.bv_auth_token !== null}>
            <BvAuthConfirmation bvAuthToken={bazaarVoiceSettings.reviews.bv_auth_token} />
          </ConditionalView>
        </div>
      );
    }
    return (
      <div className="inline-rating">
        <div className="aggregate-rating">
          <a onClick={(e) => this.clickHandler(e, childClickHandler)} className="write-review" href="#">{getStringMessage('write_a_review')}</a>
        </div>
      </div>
    );
  }
}
