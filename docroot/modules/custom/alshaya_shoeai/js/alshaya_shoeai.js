/**
 * @file
 * Contains Alshaya ShoeAI functionality.
 */

(function (drupalSettings) {  
  let shoeAi = drupalSettings.shoeai;
  if (shoeAiStatus(shoeAi)) {
    let language = drupalSettings.path.currentLanguage;
    let script = document.createElement('script');
    script.src = 'https://shoesize.me/assets/plugin/loader.js';
    script.type = 'text/javascript';
    script.async = true;  
    // newRecommendation callback is only for PDP to set the default size as per recommendation from ShoeAI.
    let newRecommendation = function(recommendation){
      shoe_size_recommendation(recommendation)
    };
    script.text = '{shopID:"' + shoeAi.shopId + '", locale:"' +
     language + '",scale: "eu", zeroHash:"' + shoeAi.zeroHash + '",newRecommendation:' + newRecommendation + '}';
    document.body.appendChild(script);
  }
})(drupalSettings);

function shoeAiStatus(shoeAi) {
  if (shoeAi && shoeAi.status != null && shoeAi.status == 1) {
    return true;
  } else {
    return false;
  }
}

// Helper function for getting recommended shoesize from shoeai and select the size in PDP if available.
window.shoe_size_recommendation = (recommendation) => {
  const recommendedSize = recommendation.size['eu'] ? recommendation.size['eu'].replace('.0','') : null;
  // work only if recommendedSize is not null.
  if (recommendedSize) {
    const sizes = document.querySelectorAll('.magv2-select-list-name');
    for (const size of sizes) {
      if (size.innerText == recommendedSize) {
        size.click();
        return;
      }
    }
  }
}
