import React from 'react';

const GridButtons = (props) => (
  <div className="grid-buttons">
    <div className="large-col-grid" onClick={props.toggle}>
      <svg className="g2" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
        <g className="grid" fill="#DADADA" fillRule="nonzero">
          <path d="M0 0h7v15H0zM8 0h7v15H8z"></path>
        </g>
      </svg>
      <svg className="g1" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
        <path className="grid" fill="#DADADA" fillRule="nonzero" d="M0 0h15v15H0z"></path>
      </svg>
    </div>
    <div className="small-col-grid active" onClick={props.toggle}>
      <svg className="g3" xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15">
        <g className="grid" fill="#DADADA" fillRule="nonzero">
          <path d="M0 0h4v7H0zM5 0h4v7H5zM10 0h4v7h-4zM10 8h4v7h-4zM5 8h4v7H5zM0 8h4v7H0z"></path>
        </g>
      </svg>
      <svg className="g2" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
        <g className="grid" fill="#DADADA" fillRule="nonzero">
          <path d="M0 0h7v15H0zM8 0h7v15H8z"></path>
        </g>
      </svg>
    </div>
  </div>
);

export default GridButtons;
