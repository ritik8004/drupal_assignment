import React from 'react';
import { postAPIData } from '../../../utilities/api/apiData';
import ReviewInappropriate from '../review-inappropriate';
import ReviewFeedbackPositive from '../review-feedback-positive';
import ReviewFeedbackNegative from '../review-feedback-negative';

class ReviewFeedback extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      disabled: false,
    };
  }

  componentDidMount() {
    document.addEventListener('handleApiResponse', this.handleApiResponse);
  }

  handleApiResponse = (event) => {
    event.preventDefault();
    const { reviewId } = event.detail;
    const { voteText } = event.detail;
    if (reviewId !== undefined && voteText !== undefined) {
      const params = `&FeedbackType=helpfulness&ContentType=review&ContentId=${reviewId}&Vote=${voteText}`;
      const apiData = postAPIData('/data/submitfeedback.json', params);
      this.setState({ disabled: true });
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined
            && result.data !== undefined
            && result.data.error === undefined) {
            // To Do - handle api response
          } else {
            // To Do - handle error response
          }
        });
      }
    }
  };

  render() {
    const {
      reviewId, isSyndicatedReview, positiveCount, negativeCount,
    } = this.props;
    const { disabled } = this.state;
    if (reviewId !== undefined && positiveCount !== undefined && negativeCount
      !== undefined && isSyndicatedReview === false) {
      return (
        <div className="review-feedback-vote">
          <span className="feedback-label">{Drupal.t('Was this review helpful?')}</span>
          <div className={`${disabled ? 'review-feedback-vote-disabled' : 'review-feedback-vote-active'}`}>
            <ReviewFeedbackPositive
              reviewId={reviewId}
              positiveCount={positiveCount}
              negativeCount={negativeCount}
            />
            <ReviewFeedbackNegative
              reviewId={reviewId}
              positiveCount={positiveCount}
              negativeCount={negativeCount}
            />
          </div>
          <ReviewInappropriate
            reviewId={reviewId}
          />
        </div>
      );
    }
    return (null);
  }
}

export default ReviewFeedback;
