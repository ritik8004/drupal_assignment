import React from 'react';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../../js/utilities/strings';
import DisplayStar from '../../../../rating/components/stars';
import {
  fetchAPIData,
  getUserBazaarVoiceSettings,
} from '../../../../utilities/api/request';
import IndividualReviewSlider from '../../../../reviews/components/individual-review-slider';
import ConditionalView from '../../../../common/components/conditional-view';
import EmptyMessage from '../../../../utilities/empty-message';
import UserReviewsProducts from '../user-reviews-products';
import UserReviewsDescription from '../user-reviews-desc';

let bazaarVoiceSettings = getUserBazaarVoiceSettings();
export default class UserReviews extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      reviewsSummary: '',
      reviewsProduct: '',
      noResultmessage: '',
      totalReviewCount: '',
      initialLimit: '',
    };
    this.loadMore = this.loadMore.bind(this);
  }

  /**
   * Get Review results and product statistical data.
   */
  componentDidMount() {
    bazaarVoiceSettings = getUserBazaarVoiceSettings();

    if (!Drupal.hasValue(bazaarVoiceSettings)) {
      return;
    }

    this.setState({
      initialLimit: bazaarVoiceSettings.reviews.bazaar_voice.reviews_initial_load,
    }, () => {
      this.getUserReviews();
    });
  }

  getUserReviews() {
    const { initialLimit } = this.state;
    const myAccountReviewsLimit = bazaarVoiceSettings.reviews.bazaar_voice.myaccount_reviews_limit;
    showFullScreenLoader();
    // Get review data from BazaarVoice based on available parameters.
    const apiUri = '/data/reviews.json';
    const params = `&include=Authors,Products&filter=AuthorId:${bazaarVoiceSettings.reviews.bazaar_voice.user_id}&stats=${bazaarVoiceSettings.reviews.bazaar_voice.stats}&Limit=${initialLimit}`;
    const apiData = fetchAPIData(apiUri, params, 'user');
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        removeFullScreenLoader();
        if (result.error === undefined && result.data !== undefined) {
          if (result.data.Results.length > 0) {
            this.setState({
              reviewsSummary: result.data.Results,
              reviewsProduct: result.data.Includes.Products,
              totalReviewCount: result.data.TotalResults <= myAccountReviewsLimit
                ? result.data.TotalResults : myAccountReviewsLimit,
              noResultmessage: null,
            });
          } else {
            this.setState({
              noResultmessage: getStringMessage('no_user_review_found'),
            });
          }
        } else {
          removeFullScreenLoader();
          Drupal.logJavascriptError('user-review-summary', result.error);
        }
      });
    }
  }

  loadMore() {
    const loadMoreLimit = bazaarVoiceSettings.reviews.bazaar_voice.reviews_on_loadmore;
    this.setState((prev) => ({ initialLimit: prev.initialLimit + loadMoreLimit }), () => {
      this.getUserReviews();
    });
  }

  render() {
    const {
      reviewsSummary,
      reviewsProduct,
      noResultmessage,
      initialLimit,
      totalReviewCount,
    } = this.state;
    return (
      <div id="user-reviews_wrapper">
        <ConditionalView condition={noResultmessage === null}>
          <div id="review-summary-wrapper">
            {Object.keys(reviewsSummary).map((item) => (
              <div className="review-summary" key={reviewsSummary[item].Id}>
                <UserReviewsProducts
                  reviewsIndividualSummary={reviewsSummary[item]}
                  reviewsProduct={reviewsProduct}
                />
                <div className="user-reviews">
                  <div className="user-desc">
                    <DisplayStar
                      starPercentage={reviewsSummary[item].Rating}
                    />
                    <UserReviewsDescription
                      reviewsIndividualSummary={reviewsSummary[item]}
                    />
                  </div>
                  <div className="user-secondary-rating">
                    <IndividualReviewSlider
                      sliderData={reviewsSummary[item].SecondaryRatings}
                      secondaryRatingsOrder={reviewsSummary[item].SecondaryRatingsOrder}
                    />
                  </div>
                </div>
              </div>
            ))}
          </div>
        </ConditionalView>
        <ConditionalView condition={initialLimit < totalReviewCount}>
          <div className="load-more-wrapper">
            <button onClick={this.loadMore} type="button" className="load-more">{getStringMessage('load_more')}</button>
          </div>
        </ConditionalView>
        <ConditionalView condition={noResultmessage !== null}>
          <EmptyMessage emptyMessage={noResultmessage} />
        </ConditionalView>
      </div>
    );
  }
}
