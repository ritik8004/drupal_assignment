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

export default class UserReviews extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      reviewsSummary: '',
      reviewsProduct: '',
      noResultmessage: '',
    };
  }

  /**
   * Get Review results and product statistical data.
   */
  componentDidMount() {
    showFullScreenLoader();
    const bazaarVoiceSettings = getbazaarVoiceSettings('user');
    // Get review data from BazaarVoice based on available parameters.
    const apiUri = '/data/authors.json';
    const params = `&include=reviews,products&filter=id:${bazaarVoiceSettings.reviews.bazaar_voice.user_id}`;
    const apiData = fetchAPIData(apiUri, params, 'user');
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        removeFullScreenLoader();
        if (result.error === undefined && result.data !== undefined) {
          if (result.data.Results.length > 0) {
            this.setState({
              reviewsProduct: result.data.Includes.Products,
              reviewsSummary: result.data.Includes.Reviews,
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


  render() {
    const {
      reviewsSummary,
      reviewsProduct,
      noResultmessage,
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
                <DisplayStar
                  starPercentage={reviewsSummary[item].Rating}
                />
                <UserReviewsDescription
                  reviewsIndividualSummary={reviewsSummary[item]}
                />
                <IndividualReviewSlider
                  sliderData={reviewsSummary[item].SecondaryRatings}
                />
              </div>
            ))}
          </div>
        </ConditionalView>
        <ConditionalView condition={noResultmessage !== null}>
          <EmptyMessage emptyMessage={noResultmessage} />
        </ConditionalView>
      </div>
    );
  }
}
