import React from 'react';
import Select from 'react-select';
import { getArraysIntersection } from '../../../utilities/write_review_util';
import getStringMessage from '../../../../../../js/utilities/strings';
import { trackFeaturedAnalytics } from '../../../utilities/analytics';

export default class ReviewRatingsFilter extends React.Component {
  handleSelect = (selectedOption) => {
    const {
      currentOptions,
      processingCallback,
    } = this.props;

    if (selectedOption.value !== 'none') {
      let isOptionNew = true;
      if (currentOptions.length > 0
        && (currentOptions
          .find((element) => element.value === selectedOption.value) !== undefined)) {
        isOptionNew = false;
      }

      if (isOptionNew) {
        processingCallback(selectedOption);
        // Process rating details click data as user clicks on filter option.
        const analyticsData = {
          type: 'Used',
          name: 'filter',
          detail1: 'stars',
          detail2: selectedOption.value.split(':')[1],
        };
        trackFeaturedAnalytics(analyticsData);
      }
    }
  }

  processRatingFilters = () => {
    const {
      currentOptions,
      filterOptions,
    } = this.props;

    if (filterOptions !== undefined && filterOptions !== null) {
      const ratingFilter = [];
      let availableOptions = '';
      Object.entries(filterOptions).forEach(([index]) => {
        const contextData = filterOptions[index].FilteredReviewStatistics.RatingDistribution;

        const options = Object.keys(contextData).map((item) => ({
          value: `rating:${contextData[item].RatingValue}`,
          label: `${contextData[item].RatingValue} ${(contextData[item].RatingValue > 1) ? getStringMessage('stars') : getStringMessage('star')} (${contextData[item].Count})`,
        }));
        availableOptions = options;
      });

      ratingFilter.options = availableOptions;
      ratingFilter.default = [{
        value: 'none',
        label: Drupal.t('Rating'),
      }];
      if (currentOptions.length > 0) {
        const selected = getArraysIntersection(currentOptions, availableOptions);
        if (selected.length > 0) {
          ratingFilter.default = selected;
        }
      }

      return ratingFilter;
    }
    return null;
  }

  render() {
    const ratingList = this.processRatingFilters();

    if (ratingList !== null) {
      return (
        <div className="filter-items">
          <Select
            classNamePrefix="bvSelect"
            className="bv-select"
            onChange={this.handleSelect}
            options={ratingList.options}
            defaultValue={ratingList.default}
            value={ratingList.default}
            isSearchable={false}
          />
        </div>
      );
    }
    return null;
  }
}
