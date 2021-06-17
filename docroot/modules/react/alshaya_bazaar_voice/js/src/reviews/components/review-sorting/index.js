import React from 'react';
import Select from 'react-select';
import dispatchCustomEvent from '../../../../../../js/utilities/events';

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
      // Dispatching click event to record analytics.
      dispatchCustomEvent('bvSortOptionsClick', selectedOption);
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
