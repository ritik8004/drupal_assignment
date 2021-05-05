import { postAPIData } from './api/apiData';
import {
  setStorageInfo, getStorageInfo,
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
          const storageList = getStorageInfo(contentType) !== null
            ? getStorageInfo(contentType) : [];
          let flag = false;
          const positiveCountVal = (voteText === 'Positive') ? positiveCount + 1 : positiveCount;
          const negativeCountVal = (voteText === 'Negative') ? negativeCount + 1 : negativeCount;
          if (storageList !== null) {
            const updatedStorageList = storageList.map((contentStorage) => {
              if (contentStorage.id === contentId) {
                const storageObj = { ...contentStorage };
                storageObj.positiveCount = positiveCountVal;
                storageObj.negativeCount = negativeCountVal;
                flag = true;
                return storageObj;
              }
              return contentStorage;
            });
            if (flag) {
              setStorageInfo(JSON.stringify(updatedStorageList), contentType);
            } else {
              const helpfulnessVoteObj = {
                id: contentId,
                postiveCount: positiveCountVal,
                negativeCount: negativeCountVal,
              };
              storageList.push(helpfulnessVoteObj);
              setStorageInfo(JSON.stringify(storageList), contentType);
            }
          }
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
