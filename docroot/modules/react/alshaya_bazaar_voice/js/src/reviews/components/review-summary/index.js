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
import Pagination from '../review-pagination';
import { getbazaarVoiceSettings } from '../../../utilities/api/request';
import WriteReviewButton from '../reviews-full-submit';

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
      offset: 0,
      numberOfPages: 0,
      currentPage: 1,
      prevButtonDisabled: true,
      nextButtonDisabled: false,
      bazaarVoiceSettings: getbazaarVoiceSettings(),
    };
    this.nextPage = this.nextPage.bind(this);
    this.previousPage = this.previousPage.bind(this);
    this.changePaginationButtonStatus = this.changePaginationButtonStatus.bind(this);
  }

  /**
   * Get Review results and product statistical data.
   */
  componentDidMount() {
    this.isComponentMounted = true;
    // Listen to the review post event.
    document.addEventListener('reviewPosted', this.eventListener, false);
    document.addEventListener('handlePagination', this.handlePagination);

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

  getReviews = (options, type, explicitTrigger = false, offset = this.getOffsetValue()) => {
    showFullScreenLoader();
    const { bazaarVoiceSettings } = this.state;
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
    const limit = this.getLimitConfigValue(bazaarVoiceSettings);
    const params = `&filter=productid:${bazaarVoiceSettings.productid}&filter=contentlocale:${bazaarVoiceSettings.reviews.bazaar_voice.content_locale}&Include=${bazaarVoiceSettings.reviews.bazaar_voice.Include}&stats=${bazaarVoiceSettings.reviews.bazaar_voice.stats}&Limit=${limit}&Offset=${offset}${sortParams}${filterParams}`;
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
                numberOfPages: Math.ceil(result.data.TotalResults / limit),
              }, () => {
                const { currentPage, numberOfPages } = this.state;
                this.changePaginationButtonStatus(currentPage, numberOfPages);
              });
            }

            this.setState({
              currentTotal: result.data.TotalResults,
              reviewsSummary: result.data.Results,
              reviewsProduct: result.data.Includes.Products,
              noResultmessage: null,
              numberOfPages: Math.ceil(result.data.TotalResults / limit),
            }, () => {
              const { currentPage, numberOfPages } = this.state;
              this.changePaginationButtonStatus(currentPage, numberOfPages);
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

        if (explicitTrigger) {
          const handlePaginationCompleteEvent = new CustomEvent('handlePaginationComplete', {});
          document.dispatchEvent(handlePaginationCompleteEvent);
        }
      });
    }
  };

  /**
   * Call next or previous page function.
   */
  handlePagination = (event) => {
    event.preventDefault();
    const { buttonValue } = event.detail;
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
   * Get limit configuration value..
   */
  getLimitConfigValue() {
    const { bazaarVoiceSettings } = this.state;
    return bazaarVoiceSettings.reviews.bazaar_voice.reviews_per_page;
  }

  /**
   * Process the sort option value when get from the select list.
   */
  processSortOption = (option) => {
    this.setState({ currentSortOption: option.value, currentPage: 1, offset: 0 }, () => {
      this.getReviews(option.value, 'sort');
    });
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

    this.setState({ currentPage: 1, offset: 0 }, () => {
      this.getReviews(currentFilterOptions, 'filter');
    });
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

    this.setState({ currentPage: 1, offset: 0 }, () => {
      this.getReviews(currentFilterOptions, 'filter');
    });
  }

  /**
   * Get the next page reviews when user clicks on next button.
   */
  nextPage() {
    const {
      currentFilterOptions, currentSortOption, offset, bazaarVoiceSettings,
    } = this.state;
    const limit = this.getLimitConfigValue(bazaarVoiceSettings);
    this.setState({ offset: offset + limit }, () => {
      if (currentFilterOptions && currentFilterOptions.length > 0) {
        this.getReviews(currentFilterOptions, 'filter', true);
      } else if (currentSortOption) {
        this.getReviews(currentSortOption, 'sort', true);
      } else {
        this.getReviews(undefined, undefined, true);
      }
      this.setState((prevState) => ({ currentPage: prevState.currentPage + 1 }), () => {
        const { currentPage, numberOfPages } = this.state;
        this.changePaginationButtonStatus(currentPage, numberOfPages);
      });
    });
  }

  /**
   * Get the previous page reviews when user clicks on previous button.
   */
  previousPage() {
    const {
      currentFilterOptions, currentSortOption, offset, bazaarVoiceSettings,
    } = this.state;
    const limit = this.getLimitConfigValue(bazaarVoiceSettings);
    this.setState({ offset: offset - limit }, () => {
      if (currentFilterOptions && currentFilterOptions.length > 0) {
        this.getReviews(currentFilterOptions, 'filter', true);
      } else if (currentSortOption) {
        this.getReviews(currentSortOption, 'sort', true);
      } else {
        this.getReviews(undefined, undefined, true);
      }
      this.setState((prevState) => ({ currentPage: prevState.currentPage - 1 }), () => {
        const { currentPage, numberOfPages } = this.state;
        this.changePaginationButtonStatus(currentPage, numberOfPages);
      });
    });
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
      prevButtonDisabled,
      nextButtonDisabled,
      currentPage,
      numberOfPages,
      bazaarVoiceSettings,
    } = this.state;

    if (totalReviews === '') {
      return (
        <>
          <div className="empty-review-summary">
            <WriteReviewButton />
          </div>
          <ConditionalView condition={postReviewData !== ''}>
            <PostReviewMessage postReviewData={postReviewData} />
          </ConditionalView>
        </>
      );
    }

    return (
      <div className="reviews-wrapper">
        <div className="histogram-data-section">
          <div className="rating-wrapper">
            <ReviewHistogram overallSummary={reviewsProduct} />
            <div className="sorting-filter-wrapper">
              <div className="sorting-filter-title-block">{Drupal.t('Filter + Sort')}</div>
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
              <div className="review-summary" key={reviewsSummary[item].Id}>
                <ConditionalView condition={window.innerWidth < 768}>
                  <DisplayStar
                    starPercentage={reviewsSummary[item].Rating}
                  />
                  <div className="review-title">{reviewsSummary[item].Title}</div>
                </ConditionalView>
                <ReviewInformation
                  reviewInformationData={reviewsSummary[item]}
                  reviewTooltipInfo={reviewsProduct[reviewsSummary[item]
                    .ProductId].ReviewStatistics}
                />
                <ReviewDescription
                  reviewDescriptionData={reviewsSummary[item]}
                />
              </div>
            ))}
          </div>
          <Pagination
            currentPage={currentPage}
            numberOfPages={numberOfPages}
            prevButtonDisabled={prevButtonDisabled}
            nextButtonDisabled={nextButtonDisabled}
          />
        </ConditionalView>
        <ConditionalView condition={noResultmessage !== null}>
          <EmptyMessage emptyMessage={noResultmessage} />
        </ConditionalView>
      </div>
    );
  }
}
