import React from 'react';
import fetchAPIData from '../../../utilities/api/apiData';
import InlineRating from '../widgets/InlineRating';
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
    const params = `&filter=productid:${drupalSettings.bazaar_voice.productid}&Include=${drupalSettings.bazaar_voice.Include}&stats=${drupalSettings.bazaar_voice.stats}`;
    const apiData = fetchAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          removeFullScreenLoader();
          /* TODO: BE to use from utiltity rather then directly from localstorage. */
          localStorage.setItem('ReviewsSummary', JSON.stringify(result.data.Results));
          localStorage.setItem('ReviewsProduct', JSON.stringify(result.data.Includes.Products));
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
    const { ReviewsData } = this.state;
    return (
      <div className="rating-wrapper">
        <InlineRating ReviewsData={ReviewsData} />
      </div>
    );
  }
}
