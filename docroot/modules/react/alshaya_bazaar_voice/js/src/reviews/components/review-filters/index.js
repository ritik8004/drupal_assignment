import React from 'react';
import Select from 'react-select';
import { getArraysIntersection } from '../../../utilities/write_review_util';

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
      currentOptions,
      filterOptions,
    } = this.props;
    const availableFilters = [];
    if (filterOptions !== undefined && filterOptions !== null) {
      Object.entries(filterOptions).forEach(([index]) => {
        const contextData = filterOptions[index].ReviewStatistics.ContextDataDistribution;
        Object.entries(contextData).forEach(([item, option]) => {
          if (item.includes('_filter')) {
            const options = Object.keys(option.Values).map((key) => ({
              value: `contextdatavalue_${item}:${option.Values[key].Value}`,
              label: `${option.Values[key].Value} (${option.Values[key].Count})`,
            }));
            availableFilters[item] = options;
            availableFilters[item].defaultValue = [{
              value: 'none',
              label: option.Label,
            }];
            if (currentOptions.length > 0) {
              const selected = getArraysIntersection(currentOptions, options);
              if (selected.length > 0) {
                availableFilters[item].defaultValue = selected;
              }
            }
          }
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
        <>
          { Object.keys(filterList).map((item) => (
            <div className="filter-items" key={item}>
              <Select
                classNamePrefix="bvSelect"
                className="bv-select"
                onChange={this.handleSelect}
                options={filterList[item]}
                defaultValue={filterList[item].defaultValue}
                value={filterList[item].defaultValue}
                isSearchable={false}
              />
            </div>
          ))}
        </>
      );
    }

    return null;
  }
}
