import React from 'react';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import getStringMessage from '../../../../../../js/utilities/strings';
import DisplayStar from '../../../rating/components/stars';
import { fetchAPIData } from '../../../utilities/api/apiData';
import IndividualReviewSlider from '../../../reviews/components/individual-review-slider';
import { getbazaarVoiceSettings } from '../../../utilities/api/request';
import ConditionalView from '../../../common/components/conditional-view';
import EmptyMessage from '../../../utilities/empty-message';
import UserReviewsProducts from '../user-reviews-products';
import UserReviewsDescription from '../user-reviews-desc';

const bazaarVoiceSettings = getbazaarVoiceSettings('user');
export default class UserReviews extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      reviewsSummary: '',
      reviewsProduct: '',
      noResultmessage: '',
      currentTotal: '',
      initialLimit: bazaarVoiceSettings.reviews.bazaar_voice.reviews_initial_load,
    };
    this.loadMore = this.loadMore.bind(this);
  }

  /**
   * Get Review results and product statistical data.
   */
  componentDidMount() {
    this.getUserReviews();
  }

  getUserReviews() {
    const { initialLimit } = this.state;
    showFullScreenLoader();
    // Get review data from BazaarVoice based on available parameters.
    const apiUri = '/data/authors.json';
    const params = `&include=reviews,products&filter=id:${bazaarVoiceSettings.reviews.bazaar_voice.user_id}&stats=${bazaarVoiceSettings.reviews.bazaar_voice.stats}&Limit_Review=${initialLimit}`;
    const apiData = fetchAPIData(apiUri, params, 'user');
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        removeFullScreenLoader();
        if (result.error === undefined && result.data !== undefined) {
          if (result.data.Results.length > 0) {
            this.setState({
              reviewsProduct: result.data.Includes.Products,
              reviewsSummary: result.data.Includes.Reviews,
              currentTotal: Object.keys(result.data.Includes.Reviews).length,
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
      currentTotal,
    } = this.state;
    return (
      <div id="user-reviews_wrapper">
        <ConditionalView condition={noResultmessage === null}>
          <div id="review-summary-wrapper">
            {Object.keys(reviewsSummary).slice(0, initialLimit).map((item) => (
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
                  <ConditionalView condition={window.innerWidth > 767}>
                    <div className="user-secondary-rating">
                      <IndividualReviewSlider
                        sliderData={reviewsProduct[reviewsSummary[item]
                          .ProductId].ReviewStatistics.SecondaryRatingsAverages}
                        secondaryRatingsOrder={reviewsProduct[reviewsSummary[item]
                          .ProductId].ReviewStatistics.SecondaryRatingsAveragesOrder}
                      />
                    </div>
                  </ConditionalView>
                </div>
              </div>
            ))}
          </div>
        </ConditionalView>
        <ConditionalView condition={initialLimit < currentTotal}>
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
