import React from 'react';
import fetchAPIData from '../../../utilities/api/apiData';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';

export default class Rating extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ReviewsData: '',
    };
  }

  /**
   * Get Average Overall ratings and total reviews count.
   */
  componentDidMount() {
    showFullScreenLoader();
    const apiUri = '/data/reviews.json';
    const params = `&filter=productid:${drupalSettings.alshaya_bazaar_voice.productid}&Include=${drupalSettings.alshaya_bazaar_voice.Include}&stats=${drupalSettings.alshaya_bazaar_voice.stats}`;
    const apiData = fetchAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          removeFullScreenLoader();
          this.setState({
            ReviewsData: result.data.Includes.Products,
          });
        } else {
          // Todo
        }
      });
    }
  }

  render() {
    const {
      ReviewsData,
    } = this.state;

    return (
      <div className="rating-wrapper">
        { Object.keys(ReviewsData).map((item) => (
          <div>
            <span className="input-label">{parseFloat(ReviewsData[item].ReviewStatistics.AverageOverallRating).toFixed(1)}</span>
            <span className="input-label">{ReviewsData[item].ReviewStatistics.TotalReviewCount}</span>
          </div>
        ))}
      </div>
    );
  }
}
