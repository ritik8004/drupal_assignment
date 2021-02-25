import React from 'react';

class ReviewFeedbackNegative extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      disabled: false,
      negativeCount: props.negativeCount,
    };
  }

  handleNegativeFeedbackCount = (reviewId, voteText) => (e) => {
    e.preventDefault();
    const { disabled, negativeCount } = this.state;
    if (disabled) {
      return;
    }
    this.setState({ disabled: true });
    const helpfulnessVoteObj = { reviewId, negativeCount };
    const event = new CustomEvent('handleApiResponse', {
      bubbles: true,
      detail: {
        reviewId,
        voteText,
      },
    });
    document.dispatchEvent(event);
    this.setState({ negativeCount: negativeCount + 1 });
    helpfulnessVoteObj.negativeCount += 1;
    localStorage.setItem(`helpfulnessVote-${reviewId}`, JSON.stringify(helpfulnessVoteObj));
  }

  render() {
    const { disabled, negativeCount } = this.state;
    const { reviewId } = this.props;
    const negativeText = 'Negative';
    const retrievedReviewVote = JSON.parse(localStorage.getItem(`helpfulnessVote-${reviewId}`));
    if (retrievedReviewVote != null && !disabled) {
      this.setState({ disabled: true });
    }
    if (reviewId !== undefined && negativeText !== undefined) {
      return (
        <span className="feedback-negative">
          <button value={negativeText} type="button" onClick={this.handleNegativeFeedbackCount(reviewId, negativeText)} disabled={disabled}>
            <span className="feedback-option-label">{Drupal.t('no')}</span>
            <span className="feedback-count">
              (
              {negativeCount}
              )
            </span>
          </button>
        </span>
      );
    }
    return (null);
  }
}

export default ReviewFeedbackNegative;
