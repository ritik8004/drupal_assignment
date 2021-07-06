import React from 'react';

/**
 * Displays and empty component in case of error.
 */
export default class EmptyErrorBoundary extends React.Component {
  static getDerivedStateFromError() {
    // Update state so the next render will show the fallback UI.
    return { hasError: true };
  }

  constructor(props) {
    super(props);
    this.state = { hasError: false };
  }

  render() {
    const { state, props } = this;
    if (state.hasError) {
      return <></>;
    }

    return props.children;
  }
}
