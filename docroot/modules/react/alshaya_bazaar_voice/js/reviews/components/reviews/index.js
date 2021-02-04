import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import fetchAPIData from '../../../utilities/api/apiData';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';

export default class Reviews extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ReviewsSummary: '',
      ReviewsProduct: '',
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
          this.setState({
            ReviewsSummary: result.data.Results,
            ReviewsProduct: result.data.Includes.Products,
          });
        } else {
          // Todo
        }
      });
    }
  }

  render() {
    const {
      ReviewsSummary, ReviewsProduct,
    } = this.state;

    return (
      <div className="reviews-wrapper">
        { Object.keys(ReviewsSummary).map((item) => {
          const date = new Date(ReviewsSummary[item].SubmissionTime);
          return (
            <div className="review-summary">
              <div className="review-detail-left">
                <div className="review-user-details">
                  <div className="review-tooltip">
                    <div className="user-detail-nickname">{ReviewsSummary[item].UserNickname}</div>
                    <div className="user-review-info">
                    <div className="user-info">
                      <div className="user-nickname">{ReviewsSummary[item].UserNickname}</div>
                      <div className="user-location">{ReviewsSummary[item].UserLocation}</div>
                    </div>
                    <div className="user-review-wrapper">
                      <div className="user-reviews-details">
                        <div className="review-count">
                          <div className="label">Review</div>
                          <div className="value">{ReviewsProduct[ReviewsSummary[item].ProductId].ReviewStatistics.TotalReviewCount}</div>
                        </div>
                        <div className="review-vote">
                          <div className="label">Vote</div>
                          <div className="value">{ReviewsProduct[ReviewsSummary[item].ProductId].ReviewStatistics.HelpfulVoteCount}</div>
                        </div>
                      </div>
                      <div className="user-personal-details">
                        <div className="user-attributes">
                          <span className="user-name">{`${ReviewsSummary[item].UserNickname}: `}</span>
                          <span className="user-attribute-value">{ReviewsSummary[item].ContextDataValues.Age.Value}</span>
                        </div>
                        <div className="user-attributes">
                          <span className="user-name">Gender:</span>
                          <span className="user-attribute-value"> Female</span>
                        </div>
                      </div>
                    </div>
                    </div>
                  </div>
                  <div className="user-detail-location">{ReviewsSummary[item].UserLocation}</div>
                </div>
                <div className="horizontal-border"></div>
                <div className="review-attributes">
                  <div className="review-attributes-wrapper">
                    {/* Replace the attribute details once available, hardcoded as of now. */}
                    <div className="review-attributes-details">
                      <span className="attribute-name">Height:</span>
                      <span className="attribute-value"> 5'5"</span>
                    </div>
                    <div className="review-attributes-details">
                      <span className="attribute-name">Weight:</span>
                      <span className="attribute-value"> 120 lbs</span>
                    </div>
                  </div>
                </div>
              </div>
              <div className="review-detail-right">
                <div className="review-details">
                  <DisplayStar
                    StarPercentage={ReviewsSummary[item].Rating}
                  />
                  <div className="review-title">{ReviewsSummary[item].Title}</div>
                  <div className="review-date">{`${date.toLocaleString('default', { month: 'short' })} ${date.getDate()}, ${date.getFullYear()}`}</div>
                  <div className="review-text">{ReviewsSummary[item].ReviewText}</div>
                  <div className="review-photo">{ReviewsSummary[item].Photo}</div>
                </div>
              </div>
            </div>
          );
        })}
      </div>
    );
  }
}
