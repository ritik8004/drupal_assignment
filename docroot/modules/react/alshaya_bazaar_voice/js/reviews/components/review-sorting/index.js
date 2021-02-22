import React from 'react';
import Select from 'react-select';

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
        />
      </>
    );
  }
}
