import React from 'react';
import { postAPIData } from '../../../utilities/api/apiData';
import {
  setStorageInfo,
  getStorageInfo,
} from '../../../utilities/storage';

class ReviewInappropriate extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      disabled: false,
      reportButtonText: Drupal.t('Report'),
    };
  }

  reportContent = (contentId, newText, contentType) => (event) => {
    event.preventDefault();
    const { disabled } = this.state;
    if (disabled) {
      return;
    }
    this.setState({
      disabled: true,
      reportButtonText: newText,
    });
    const params = `&FeedbackType=inappropriate&ContentType=${contentType}&ContentId=${contentId}`;
    const apiData = postAPIData('/data/submitfeedback.json', params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
          && result.data !== undefined
          && result.data.error === undefined) {
          const reportedVoteObj = {
            contentId,
            reported: 'Yes',
          };
          setStorageInfo(reportedVoteObj, `${contentType}-reportedVote-${contentId}`);
        } else {
          Drupal.logJavascriptError(`review-${contentType}-report-feedback`, result.error);
        }
      });
    }
  }

  render() {
    const { contentId, contentType } = this.props;
    if (contentId !== undefined) {
      const reportedContentVote = getStorageInfo(`${contentType}-reportedVote-${contentId}`);
      const { disabled, reportButtonText } = this.state;
      const newText = Drupal.t('Reported');
      return (
        <>
          {reportedContentVote === null ? (
            <span className={`feedback-report ${disabled ? 'feedback-report-disabled' : 'feedback-report-active'}`}>
              <button type="button" onClick={this.reportContent(contentId, newText, contentType)} disabled={disabled}>
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
        </>
      );
    }
    return (null);
  }
}

export default ReviewInappropriate;
