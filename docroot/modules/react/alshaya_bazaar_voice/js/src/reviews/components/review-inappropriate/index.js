import React from 'react';
import { postAPIData } from '../../../utilities/api/request';
import {
  updateStorageInfo,
} from '../../../utilities/storage';
import getStringMessage from '../../../../../../js/utilities/strings';
import { getFeedbackInfo } from '../../../utilities/feedback_util';
import { trackFeaturedAnalytics } from '../../../utilities/analytics';

class ReviewInappropriate extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      disabled: false,
      reportButtonText: getStringMessage('report'),
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
          const reportVoteObj = {
            id: contentId,
            reported: 1,
          };
          updateStorageInfo(contentType, reportVoteObj, contentId);
          // Process report feedback click data as user clicks on report.
          const analyticsData = {
            type: 'Used',
            name: 'helpfulness',
            detail1: 'reported',
            detail2: contentType,
          };
          trackFeaturedAnalytics(analyticsData);
        } else {
          Drupal.logJavascriptError(`review-${contentType}-report-feedback`, result.error);
        }
      });
    }
  }

  render() {
    const { contentId, contentType } = this.props;
    if (contentId !== undefined) {
      const feedbackStorage = getFeedbackInfo(contentType, contentId, 'report');
      const { disabled, reportButtonText } = this.state;
      const newText = getStringMessage('reported');
      return (
        <>
          {feedbackStorage === null ? (
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
