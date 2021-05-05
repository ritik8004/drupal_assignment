import React from 'react';
import { postAPIData } from '../../../utilities/api/apiData';
import {
  setStorageInfo,
  getStorageInfo,
} from '../../../utilities/storage';
import getStringMessage from '../../../../../../js/utilities/strings';

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
          const storageList = getStorageInfo(contentType) !== null
            ? getStorageInfo(contentType) : [];
          let contentExists = false;
          if (storageList !== null) {
            const updatedStorage = storageList.map((contentStorage) => {
              // Check if current content already exists in storage.
              if (contentStorage.id === contentId) {
                const storageObj = { ...contentStorage };
                storageObj.reported = 1;
                contentExists = true;
                return storageObj;
              }
              return contentStorage;
            });
            if (contentExists) {
              setStorageInfo(JSON.stringify(updatedStorage), contentType);
            } else {
              const reportedVoteObj = {
                id: contentId,
                reported: 1,
              };
              storageList.push(reportedVoteObj);
              setStorageInfo(JSON.stringify(storageList), contentType);
            }
          }
        } else {
          Drupal.logJavascriptError(`review-${contentType}-report-feedback`, result.error);
        }
      });
    }
  }

  render() {
    const { contentId, contentType } = this.props;
    if (contentId !== undefined) {
      const reportedContentVote = getStorageInfo(contentType);
      let reported = false;
      if (reportedContentVote !== null) {
        reportedContentVote.find((review) => {
          if (review.id === contentId && review.reported === 1) {
            reported = true;
          }
          return reported;
        });
      }
      const { disabled, reportButtonText } = this.state;
      const newText = getStringMessage('reported');
      return (
        <>
          {!reported ? (
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
