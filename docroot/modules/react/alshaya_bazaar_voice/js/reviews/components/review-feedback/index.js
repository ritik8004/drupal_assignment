import React from 'react';
import { postAPIData } from '../../../utilities/api/apiData';
import ReviewInappropriate from '../review-inappropriate';
import ReviewFeedbackPositive from '../review-feedback-positive';

class ReviewFeedback extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      disabled: false,
      positiveCount: props.PositiveFeedbackCount,
      negativeCount: props.NegativeFeedbackCount,
    };
  }

  handleFeedbackCount = (reviewId, voteText) => (event) => {
    event.preventDefault();
    const { disabled, positiveCount, negativeCount} = this.state;
    if (disabled) {
      return;
    }
    this.setState({ disabled: true });
    const apiUri = '/data/submitfeedback.json';
    const params = `&FeedbackType=helpfulness&ContentType=review&ContentId=${reviewId}&Vote=${voteText}`;
    const apiData = postAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
          && result.data !== undefined
          && result.data.error === undefined) {
          const helpfulnessVoteObj = {
            reviewId,
            positiveCount,
            negativeCount,
          };
          if (voteText === 'Positive') {
            this.setState({ positiveCount: positiveCount + 1 });
            helpfulnessVoteObj.positiveCount += 1;
            localStorage.setItem(`helpfulnessVote-${reviewId}`, JSON.stringify(helpfulnessVoteObj));
          }
          if (voteText === 'Negative') {
            this.setState({ negativeCount: negativeCount + 1 });
            helpfulnessVoteObj.negativeCount += 1;
            localStorage.setItem(`helpfulnessVote-${reviewId}`, JSON.stringify(helpfulnessVoteObj));
          }
        }
      });
    }
  };

  render() {
    const { reviewId, isSyndicatedReview} = this.props;
    const { disabled, positiveCount, negativeCount} = this.state;
    const positiveText = 'Positive';
    const negativeText = 'Negative';
    if (reviewId !== undefined && positiveCount !== undefined && negativeCount
      !== undefined && isSyndicatedReview === false) {
      const retrievedReviewVote = JSON.parse(localStorage.getItem(`helpfulnessVote-${reviewId}`));
      return (
        <div className="review-feedback-vote">
          <span className="feedback-label">{Drupal.t('Was this review helpful?')}</span>
          {retrievedReviewVote === null ? (
            <div className={`${disabled ? 'review-feedback-vote-disabled' : 'review-feedback-vote-active'}`}>
              <span className="feedback-positive">
                <button value={positiveText} type="button" onClick={this.handleFeedbackCount(reviewId, positiveText)} disabled={disabled}>
                  <span className="feedback-option-label">{Drupal.t('yes')}</span>
                  <span className="feedback-count">
                    (
                    {positiveCount}
                    )
                  </span>
                </button>
              </span>
              <span className="feedback-negative">
                <button value={negativeText} type="button" onClick={this.handleFeedbackCount(reviewId, negativeText)} disabled={disabled}>
                  <span className="feedback-option-label">{Drupal.t('no')}</span>
                  <span className="feedback-count">
                    (
                    {negativeCount}
                    )
                  </span>
                </button>
              </span>
            </div>
          ) : (
            <div className="review-feedback-vote-disabled">
              <span className="feedback-positive">
                <button value={positiveText} type="button" onClick={this.handleFeedbackCount(reviewId, positiveText)} disabled>
                  <span className="feedback-option-label">{Drupal.t('yes')}</span>
                  <span className="feedback-count">
                    (
                    {retrievedReviewVote.positiveVoteCount}
                    )
                  </span>
                </button>
              </span>
              <span className="feedback-negative">
                <button value={negativeText} type="button" onClick={this.handleFeedbackCount(reviewId, negativeText)} disabled>
                  <span className="feedback-option-label">{Drupal.t('no')}</span>
                  <span className="feedback-count">
                    (
                    {retrievedReviewVote.negativeVoteCount}
                    )
                  </span>
                </button>
              </span>
            </div>
          )}
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
