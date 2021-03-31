import { postAPIData } from './api/apiData';
import {
  setStorageInfo,
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
          const helpfulnessVoteObj = { contentId, positiveCount, negativeCount };
          if (voteText === 'Positive') {
            helpfulnessVoteObj.positiveCount += 1;
          } else if (voteText === 'Negative') {
            helpfulnessVoteObj.negativeCount += 1;
          }
          setStorageInfo(helpfulnessVoteObj, `${contentType}-helpfulnessVote-${contentId}`);
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
