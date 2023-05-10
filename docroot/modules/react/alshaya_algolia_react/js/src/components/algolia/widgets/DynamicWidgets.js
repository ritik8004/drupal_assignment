// Copied from https://github.com/algolia/react-instantsearch/blob/master/packages/react-instantsearch-core/src/widgets/DynamicWidgets.tsx
// This is overridden in order to use custom connectDynamicWidgets connector.
// The connector collects userData from results and return to
// calling component i.e Filters in callback.
import React, { Fragment } from 'react';
import { getDisplayName } from '../../../utils/FilterUtils';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import connectDynamicWidgets from '../connectors/connectDynamicWidgets';
import { isDesktop } from '../../../../../../js/utilities/display';

function isReactElement(element) {
  return typeof element === 'object' && element.props;
}

function getAttribute(element) {
  if (!isReactElement(element)) {
    return undefined;
  }

  if (element.props.attribute) {
    return element.props.attribute;
  }
  if (Array.isArray(element.props.attributes)) {
    return element.props.attributes[0];
  }
  if (element.props.children) {
    return getAttribute(React.Children.only(element.props.children));
  }

  return undefined;
}

function DynamicWidgets({
  children,
  attributesToRender,
  userData,
  fallbackComponent,
  buildFacets,
  lhn = false,
}) {
  if (hasValue(userData) && !hasValue(children) && hasValue(buildFacets)) {
    // Get userData from algolia result and if children prop is empty
    // then call buildFacets from props to pass userData.
    buildFacets(userData);
  }

  const Fallback = hasValue(fallbackComponent)
    ? fallbackComponent
    : function fallback() { return null; };

  const widgets = new Map();
  const labels = {};

  // This is only required for search lhn.
  const { filters } = drupalSettings.algoliaSearch.search;

  React.Children.forEach(children, (child) => {
    const attribute = getAttribute(child);
    if (!attribute) {
      throw new Error(
        `Could not find "attribute" prop for ${getDisplayName(child)}.`,
      );
    }
    widgets.set(attribute, child);

    // Set facet labels.
    const attributeCode = (attribute.indexOf('.') > -1) ? attribute.split('.')[0] : attribute;
    labels[attribute] = hasValue(filters[attributeCode]) ? filters[attributeCode].label : '';
  });

  // on initial render this will be empty, but React InstantSearch keeps
  // search state for unmounted components in place, so routing works.
  // If lhn is set then render category facets in the sidebar.
  if (isDesktop() && lhn) {
    return (
      <aside className="c-sidebar-first">
        <div className="c-sidebar-first__region">
          <div className="region region__sidebar-first clearfix">
            { drupalSettings.algoliaSearch.enable_lhn_tree_search > 0
              && (
                <div className="c-facet__blocks">
                  {attributesToRender.map((attribute) => (
                    <Fragment key={attribute}>
                      {widgets.get(attribute)
                        && (
                          <div className="c-facet__blocks c-facet block-facet-blockcategory-facet-search supercategory-facet c-accordion">
                            <h3 className="c-facet__title c-accordion__title c-collapse__title">{labels[attribute]}</h3>
                            {widgets.get(attribute) || <Fallback attribute={attribute} />}
                          </div>
                        )}
                    </Fragment>
                  ))}
                </div>
              )}
          </div>
        </div>
      </aside>
    );
  }

  // If lhn is set then render category facets for mobile view.
  if (!isDesktop() && lhn) {
    return (
      <div className="category-facet-wrapper">
        {attributesToRender.map((attribute) => (
          <Fragment key={attribute}>
            {widgets.get(attribute)
              && (
                <div className="supercategory-facet c-accordion" attribute="super_category">
                  <h3 className="c-facet__title c-accordion__title c-collapse__title">{labels[attribute]}</h3>
                  {widgets.get(attribute) || <Fallback attribute={attribute} />}
                </div>
              )}
          </Fragment>
        ))}
      </div>
    );
  }

  // Render facets above SRP / PLP listings.
  return (
    <>
      {attributesToRender.map((attribute) => (
        <Fragment key={attribute}>
          {widgets.get(attribute) || <Fallback attribute={attribute} />}
        </Fragment>
      ))}
    </>
  );
}

export default connectDynamicWidgets(DynamicWidgets, {
  $$widgetType: 'ais.dynamicWidgets',
});
