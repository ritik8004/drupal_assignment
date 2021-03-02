import React from 'react';
import DisplayStar from '../../../rating/components/stars/DisplayStar';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewInformation from '../review-info';
import ReviewDescription from '../review-desc';
import ReviewHistogram from '../review-histogram';
import { fetchAPIData } from '../../../utilities/api/apiData';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import ReviewSorting from '../review-sorting';
import ReviewFilters from '../review-filters';
import ReviewFiltersDisplay from '../review-filters-display';
import EmptyMessage from '../../../utilities/empty-message';
import ReviewRatingsFilter from '../review-ratings-filter';
import PostReviewMessage from '../reviews-full-submit/post-review-message';

export default class ReviewSummary extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    this.state = {
      reviewsSummary: '',
      reviewsProduct: '',
      currentSortOption: '',
      currentFilterOptions: [],
      noResultmessage: null,
      totalReviews: '',
      currentTotal: '',
      postReviewData: '',
    };
  }

  /**
   * Get Review results and product statistical data.
   */
  componentDidMount() {
    this.isComponentMounted = true;
    // Listen to the review post event.
    document.addEventListener('reviewPosted', this.eventListener, false);

    this.getReviews();
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('reviewPosted', this.eventListener, false);
  }

  eventListener = (e) => {
    if (!this.isComponentMounted) {
      return;
    }

    if (e.detail.SubmissionId !== null) {
      this.setState({
        postReviewData: e.detail,
      });
    }
  }

  getReviews = (options, type) => {
    showFullScreenLoader();
    // Add sorting parameters.
    const sortParams = (type === 'sort') ? `&${type}=${options}` : '';

    // Add filtering parameters.
    let filterParams = '';
    if (type === 'filter' && options.length > 0) {
      options.map((item) => {
        filterParams += `&${type}=${item.value}`;
        return filterParams;
      });
    }

    // Get review data from BazaarVoice based on available parameters.
    const apiUri = '/data/reviews.json';
    const params = `&filter=productid:${drupalSettings.bazaar_voice.productid}&Include=${drupalSettings.bazaar_voice.Include}&stats=${drupalSettings.bazaar_voice.stats}${sortParams}${filterParams}`;
    const apiData = fetchAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        removeFullScreenLoader();
        if (result.error === undefined && result.data !== undefined) {
          if (result.data.Results.length > 0) {
            if (type === undefined) {
              this.setState({
                totalReviews: result.data.TotalResults,
                reviewsProduct: result.data.Includes.Products,
              });
            }

            this.setState({
              currentTotal: result.data.TotalResults,
              reviewsSummary: result.data.Results,
              noResultmessage: null,
            });
          } else {
            this.setState({
              currentTotal: result.data.TotalResults,
              noResultmessage: Drupal.t('No review found.'),
            });
          }
        } else {
          removeFullScreenLoader();
          Drupal.logJavascriptError('review-summary', result.error);
        }
      });
    }
  };

  /**
   * Process the sort option value when get from the select list.
   */
  processSortOption = (option) => {
    this.setState({ currentSortOption: option.value });

    this.getReviews(option.value, 'sort');
  }

  /**
   * Add the filter option value when get from the select list.
   */
  addFilters = (option) => {
    const { currentFilterOptions } = this.state;

    // Remove multi filter option from same filter list.
    if (currentFilterOptions.length > 0) {
      currentFilterOptions.map((item, key) => {
        const contextKey = option.value.split(':');
        const itemKey = item.value.split(':');
        if (itemKey[0] === contextKey[0]) {
          if (key !== -1) {
            currentFilterOptions.splice(key, 1);
          }
        }
        return currentFilterOptions;
      });
    }
    currentFilterOptions.push(option);

    this.getReviews(currentFilterOptions, 'filter');
  }

  /**
   * Remove the filter option value when get from the select list.
   */
  removeFilters = (option) => {
    let { currentFilterOptions } = this.state;
    const index = currentFilterOptions.indexOf(option);
    if (index !== -1) {
      currentFilterOptions.splice(index, 1);
    }

    if (option === 'clearall') {
      currentFilterOptions = [];
      this.setState({
        currentFilterOptions,
      });
    }

    this.getReviews(currentFilterOptions, 'filter');
  }

  render() {
    const {
      reviewsSummary,
      reviewsProduct,
      currentSortOption,
      currentFilterOptions,
      noResultmessage,
      totalReviews,
      currentTotal,
      postReviewData,
    } = this.state;

    return (
      <div className="reviews-wrapper">
        <div className="histogram-data-section">
          <div className="rating-wrapper">
            <ReviewHistogram overallSummary={reviewsProduct} />
            <div className="sorting-filter-wrapper">
              <div className="sorting-filter-title-block">{Drupal.t('Filter + Sort')}</div>
              <ReviewSorting
                currentOption={currentSortOption}
                sortOptions={drupalSettings.bazaar_voice.sorting_options}
                processingCallback={this.processSortOption}
              />
              <ReviewRatingsFilter
                currentOptions={currentFilterOptions}
                filterOptions={reviewsProduct}
                processingCallback={this.addFilters}
              />
              <ReviewFilters
                currentOptions={currentFilterOptions}
                filterOptions={reviewsProduct}
                processingCallback={this.addFilters}
              />
            </div>
            <ReviewFiltersDisplay
              currentOptions={currentFilterOptions}
              processingCallback={this.removeFilters}
              totalReviews={totalReviews}
              currentTotal={currentTotal}
            />
          </div>
        </div>
        {noResultmessage === null
          && (
            <>
              {postReviewData !== ''
                && (
                  <PostReviewMessage postReviewData={postReviewData} />)}
              {Object.keys(reviewsSummary).map((item) => (
                <div className="review-summary" key={reviewsSummary[item].Id}>
                  <ConditionalView condition={window.innerWidth < 768}>
                    <DisplayStar
                      starPercentage={reviewsSummary[item].Rating}
                    />
                    <div className="review-title">{reviewsSummary[item].Title}</div>
                  </ConditionalView>
                  <ReviewInformation
                    reviewInformationData={reviewsSummary[item]}
                    reviewTooltipInfo={
                    reviewsProduct[reviewsSummary[item].ProductId].ReviewStatistics
                  }
                  />
                  <ReviewDescription
                    reviewDescriptionData={reviewsSummary[item]}
                  />
                </div>
              ))}
            </>
          )}
        {noResultmessage !== null
          && (
            <EmptyMessage emptyMessage={noResultmessage} />
          )}
      </div>
    );
  }
}
