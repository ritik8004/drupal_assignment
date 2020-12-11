import dispatchCustomEvent from '../../../../js/utilities/events';

const styleFinderDyApi = (realtimeRules) => {
  const { dyStrategyId } = drupalSettings.styleFinder;
  window.DYO.recommendations.getRcomData(dyStrategyId,
    { maxProducts: 50, realtimeRules }, (err, data) => {
      let error = false;
      if (err) {
        error = true;
      }
      dispatchCustomEvent('dyGetProductRecommendation', {
        productData: data,
        error,
      });
    });
};

export default styleFinderDyApi;
