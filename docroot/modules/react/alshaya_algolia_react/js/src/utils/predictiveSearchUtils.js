const openPredictiveSearch = () => {
  const predictiveSearch = document.getElementsByClassName('predictive-search');
  if (predictiveSearch.length > 0) {
    predictiveSearch[0].classList.add('predictive-search--open');
    document.body.classList.add('show-predictive-search');
  }
};

const closePredictiveSearch = () => {
  const predictiveSearch = document.getElementsByClassName(
    'predictive-search--open',
  );
  if (predictiveSearch.length > 0) {
    predictiveSearch[0].classList.remove('predictive-search--open');
    document.body.classList.remove('show-predictive-search');
  }
};

export { openPredictiveSearch, closePredictiveSearch };
