import React from 'react';

class ProgressBar extends React.Component {

  static defaultProps = {
    completed: 0,
    color: '#0BD318',
    animation: 200,
    height: 10
  }

  static throwError() {
    return new Error(...arguments);
  }

  render () {
    const {color, completed, animation, height, className, children, ...rest} = this.props;
    const style = {
      backgroundColor: color,
      width: completed + '%',
      transition: `width ${animation}ms`,
      height: height
    };

    return (
      <div className={className || "progressbar-container"} {...rest}>
        <div className="progressbar-progress" style={style}>{children}</div>
      </div>
    );
  }
}

export default ProgressBar;
