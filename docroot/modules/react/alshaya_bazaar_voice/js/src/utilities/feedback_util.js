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
  const feedbackStorage = getStorageInfo(contentType);
  let checkFeedbackVote = false;
  if (feedbackStorage !== null) {
    const contentStorage = feedbackStorage.find((content) => {
      if (content.id === contentId) {
        if ((voteType === 'positiveCount' || voteType === 'negativeCount')
          && (content.positiveCount !== undefined && content.negativeCount !== undefined)) {
          checkFeedbackVote = true;
        } else if (voteType === 'report' && content.reported !== undefined) {
          checkFeedbackVote = true;
        }
      }
      return checkFeedbackVote;
    });
    if (checkFeedbackVote) {
      return contentStorage;
    }
  }
  return null;
};

export default {
  handleFeedbackSubmit,
  getFeedbackInfo,
};
