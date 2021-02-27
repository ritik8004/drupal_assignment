import React from 'react';

class Slider extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      sliderVal: '',
      activeId: '',
    };
  }

  setActiveId = (e, sliderLabel, sliderValue) => {
    if (sliderLabel.length > 0) {
      this.setState({
        activeId: sliderLabel,
        sliderVal: sliderValue,
      });
    }
  };

  render() {
    const {
      required,
      id,
      label,
      options,
      text,
    } = this.props;
    const { sliderVal, activeId } = this.state;

    return (
      <>
        {text !== undefined
          && (
          <div className="head-row">{text}</div>
          )}
        <div className="write-review-type-slider">
          <div className="select-slider__wrap">
            <div className="selectedValue">
              <span>
                {label}
                {':'}
              </span>
              <span className="selectedLabel">{activeId}</span>
            </div>
            <div className="range-slider-block">
              <div className="range-slider">
                {Object.values(options).map((sliderLabel, i) => {
                  const sliderValue = i + 1;
                  return (
                    <React.Fragment key={sliderValue}>
                      <input
                        id={`slider-${sliderValue}`}
                        type="radio"
                        name={`slider-${id}`}
                        value={sliderValue}
                        required={required}
                      />

                      <label
                        className={activeId === sliderLabel ? 'active' : 'inactive'}
                        onClick={(e) => this.setActiveId(e, sliderLabel, sliderValue)}
                        htmlFor={sliderLabel}
                      >
                        <span>{sliderLabel}</span>
                      </label>
                    </React.Fragment>
                  );
                })}
              </div>
            </div>
            <input type="hidden" id={id} name={id} required={required} value={sliderVal || ''} />
          </div>
          <div id={`${label}-error`} className="error" />
        </div>
      </>
    );
  }
}

export default Slider;
