import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewInformation from '../review-info';
import ReviewDescription from '../review-desc';
import ReviewHistogram from '../review-histogram';
import { fetchAPIData } from '../../../utilities/api/apiData';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../js/utilities/showRemoveFullScreenLoader';
import ReviewSorting from '../review-sorting';

export default class ReviewSummary extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      reviewsSummary: '',
      reviewsProduct: '',
      currentOption: '',
    };
  }

  /**
   * Get Review results and product statistical data.
   */
  componentDidMount() {
    this.getReviews();
  }

  getReviews = (extraParam) => {
    showFullScreenLoader();
    const extraParams = (extraParam !== undefined) ? extraParam : '';
    const apiUri = '/data/reviews.json';
    const params = `&filter=productid:${drupalSettings.bazaar_voice.productid}&Include=${drupalSettings.bazaar_voice.Include}&stats=${drupalSettings.bazaar_voice.stats}${extraParams}`;
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
          removeFullScreenLoader();
          Drupal.logJavascriptError('review-summary', result.error);
        }
      });
    }
  };

  /**
   * Process the option value when get from the select list.
   */
  processSortOption = (option) => {
    this.setState({ currentOption: option.value });

    const sortOption = `&sort=${option.value}`;
    this.getReviews(sortOption);
  }

  render() {
    const {
      reviewsSummary,
      reviewsProduct,
      currentOption,
    } = this.state;

    return (
      <div className="reviews-wrapper">
        <div className="histogram-data-section">
          <div className="rating-wrapper">
            <ReviewHistogram overallSummary={reviewsProduct} />
          </div>
          <div className="sorting-filter-wrapper">
            <ReviewSorting
              currentOption={currentOption}
              sortOptions={drupalSettings.bazaar_voice.sorting_options}
              processingCallback={this.processSortOption}
            />
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
