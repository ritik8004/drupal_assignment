// Copied from https://github.com/algolia/react-instantsearch/blob/master/packages/react-instantsearch-core/src/widgets/DynamicWidgets.tsx
// This is overridden in order to use custom connectDynamicWidgets connector.
// The connector collects userData from results and return to
// calling component i.e Filters in callback.
import React, { Fragment } from 'react';
import { getDisplayName } from '../../../utils/FilterUtils';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';
import connectDynamicWidgets from '../connectors/connectDynamicWidgets';

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
}) {
  if (hasValue(userData) && !hasValue(children)) {
    // Get userData from algolia result and if children prop is empty
    // then call buildFacets from props to pass userData.
    buildFacets(userData);
  }

  const Fallback = hasValue(fallbackComponent)
    ? fallbackComponent
    : function fallback() { return null; };

  const widgets = new Map();

  React.Children.forEach(children, (child) => {
    const attribute = getAttribute(child);
    if (!attribute) {
      throw new Error(
        `Could not find "attribute" prop for ${getDisplayName(child)}.`,
      );
    }
    widgets.set(attribute, child);
  });

  // on initial render this will be empty, but React InstantSearch keeps
  // search state for unmounted components in place, so routing works.
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
