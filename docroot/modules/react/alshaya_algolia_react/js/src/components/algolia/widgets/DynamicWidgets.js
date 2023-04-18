import React, { Fragment } from 'react';
import { getDisplayName } from 'react-instantsearch-core/dist/cjs/core/utils';
import { connectDynamicWidgets } from 'react-instantsearch-core';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

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
    buildFacets(userData);
  }

  const Fallback = typeof fallbackComponent === 'undefined' ? function fallback() {
    return null;
  } : fallbackComponent;

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
