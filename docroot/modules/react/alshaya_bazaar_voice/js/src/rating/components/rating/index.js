import React from 'react';
import { fetchAPIData } from '../../../utilities/api/apiData';
import InlineRating from '../widgets/InlineRating';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import smoothScrollTo from '../../../utilities/smoothScroll';

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
    const apiUri = '/data/products.json';
    const params = `&filter=id:${drupalSettings.bazaar_voice.productid}&stats=${drupalSettings.bazaar_voice.stats}`;
    const apiData = fetchAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          removeFullScreenLoader();
          this.setState({
            ReviewsData: result.data.Results,
          });
        } else {
          Drupal.logJavascriptError('review-statistics', result.error);
        }
      });
    }
  }

  render() {
    const { ReviewsData } = this.state;
    if (ReviewsData !== undefined && ReviewsData !== '') {
      return (
        <div className="rating-wrapper">
          <InlineRating ReviewsData={ReviewsData} />
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
