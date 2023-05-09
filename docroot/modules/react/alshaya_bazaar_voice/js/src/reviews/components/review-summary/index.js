import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import ReviewInformation from '../review-info';
import ReviewDescription from '../review-desc';
import ReviewHistogram from '../review-histogram';
import { removeFullScreenLoader, showFullScreenLoader }
  from '../../../../../../js/utilities/showRemoveFullScreenLoader';
import ReviewSorting from '../review-sorting';
import ReviewFilters from '../review-filters';
import ReviewFiltersDisplay from '../review-filters-display';
import EmptyMessage from '../../../utilities/empty-message';
import ReviewRatingsFilter from '../review-ratings-filter';
import PostReviewMessage from '../reviews-full-submit/post-review-message';
import Pagination from '../review-pagination';
import {
  getbazaarVoiceSettings,
  getUserDetails,
  fetchAPIData,
} from '../../../utilities/api/request';
import WriteReviewButton from '../reviews-full-submit';
import getStringMessage from '../../../../../../js/utilities/strings';
import DisplayStar from '../../../rating/components/stars';
import { createUserStorage } from '../../../utilities/user_util';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { trackPassiveAnalytics, trackFeaturedAnalytics, trackContentImpression } from '../../../utilities/analytics';

let bazaarVoiceSettings = null;

export default class ReviewSummary extends React.Component {
  isComponentMounted = true;

  constructor(props) {
    super(props);
    bazaarVoiceSettings = getbazaarVoiceSettings(props.productId);
    this.state = {
      reviewsSummary: '',
      reviewsProduct: '',
      reviewsComment: '',
      reviewsAuthors: '',
      currentSortOption: 'none',
      currentFilterOptions: [],
      noResultmessage: null,
      totalReviews: '',
      currentTotal: '',
      postReviewData: '',
      offset: 0,
      numberOfPages: 0,
      currentPage: 1,
      prevButtonDisabled: true,
      nextButtonDisabled: false,
      analyticsState: false,
      loadMoreLimit: '',
      paginationLimit: '',
      userDetails: {
        productReview: null,
      },
    };
    this.nextPage = this.nextPage.bind(this);
    this.previousPage = this.previousPage.bind(this);
    this.changePaginationButtonStatus = this.changePaginationButtonStatus.bind(this);
    this.loadMore = this.loadMore.bind(this);
  }

  /**
   * Get Review results and product statistical data.
   */
  componentDidMount() {
    bazaarVoiceSettings = getbazaarVoiceSettings();

    if (!Drupal.hasValue(bazaarVoiceSettings)) {
      return;
    }

    this.setState({
      loadMoreLimit: bazaarVoiceSettings.reviews.bazaar_voice.reviews_initial_load,
      paginationLimit: bazaarVoiceSettings.reviews.bazaar_voice.reviews_per_page,
    });

    getUserDetails().then((result) => {
      this.setState({ userDetails: result }, () => {
        const { userDetails } = this.state;
        this.isComponentMounted = true;
        // Listen to the review post event.
        if (userDetails && Object.keys(userDetails).length !== 0) {
          createUserStorage(userDetails.user.userId, userDetails.user.emailId);
          this.getReviews();
        }
      });
    });
    document.addEventListener('reviewPosted', this.eventListener, false);
    document.addEventListener('handlePagination', this.handlePagination);
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

  getReviews = (extraParams, explicitTrigger = false, offset = this.getOffsetValue()) => {
    showFullScreenLoader();

    let sortParams = '';
    let filterParams = '';
    // Set default sorting options.
    const sortOptions = bazaarVoiceSettings.reviews.bazaar_voice.sorting_options;
    const { currentSortOption, analyticsState } = this.state;
    if (sortOptions.length > 0
      && (extraParams === undefined || currentSortOption === 'none')) {
      let optionVal = '';
      sortOptions.map((option) => {
        if (option.value !== 'none') {
          optionVal += `${option.value},`;
        }
        return optionVal;
      });
      optionVal = optionVal.replace(/,\s*$/, '');
      sortParams = `&sort=${optionVal}`;
    }
    if (extraParams !== undefined) {
      // Add sorting parameters.
      if (extraParams.currentSortOption.length > 0 && currentSortOption !== 'none') {
        sortParams = `&${extraParams.sortType}=${extraParams.currentSortOption}`;
      }
      // Add filtering parameters.
      if (extraParams.currentFilterOptions.length > 0) {
        extraParams.currentFilterOptions.map((item) => {
          filterParams += `&${extraParams.filterType}=${item.value}`;
          return filterParams;
        });
      }
    }

    // Get review data from BazaarVoice based on available parameters.
    const apiUri = '/data/reviews.json';
    const reviewLimit = this.getReviewLimit();
    const params = `&filter=productid:${bazaarVoiceSettings.productid}&filter=contentlocale:${bazaarVoiceSettings.reviews.bazaar_voice.content_locale}&Include=${bazaarVoiceSettings.reviews.bazaar_voice.Include}&Stats=${bazaarVoiceSettings.reviews.bazaar_voice.stats}&FilteredStats=${bazaarVoiceSettings.reviews.bazaar_voice.stats}&Limit=${reviewLimit}&Offset=${offset}${sortParams}${filterParams}`;
    const apiData = fetchAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        removeFullScreenLoader();
        if (result.error === undefined && result.data !== undefined) {
          if (result.data.Results.length > 0) {
            if (extraParams === undefined) {
              this.setState({
                totalReviews: result.data.TotalResults,
                reviewsProduct: result.data.Includes.Products,
                reviewsComment: result.data.Includes.Comments,
                reviewsAuthors: result.data.Includes.Authors,
                numberOfPages: Math.ceil(result.data.TotalResults / reviewLimit),
              }, () => {
                const { currentPage, numberOfPages } = this.state;
                this.changePaginationButtonStatus(currentPage, numberOfPages);
              });
            }
            this.setState({
              currentTotal: result.data.TotalResults,
              reviewsSummary: result.data.Results,
              reviewsProduct: result.data.Includes.Products,
              reviewsComment: result.data.Includes.Comments,
              reviewsAuthors: result.data.Includes.Authors,
              noResultmessage: null,
              numberOfPages: Math.ceil(result.data.TotalResults / reviewLimit),
            }, () => {
              const { currentPage, numberOfPages } = this.state;
              this.changePaginationButtonStatus(currentPage, numberOfPages);
            });
          } else {
            this.setState({
              totalReviews: result.data.TotalResults,
              currentTotal: result.data.TotalResults,
              noResultmessage: getStringMessage('no_review_found'),
            });
          }
          // Track reviews into bazaarvoice analytics.
          if (!analyticsState) {
            trackPassiveAnalytics(result.data);
            this.setState({
              analyticsState: true,
            });
          }
          trackContentImpression(result.data);
        } else {
          removeFullScreenLoader();
          Drupal.logJavascriptError('review-summary', result.error);
        }

        if (explicitTrigger) {
          dispatchCustomEvent('handlePaginationComplete', {});
        }
      });
    }
  };

  /**
   * Call next or previous page function.
   */
  handlePagination = (event) => {
    event.preventDefault();
    const buttonValue = event.detail;
    if (buttonValue === 'prev') {
      this.previousPage();
    }
    if (buttonValue === 'next') {
      this.nextPage();
    }
  };

  /**
   * Get offset value.
   */
  getOffsetValue() {
    const { offset } = this.state;
    return offset;
  }

  /**
   * Get Limit value for reviews.
   */
  getReviewLimit() {
    const { loadMoreLimit, paginationLimit } = this.state;
    if (bazaarVoiceSettings.reviews.bazaar_voice.reviews_pagination_type === 'pagination') {
      return paginationLimit;
    }
    return loadMoreLimit;
  }

  /**
   * Process the sort option value when get from the select list.
   */
  processSortOption = (option) => {
    const initialLimit = bazaarVoiceSettings.reviews.bazaar_voice.reviews_initial_load;
    this.setState({
      currentSortOption: option.value, currentPage: 1, offset: 0, loadMoreLimit: initialLimit,
    }, () => {
      this.processSortAndFilters();
    });
  }

  /**
   * Add the filter option value when get from the select list.
   */
  addFilters = (option) => {
    const { currentFilterOptions } = this.state;
    const initialLimit = bazaarVoiceSettings.reviews.bazaar_voice.reviews_initial_load;
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

    this.setState({ currentPage: 1, offset: 0, loadMoreLimit: initialLimit }, () => {
      this.processSortAndFilters();
    });
  }

  /**
   * Process sort + filters options value and get reviews from bazaarvoice.
   */
  processSortAndFilters = () => {
    const extraParams = [];
    const { currentSortOption } = this.state;
    const { currentFilterOptions } = this.state;
    extraParams.sortType = 'sort';
    extraParams.filterType = 'filter';
    extraParams.currentSortOption = currentSortOption;
    extraParams.currentFilterOptions = currentFilterOptions;
    // Get reviews from bazaarvoice.
    this.getReviews(extraParams, true);
  }

  /**
   * Remove the filter option value when get from the select list.
   */
  removeFilters = (option) => {
    let { currentFilterOptions } = this.state;
    const initialLimit = bazaarVoiceSettings.reviews.bazaar_voice.reviews_initial_load;
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

    this.setState({ currentPage: 1, offset: 0, loadMoreLimit: initialLimit }, () => {
      this.processSortAndFilters();
    });
  }

  /**
   * Get the next page reviews when user clicks on next button.
   */
  nextPage() {
    const {
      offset, paginationLimit,
    } = this.state;
    this.setState({ offset: offset + paginationLimit }, () => {
      this.processSortAndFilters();
      this.setState((prevState) => ({ currentPage: prevState.currentPage + 1 }), () => {
        const { currentPage, numberOfPages } = this.state;
        this.changePaginationButtonStatus(currentPage, numberOfPages);
      });
    });
    // Process paginate click data as user clicks on next link.
    const analyticsData = {
      type: 'Used',
      name: 'paginate',
      detail1: 'next',
      detail2: '',
    };
    trackFeaturedAnalytics(analyticsData);
  }

  /**
   * Get the previous page reviews when user clicks on previous button.
   */
  previousPage() {
    const {
      offset, paginationLimit,
    } = this.state;
    this.setState({ offset: offset - paginationLimit }, () => {
      this.processSortAndFilters();
      this.setState((prevState) => ({ currentPage: prevState.currentPage - 1 }), () => {
        const { currentPage, numberOfPages } = this.state;
        this.changePaginationButtonStatus(currentPage, numberOfPages);
      });
    });
    // Process paginate click data as user clicks on previous link.
    const analyticsData = {
      type: 'Used',
      name: 'paginate',
      detail1: 'previous',
      detail2: '',
    };
    trackFeaturedAnalytics(analyticsData);
  }

  /**
   * Change button status to disabled or enabled depending on data.
   */
  changePaginationButtonStatus(currentPage, numberOfPages) {
    // Change previous button status.
    if (currentPage > 1 && currentPage <= numberOfPages) {
      this.setState({ prevButtonDisabled: false });
    } else {
      this.setState({ prevButtonDisabled: true });
    }
    // Change next button status.
    if (currentPage >= 1 && currentPage < numberOfPages) {
      this.setState({ nextButtonDisabled: false });
    } else {
      this.setState({ nextButtonDisabled: true });
    }
  }

  loadMore() {
    const initialLimit = bazaarVoiceSettings.reviews.bazaar_voice.reviews_on_loadmore;
    this.setState((prev) => ({ loadMoreLimit: prev.loadMoreLimit + initialLimit }), () => {
      this.processSortAndFilters();
    });
    // Process load more click data as user clicks on load more link.
    const analyticsData = {
      type: 'Used',
      name: 'loadmore',
      detail1: 'reviews',
      detail2: '',
    };
    trackFeaturedAnalytics(analyticsData);
  }

  render() {
    const {
      reviewsSummary,
      reviewsProduct,
      reviewsComment,
      reviewsAuthors,
      currentSortOption,
      currentFilterOptions,
      noResultmessage,
      totalReviews,
      currentTotal,
      postReviewData,
      prevButtonDisabled,
      nextButtonDisabled,
      currentPage,
      numberOfPages,
      loadMoreLimit,
      userDetails,
    } = this.state;
    const {
      isNewPdpLayout,
      isWriteReview,
      productId,
    } = this.props;

    let newPdp = isNewPdpLayout;
    newPdp = (newPdp === undefined) ? false : newPdp;
    const reviewSettings = bazaarVoiceSettings.reviews.bazaar_voice.reviews_pagination_type;
    // Totalreviews count is emtpy.
    if (totalReviews === '') {
      return null;
    }
    // Totalreviews count is 0.
    if (totalReviews === 0) {
      return (
        <>
          <div className="histogram-data-section">
            <div className="rating-wrapper">
              <div className="overall-summary-title">{getStringMessage('ratings_reviews')}</div>
              <div className="empty-review-summary">
                <div className="no-review-section">
                  <p className="no-review-title">{getStringMessage('no_reviews_yet')}</p>
                  <p className="no-review-msg">{getStringMessage('first_to_review')}</p>
                </div>
                <WriteReviewButton
                  reviewedByCurrentUser={userDetails.productReview !== null}
                  newPdp={newPdp}
                  isWriteReview={isWriteReview || false}
                  productId={productId}
                />
              </div>
            </div>
          </div>
          <ConditionalView condition={postReviewData !== ''}>
            <PostReviewMessage postReviewData={postReviewData} />
          </ConditionalView>
        </>
      );
    }
    // Totalreviews count is more than 0.
    return (
      <div className="reviews-wrapper">
        <div className="histogram-data-section">
          <div className="rating-wrapper">
            <ReviewHistogram
              overallSummary={reviewsProduct}
              isNewPdpLayout={isNewPdpLayout}
              reviewedByCurrentUser={userDetails.productReview !== null}
              isWriteReview={isWriteReview || false}
              productId={productId}
            />
            <div className="sorting-filter-wrapper">
              <div className="sorting-filter-title-block">{getStringMessage('filter_sort')}</div>
              <ReviewSorting
                currentOption={currentSortOption}
                sortOptions={bazaarVoiceSettings.reviews.bazaar_voice.sorting_options}
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
        <ConditionalView condition={noResultmessage === null}>
          <div id="review-summary-wrapper">
            <ConditionalView condition={postReviewData !== ''}>
              <PostReviewMessage postReviewData={postReviewData} />
            </ConditionalView>
            {Object.keys(reviewsSummary).map((item) => (
              <div key={reviewsSummary[item].Id}>
                {reviewsSummary[item].ModerationStatus === 'APPROVED'
                  && reviewsSummary[item].AuthorId in reviewsAuthors
                  && (
                  <div className="review-summary" key={reviewsSummary[item].Id}>
                    <ConditionalView condition={(window.innerWidth < 768) || newPdp}>
                      <DisplayStar
                        starPercentage={reviewsSummary[item].Rating}
                      />
                      <div id={`${reviewsSummary[item].Id}-review-title`} className="review-title">{reviewsSummary[item].Title}</div>
                    </ConditionalView>
                    <ReviewInformation
                      reviewInformationData={reviewsSummary[item]}
                      reviewTooltipInfo={reviewsAuthors[reviewsSummary[item]
                        .AuthorId].ReviewStatistics}
                      isNewPdpLayout={isNewPdpLayout}
                      showLocationFilter={bazaarVoiceSettings.reviews
                        .bazaar_voice.show_location_filter}
                    />
                    <ReviewDescription
                      reviewDescriptionData={reviewsSummary[item]}
                      reviewsComment={reviewsComment}
                      isNewPdpLayout={isNewPdpLayout}
                    />
                  </div>
                  )}
              </div>
            ))}
          </div>
          <ConditionalView condition={reviewSettings === 'pagination'}>
            <Pagination
              currentPage={currentPage}
              numberOfPages={numberOfPages}
              prevButtonDisabled={prevButtonDisabled}
              nextButtonDisabled={nextButtonDisabled}
            />
          </ConditionalView>
          <ConditionalView condition={reviewSettings === 'load_more' && loadMoreLimit < currentTotal}>
            <div className="load-more-wrapper">
              <button onClick={this.loadMore} type="button" className="load-more">{getStringMessage('load_more')}</button>
            </div>
          </ConditionalView>
        </ConditionalView>
        <ConditionalView condition={noResultmessage !== null}>
          <EmptyMessage emptyMessage={noResultmessage} />
        </ConditionalView>
      </div>
    );
  }
}
