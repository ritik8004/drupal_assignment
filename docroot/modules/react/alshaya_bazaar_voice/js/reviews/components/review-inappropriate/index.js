import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
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
    const { disabled } = this.state;
    if (disabled) {
      return;
    }
    this.setState({
      disabled: true,
      reportButtonText: newText,
    });
    const params = `&FeedbackType=inappropriate&ContentType=review&ContentId=${reviewId}`;
    const apiData = postAPIData('/data/submitfeedback.json', params);
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
    const { reviewId } = this.props;
    if (reviewId !== undefined) {
      const reportedReviewVote = JSON.parse(localStorage.getItem(`reportedVote-${reviewId}`));
      const { disabled, reportButtonText } = this.state;
      const newText = Drupal.t('Reported');
      return (
        <ConditionalView condition={window.innerWidth > 767}>
          {reportedReviewVote === null ? (
            <span className={`feedback-report ${disabled ? 'feedback-report-disabled' : 'feedback-report-active'}`}>
              <button type="button" onClick={this.reportReview(reviewId, newText)} disabled={disabled}>
                <span className="feedback-option-label">{reportButtonText}</span>
              </button>
            </span>
          ) : (
            <span className="feedback-report feedback-report-disabled">
              <button type="button" disabled>
                <span className="feedback-option-label">{newText}</span>
              </button>
            </span>
          )}
        </ConditionalView>
      );
    }
    return (null);
  }
}

export default ReviewInappropriate;
