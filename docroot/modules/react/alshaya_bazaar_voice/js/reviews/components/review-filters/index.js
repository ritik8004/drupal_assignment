import React from 'react';
import Select from 'react-select';

export default class ReviewFilters extends React.Component {
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

  processFilters = () => {
    const {
      filterOptions,
    } = this.props;
    const availableFilters = [];
    if (filterOptions !== undefined && filterOptions !== null) {
      Object.entries(filterOptions).forEach(([index]) => {
        const contextData = filterOptions[index].ReviewStatistics.ContextDataDistribution;
        Object.entries(contextData).forEach(([item, option]) => {
          const options = Object.keys(option.Values).map((key) => ({
            value: `contextdatavalue_${item}:${option.Values[key].Value}`,
            label: `${option.Values[key].Value} (${option.Values[key].Count})`,
          }));
          availableFilters[item] = options;
        });
      });
    }
    if (Object.entries(availableFilters).length > 0) {
      return availableFilters;
    }
    return null;
  }

  render() {
    const filterList = this.processFilters();

    if (filterList !== null) {
      return (
        <div className="review-filter-wrapper">
          { Object.keys(filterList).map((item) => (
            <div key={item}>
              <Select
                onChange={this.handleSelect}
                options={filterList[item]}
                defaultValue={{ value: 'none', label: item }}
              />
            </div>
          ))}
        </div>
      );
    }
    return null;
  }
}
