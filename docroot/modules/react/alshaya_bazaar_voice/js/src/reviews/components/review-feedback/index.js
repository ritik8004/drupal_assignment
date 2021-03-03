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
    document.addEventListener('handleFeedbackSubmit', this.handleFeedbackSubmit);
  }

  handleFeedbackSubmit = (event) => {
    event.preventDefault();
    const { contentId, voteText, contentType } = event.detail;
    if (contentId !== undefined && voteText !== undefined) {
      const params = `&FeedbackType=helpfulness&ContentType=${contentType}&ContentId=${contentId}&Vote=${voteText}`;
      const apiData = postAPIData('/data/submitfeedback.json', params);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined
            && result.data !== undefined
            && result.data.error === undefined) {
            this.setState({ disabled: true });
          } else {
            // To Do - handle error response
          }
        });
      }
    }
  };

  render() {
    const {
      contentId, isSyndicatedReview, positiveCount, negativeCount, contentType,
    } = this.props;
    const { disabled } = this.state;
    let contentTypeDisplayValue = null;
    if (contentType === 'review_comment') {
      contentTypeDisplayValue = 'comment';
    }
    if (contentType === 'review') {
      contentTypeDisplayValue = 'review';
    }
    if (contentId !== undefined && positiveCount !== undefined && negativeCount
      !== undefined && isSyndicatedReview === false) {
      return (
        <div className="review-feedback-vote">
          <span className="feedback-label">{Drupal.t(`Was this ${contentTypeDisplayValue} helpful?`)}</span>
          <div className={`${disabled ? 'review-feedback-vote-disabled' : 'review-feedback-vote-active'}`}>
            <ReviewFeedbackPositive
              contentId={contentId}
              contentType={contentType}
              positiveCount={positiveCount}
              negativeCount={negativeCount}
            />
            <ReviewFeedbackNegative
              contentId={contentId}
              contentType={contentType}
              positiveCount={positiveCount}
              negativeCount={negativeCount}
            />
          </div>
          <ReviewInappropriate
            contentId={contentId}
            contentType={contentType}
          />
        </div>
      );
    }
    return (null);
  }
}

export default ReviewFeedback;
