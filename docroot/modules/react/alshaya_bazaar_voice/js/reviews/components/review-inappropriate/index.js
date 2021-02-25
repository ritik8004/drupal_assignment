import React from 'react';
import { postAPIData } from '../../../utilities/api/apiData';

class ReviewInappropriate extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      disabled: false,
      reportButtonText: Drupal.t('Report'),
    };
  }

  reportReview = (reviewId, newText) => (event) => {
    event.preventDefault();
    const { disabled: buttonState } = this.state;
    if (buttonState) {
      return;
    }
    this.setState({ disabled: true });
    this.setState({ reportButtonText: newText });
    const apiUri = '/data/submitfeedback.json';
    const params = `&FeedbackType=inappropriate&ContentType=review&ContentId=${reviewId}`;
    const apiData = postAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
      && result.data !== undefined
      && result.data.error === undefined) {
          const reportedVoteObj = {
            reviewId,
            reported: 'Yes',
          };
          localStorage.setItem(`reportedVote-${reviewId}`, JSON.stringify(reportedVoteObj));
        }
      });
    }
  }

  render() {
    const { ReviewId: reviewId } = this.props;
    if (reviewId !== undefined) {
      const reportedReviewVote = JSON.parse(localStorage.getItem(`reportedVote-${reviewId}`));
      const { disabled: buttonState } = this.state;
      const { reportButtonText: text } = this.state;
      const newText = Drupal.t('Reported');
      return (
        <>
          {reportedReviewVote === null ? (
            <span className={`feedback-report ${buttonState ? 'feedback-report-disabled' : 'feedback-report-active'}`}>
              <button type="button" onClick={this.reportReview(reviewId, newText)} disabled={buttonState}>
                <span className="feedback-option-label">{text}</span>
              </button>
            </span>
          ) : (
            <span className="feedback-report feedback-report-disabled">
              <button type="button" disabled>
                <span className="feedback-option-label">{newText}</span>
              </button>
            </span>
          )}
        </>
      );
    }
    return (null);
  }
}

export default ReviewInappropriate;
