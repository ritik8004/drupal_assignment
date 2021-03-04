import React from 'react';
import { fetchAPIData } from '../../../utilities/api/apiData';
import InlineRating from '../widgets/InlineRating';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import smoothScrollTo from '../../../utilities/smoothScroll';
import BvAuthConfirmation from '../../../reviews/components/reviews-full-submit/bv-auth-confirmation';
import { getbazaarVoiceSettings } from '../../../utilities/api/request';

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

  render() {
    const { reviewsData, bazaarVoiceSettings } = this.state;

    if (reviewsData !== undefined
      && reviewsData !== ''
      && reviewsData.TotalReviewCount > 0) {
      return (
        <div className="rating-wrapper">
          <InlineRating reviewsData={reviewsData} />
          {bazaarVoiceSettings.reviews.bv_auth_token !== null
          && (<BvAuthConfirmation bvAuthToken={bazaarVoiceSettings.reviews.bv_auth_token} />)}
        </div>
      );
    }
    return (
      <div className="inline-rating">
        <div className="aggregate-rating">
          <a onClick={(e) => smoothScrollTo(e, '#reviews-section')} className="write-review" href="#">{Drupal.t('Write a Review')}</a>
        </div>
      </div>
    );
  }
}
