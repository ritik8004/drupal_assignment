import React from 'react';

// Display progress bar for number of results being displayed.
// this will progress with number of pages and results user scrolls.
class ProgressBar extends React.Component {

  static defaultProps = {
    completed: 0,
    animation: 200,
  };

  static throwError() {
    return new Error(...arguments);
  }

  render () {
    const {completed, animation, className, children, ...rest} = this.props;
    const style = {
      width: completed + '%',
      transition: `width ${animation}ms`,
    };

    return (
      <div className={className || "progressbar-container"} {...rest}>
        <div className="progressbar-progress" style={style}>{children}</div>
      </div>
    );
  }
}

export default ProgressBar;
