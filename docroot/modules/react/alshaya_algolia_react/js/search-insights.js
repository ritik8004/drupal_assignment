/**
 * @file
 * This file contains code for integration with Algolia Insights for analytics.
 */

var ALGOLIA_INSIGHTS_SRC = drupalSettings.path.baseUrl + drupalSettings.algoliaSearch.insightsJsUrl;

!function(e,a,t,n,s,i,c){e.AlgoliaAnalyticsObject=s,e.aa=e.aa||function(){
(e.aa.queue=e.aa.queue||[]).push(arguments)},i=a.createElement(t),c=a.getElementsByTagName(t)[0],
i.async=1,i.src=ALGOLIA_INSIGHTS_SRC,c.parentNode.insertBefore(i,c)
}(window,document,"script",0,"aa");

// Initialize library
aa('init', {
  appId: drupalSettings.algoliaSearch.application_id,
  apiKey: drupalSettings.algoliaSearch.api_key
});
