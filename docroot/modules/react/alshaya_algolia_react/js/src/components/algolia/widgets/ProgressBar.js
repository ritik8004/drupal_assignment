import React from 'react';

// Display progress bar for number of results being displayed.
// this will progress with number of pages and results user scrolls.

const defaultProps = {
  completed: 0,
  animation: 200,
};

class ProgressBar extends React.Component {
  static throwError(...args) {
    return new Error(args);
  }

  render() {
    const {
      completed, animation, className, children, ...rest
    } = this.props;
    const style = {
      width: `${completed}%`,
      transition: `width ${animation}ms`,
    };

    return (
      <div className={className || 'progressbar-container'} {...rest}>
        <div className="progressbar-progress" style={style}>{children}</div>
      </div>
    );
  }
}
ProgressBar.defaultProps = defaultProps;
export default ProgressBar;
