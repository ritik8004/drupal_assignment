import React from 'react';
import SectionTitle from '../section-title';

export default class FilterList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      items: [],
      selected: null,
    };
  }

  /**
   * Filter the list on search.
   */
  filterList = () => {
    let updatedList = this.getInitialItems();
    updatedList = updatedList.filter((item) => item.label.toLowerCase().search(
      event.target.value.toLowerCase(),
    ) !== -1);

    this.setState({
      items: updatedList,
    });
  }

  /**
   * Prepare initial list.
   */
  getInitialItems = () => this.props.options;

  /**
   * Handle click on <li>.
   */
  handleLiClick = (e) => {
    this.setState({
      selected: e.target.parentElement.value,
    });

    this.props.toggleFilterList();

    // Call the process.
    this.props.processingCallback(e.target.parentElement.value);
  };

  backButtonClick = () => {
    this.props.toggleFilterList();
  };

  componentDidMount() {
    this.setState({
      items: this.getInitialItems(),
      selected: this.props.selected,
    });
  }

  render() {
    const { items } = this.state;
    if (items === 0) {
      return (null);
    }

    return (
      <div className="filter-list">
        <div className="spc-filter-panel-header">
          <span className="spc-filter-panel-back" onClick={() => this.backButtonClick()} />
          <SectionTitle>{this.props.panelTitle}</SectionTitle>
        </div>
        <div className="spc-filter-panel-search-form-item">
          <input className="spc-filter-panel-search-field" type="text" placeholder={this.props.placeHolderText} onChange={this.filterList} />
        </div>
        <div className="spc-filter-area-panel-list-wrapper">
          <ul>
            {
            items.map((item) => (
              <li
                key={item.value}
                value={item.value}
                className={
                    ((this.state.selected !== undefined && this.state.selected.value == item.value))
                      ? 'active' : 'in-active'
}
              >
                <span onClick={(e) => this.handleLiClick(e)} className="spc-area-panel-item">{item.label}</span>
              </li>
            ))
            }
          </ul>
        </div>
      </div>
    );
  }
}
