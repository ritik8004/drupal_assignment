import React from 'react';
import { postAPIData } from '../../../utilities/api/apiData';
import ReviewInappropriate from '../review-inappropriate';

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
    const { disabled: buttonState } = this.state;
    if (buttonState) {
      return;
    }
    this.setState({ disabled: true });
    const apiUri = '/data/submitfeedback.json';
    const params = `&FeedbackType=helpfulness&ContentType=review&ContentId=${reviewId}&Vote=${voteText}`;
    const apiData = postAPIData(apiUri, params);
    const { positiveCount: positiveVoteCount } = this.state;
    const { negativeCount: negativeVoteCount } = this.state;
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
          && result.data !== undefined
          && result.data.error === undefined) {
          const helpfulnessVoteObj = {
            reviewId,
            positiveVoteCount,
            negativeVoteCount,
          };
          if (voteText === 'Positive') {
            this.setState({ positiveCount: positiveVoteCount + 1 });
            helpfulnessVoteObj.positiveVoteCount += 1;
            localStorage.setItem(`helpfulnessVote-${reviewId}`, JSON.stringify(helpfulnessVoteObj));
          }
          if (voteText === 'Negative') {
            this.setState({ negativeCount: negativeVoteCount + 1 });
            helpfulnessVoteObj.negativeVoteCount += 1;
            localStorage.setItem(`helpfulnessVote-${reviewId}`, JSON.stringify(helpfulnessVoteObj));
          }
        }
      });
    }
  };

  render() {
    const { ReviewId: reviewId } = this.props;
    const { IsSyndicatedReview: isSyndicatedReview } = this.props;
    const { disabled: buttonState } = this.state;
    const { positiveCount: positiveVoteCount } = this.state;
    const { negativeCount: negativeVoteCount } = this.state;
    const positiveText = 'Positive';
    const negativeText = 'Negative';
    if (reviewId !== undefined && positiveVoteCount !== undefined && negativeVoteCount
      !== undefined && isSyndicatedReview === false) {
      const retrievedReviewVote = JSON.parse(localStorage.getItem(`helpfulnessVote-${reviewId}`));
      return (
        <div className="review-feedback-vote">
          <span className="feedback-label">{Drupal.t('Was this review helpful?')}</span>
          {retrievedReviewVote === null ? (
            <div className={`${buttonState ? 'review-feedback-vote-disabled' : 'review-feedback-vote-active'}`}>
              <span className="feedback-positive">
                <button value={positiveText} type="button" onClick={this.handleFeedbackCount(reviewId, positiveText)} disabled={buttonState}>
                  <span className="feedback-option-label">{Drupal.t('yes')}</span>
                  <span className="feedback-count">
                    (
                    {positiveVoteCount}
                    )
                  </span>
                </button>
              </span>
              <span className="feedback-negative">
                <button value={negativeText} type="button" onClick={this.handleFeedbackCount(reviewId, negativeText)} disabled={buttonState}>
                  <span className="feedback-option-label">{Drupal.t('no')}</span>
                  <span className="feedback-count">
                    (
                    {negativeVoteCount}
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
            ReviewId={reviewId}
          />
        </div>
      );
    }
    return (null);
  }
}

export default ReviewFeedback;
