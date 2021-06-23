import React from 'react';
import Select from 'react-select';
import { trackFeaturedAnalytics } from '../../../utilities/analytics';

export default class ReviewSorting extends React.Component {
  handleSelect = (selectedOption) => {
    const {
      currentOption,
      processingCallback,
    } = this.props;

    if (selectedOption.value !== 'none'
      && currentOption !== selectedOption.value) {
      // Callback to process sort option.
      processingCallback(selectedOption);
      // Process sort click data as user clicks on sort option.
      const analyticsData = {
        type: 'Used',
        name: 'sort',
        detail1: selectedOption.value.split(':')[0],
        detail2: '',
      };
      trackFeaturedAnalytics(analyticsData);
    }
  }

  render() {
    const {
      sortOptions,
    } = this.props;

    return (
      <>
        <Select
          classNamePrefix="bvSelect"
          className="bv-select sort-item"
          onChange={this.handleSelect}
          options={sortOptions}
          defaultValue={sortOptions[0]}
          isSearchable={false}
        />
      </>
    );
  }
}
