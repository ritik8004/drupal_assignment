import { postAPIData } from './api/apiData';
import {
  updateStorageInfo, getStorageInfo,
} from './storage';

export const handleFeedbackSubmit = (contentId, voteText,
  contentType, positiveCount, negativeCount) => {
  if (contentId !== undefined && voteText !== undefined && contentType !== undefined) {
    const params = `&FeedbackType=helpfulness&ContentType=${contentType}&ContentId=${contentId}&Vote=${voteText}`;
    const apiData = postAPIData('/data/submitfeedback.json', params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
            && result.data !== undefined
            && result.data.error === undefined) {
          const positiveCountVal = (voteText === 'Positive') ? positiveCount + 1 : positiveCount;
          const negativeCountVal = (voteText === 'Negative') ? negativeCount + 1 : negativeCount;
          const helpfulnessVoteObj = {
            id: contentId,
            positiveCount: positiveCountVal,
            negativeCount: negativeCountVal,
          };
          updateStorageInfo(contentType, helpfulnessVoteObj, contentId);
        } else {
          Drupal.logJavascriptError(`review-${contentType}-feedback-submit`, result.error);
        }
      });
    }
  }
};

export const getFeedbackInfo = (contentType, contentId, voteType) => {
  const retrievedFeedback = getStorageInfo(contentType);
  let checkFeedbackVote = false;
  if (retrievedFeedback !== null) {
    if (voteType === 'positiveCount' || voteType === 'negativeCount') {
      if (retrievedFeedback.positiveCount >= 0 && retrievedFeedback.negativeCount >= 0) {
        checkFeedbackVote = true;
      }
    }
    if (voteType === 'report') {
      if (retrievedFeedback.reported === 0) {
        checkFeedbackVote = true;
      }
    }
    const contentStorage = retrievedFeedback.find((storage) => (storage.id === contentId
      && checkFeedbackVote));
    if (contentStorage !== undefined) {
      return contentStorage;
    }
  }
  return null;
};

export default {
  handleFeedbackSubmit,
  getFeedbackInfo,
};
