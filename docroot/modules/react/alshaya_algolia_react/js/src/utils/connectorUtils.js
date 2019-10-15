import { has, omit, get } from 'lodash';

export function getResults(searchResults, context) {
  if (searchResults.results && !searchResults.results.hits) {
    return searchResults.results[getIndex(context)]
      ? searchResults.results[getIndex(context)]
      : null;
  } else {
    return searchResults.results ? searchResults.results : null;
  }
}

export function getCurrentRefinementValue(
  props,
  searchState,
  context,
  id,
  defaultValue,
  refinementsCallback = x => x
) {
  const index = getIndex(context);
  const { namespace, attributeName } = getNamespaceAndAttributeName(id);
  const refinements =
    (hasMultipleIndex(context) &&
      searchState.indices &&
      namespace &&
      searchState.indices[`${index}`] &&
      has(searchState.indices[`${index}`][namespace], `${attributeName}`)) ||
    (hasMultipleIndex(context) &&
      searchState.indices &&
      has(searchState, `indices.${index}.${id}`)) ||
    (!hasMultipleIndex(context) &&
      namespace &&
      has(searchState[namespace], attributeName)) ||
    (!hasMultipleIndex(context) && has(searchState, id));
  if (refinements) {
    let currentRefinement;

    if (hasMultipleIndex(context)) {
      currentRefinement = namespace
        ? get(searchState.indices[`${index}`][namespace], attributeName)
        : get(searchState.indices[index], id);
    } else {
      currentRefinement = namespace
        ? get(searchState[namespace], attributeName)
        : get(searchState, id);
    }

    return refinementsCallback(currentRefinement);
  }

  if (props.defaultRefinement) {
    return props.defaultRefinement;
  }

  return defaultValue;
}

export function getIndex(context) {
  return context && context.multiIndexContext
    ? context.multiIndexContext.targetedIndex
    : context.ais.mainTargetedIndex;
}

export function refineValue(
  searchState,
  nextRefinement,
  context,
  resetPage,
  namespace
) {
  if (hasMultipleIndex(context)) {
    return namespace
      ? refineMultiIndexWithNamespace(
          searchState,
          nextRefinement,
          context,
          resetPage,
          namespace
        )
      : refineMultiIndex(searchState, nextRefinement, context, resetPage);
  } else {
    // When we have a multi index page with shared widgets we should also
    // reset their page to 1 if the resetPage is provided. Otherwise the
    // indices will always be reset
    // see: https://github.com/algolia/react-instantsearch/issues/310
    // see: https://github.com/algolia/react-instantsearch/issues/637
    if (searchState.indices && resetPage) {
      Object.keys(searchState.indices).forEach(targetedIndex => {
        searchState = refineValue(
          searchState,
          { page: 1 },
          { multiIndexContext: { targetedIndex } },
          true,
          namespace
        );
      });
    }
    return namespace
      ? refineSingleIndexWithNamespace(
          searchState,
          nextRefinement,
          resetPage,
          namespace
        )
      : refineSingleIndex(searchState, nextRefinement, resetPage);
  }
}

export function cleanUpValue(searchState, context, id) {
  const indexName = getIndex(context);
  const { namespace, attributeName } = getNamespaceAndAttributeName(id);

  return cleanUpValueWithSingleIndex({
    attribute: attributeName,
    searchState,
    id,
    namespace,
  });
}

function hasMultipleIndex(context) {
  return context && context.multiIndexContext;
}

function getNamespaceAndAttributeName(id) {
  const parts = id.match(/^([^.]*)\.(.*)/);
  const namespace = parts && parts[1];
  const attributeName = parts && parts[2];

  return { namespace, attributeName };
}

function refineSingleIndexWithNamespace(
  searchState,
  nextRefinement,
  resetPage,
  namespace
) {
  const page = resetPage ? { page: 1 } : undefined;
  return {
    ...searchState,
    [namespace]: { ...searchState[namespace], ...nextRefinement },
    ...page,
  };
}

function refineMultiIndexWithNamespace(
  searchState,
  nextRefinement,
  context,
  resetPage,
  namespace
) {
  const index = getIndex(context);
  const page = resetPage ? { page: 1 } : undefined;
  const state = has(searchState, `indices.${index}`)
    ? {
        ...searchState.indices,
        [index]: {
          ...searchState.indices[index],
          ...{
            [namespace]: {
              ...searchState.indices[index][namespace],
              ...nextRefinement,
            },
            page: 1,
          },
        },
      }
    : {
        ...searchState.indices,
        ...{ [index]: { [namespace]: nextRefinement, ...page } },
      };
  return { ...searchState, indices: state };
}

function refineSingleIndex(searchState, nextRefinement, resetPage) {
  const page = resetPage ? { page: 1 } : undefined;
  return { ...searchState, ...nextRefinement, ...page };
}

function refineMultiIndex(searchState, nextRefinement, context, resetPage) {
  const page = resetPage ? { page: 1 } : undefined;
  const index = getIndex(context);
  const state = has(searchState, `indices.${index}`)
    ? {
        ...searchState.indices,
        [index]: { ...searchState.indices[index], ...nextRefinement, ...page },
      }
    : {
        ...searchState.indices,
        ...{ [index]: { ...nextRefinement, ...page } },
      };
  return { ...searchState, indices: state };
}

function cleanUpValueWithSingleIndex({
  searchState,
  id,
  namespace,
  attribute,
}) {
  if (namespace) {
    return {
      ...searchState,
      [namespace]: omit(searchState[namespace], attribute),
    };
  }

  return omit(searchState, id);
}
