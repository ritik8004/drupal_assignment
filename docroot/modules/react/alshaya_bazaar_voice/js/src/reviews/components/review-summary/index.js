import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewInformation from '../review-info';
import ReviewDescription from '../review-desc';
import ReviewHistogram from '../review-histogram';
import { fetchAPIData } from '../../../utilities/api/apiData';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../js/utilities/showRemoveFullScreenLoader';

export default class ReviewSummary extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      reviewsSummary: '',
      reviewsProduct: '',
    };
  }

  /**
   * Get Review results and product statistical data.
   */
  componentDidMount() {
    showFullScreenLoader();
    const apiUri = '/data/reviews.json';
    const params = `&filter=productid:${drupalSettings.bazaar_voice.productid}&Include=${drupalSettings.bazaar_voice.Include}&stats=${drupalSettings.bazaar_voice.stats}`;
    const apiData = fetchAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          removeFullScreenLoader();
          this.setState({
            reviewsSummary: result.data.Results,
            reviewsProduct: result.data.Includes.Products,
          });
        } else {
          Drupal.logJavascriptError('review-summary', result.error);
        }
      });
    }
  }

  render() {
    const { reviewsSummary, reviewsProduct } = this.state;
    return (
      <div className="reviews-wrapper">
        <div className="histogram-data-section">
          <div className="rating-wrapper">
            <ReviewHistogram overallSummary={reviewsProduct} />
          </div>
        </div>
        { Object.keys(reviewsSummary).map((item) => (
          <div className="review-summary" key={reviewsSummary[item].Id}>
            <ConditionalView condition={window.innerWidth < 768}>
              <DisplayStar
                starPercentage={reviewsSummary[item].Rating}
              />
              <div className="review-title">{reviewsSummary[item].Title}</div>
            </ConditionalView>
            <ReviewInformation
              reviewInformationData={reviewsSummary[item]}
              reviewTooltipInfo={
                reviewsProduct[reviewsSummary[item].ProductId].ReviewStatistics
              }
            />
            <ReviewDescription
              reviewDescriptionData={reviewsSummary[item]}
            />
          </div>
        ))}
      </div>
    );
  }
}
