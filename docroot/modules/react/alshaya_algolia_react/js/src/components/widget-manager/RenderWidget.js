import React from 'react';

/**
 * Decide and return which widget to render based on Drupal widget types.
 *
 * @param {Array} filter
 *   The array of filter, with name, identifier and widget as key.
 * @param {String} indexName
 *   The current index name.
 */
const renderWidget = (WidgetManager) => class RenderWidget extends React.Component {
    updateItemCount = (attr, count) => {
      const { filterResult } = this.props;
      filterResult({ attr, count });
    }

    render() {
      return (
        <WidgetManager
          {...this.props}
          itemCount={(attr, count) => this.updateItemCount(attr, count)}
        />
      );
    }
};

export default renderWidget;
