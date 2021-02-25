import React from 'react';
import Select from 'react-select';

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
      }
    }
  }

  processRatingFilters = () => {
    const {
      filterOptions,
    } = this.props;

    if (filterOptions !== undefined && filterOptions !== null) {
      let availableOptions = '';
      Object.entries(filterOptions).forEach(([index]) => {
        const contextData = filterOptions[index].ReviewStatistics.RatingDistribution;

        const options = Object.keys(contextData).map((item) => ({
          value: `rating:${contextData[item].RatingValue}`,
          label: `${contextData[item].RatingValue} ${(contextData[item].RatingValue > 1) ? 'stars' : 'star'} (${contextData[item].Count})`,
        }));
        availableOptions = options.reverse();
      });

      return availableOptions;
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
            options={ratingList}
            defaultValue={{ value: 'none', label: Drupal.t('Rating') }}
          />
        </div>
      );
    }
    return null;
  }
}
