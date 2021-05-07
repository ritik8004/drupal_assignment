import { postAPIData } from './api/apiData';
import {
  updateStorageInfo,
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

export default {
  handleFeedbackSubmit,
};
