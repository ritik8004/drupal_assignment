import React from 'react';
import FilterPanel from '../FilterPanel';
import SortByList from '../SortByList';
import ColorFilter from './ColorFilter';
import RefinementList from './RefinementList';
import PriceFilter from './PriceFilter';
import { getFilters } from '../../utils';
import renderWidget from './RenderWidget';

class WidgetManager extends React.Component {
  render() {
    const filter = this.props.facet;
    const indexName = this.props.indexName;

    var currentWidget = '';
    var className = '';
    switch (filter.widget.type) {
      case 'sort_by':
        currentWidget = <SortByList defaultRefinement={indexName} items={filter.widget.items}/>;
        break;

      case 'swatch_list':
        className = 'block-facet--swatch-list';
        currentWidget = <ColorFilter attribute={`${filter.identifier}.label`} searchable={false} />;
        break;

      case 'range_checkbox':
        currentWidget = <PriceFilter attribute={filter.identifier} granularity={filter.widget.config.granularity} />;
        break;

      case 'checkbox':
      default:
        currentWidget = <RefinementList attribute={filter.identifier} searchable={false} itemCount={this.props.itemCount} />;
    }

    return (
      <FilterPanel header={filter.label} id={filter.identifier} className={className}>
        {currentWidget}
      </FilterPanel>
    );
  }
}

export default renderWidget(WidgetManager);
