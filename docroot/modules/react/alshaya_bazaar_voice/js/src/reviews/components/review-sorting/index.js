import React from 'react';
import Select from 'react-select';
import { trackFeaturedAnalytics } from '../../../utilities/analytics';

export default class ReviewSorting extends React.Component {
  handleSelect = (selectedOption) => {
    const {
      currentOption,
      processingCallback,
    } = this.props;

    if (currentOption !== selectedOption.value) {
      // Callback to process sort option.
      processingCallback(selectedOption);
      // Process sort click data as user clicks on sort option.
      const analyticsData = {
        type: 'Used',
        name: 'sort',
        detail1: selectedOption.label,
        detail2: selectedOption.value,
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
