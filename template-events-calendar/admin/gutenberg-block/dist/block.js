/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/block.js":
/*!**********************!*\
  !*** ./src/block.js ***!
  \**********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _icons__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./icons */ "./src/icons.js");
/* harmony import */ var _layout_type__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./layout-type */ "./src/layout-type.js");
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./style.scss */ "./src/style.scss");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/**
 * Block dependencies
 */




/**
 * Internal block libraries
 */
var __ = wp.i18n.__;
var registerBlockType = wp.blocks.registerBlockType;
var baseURL = ectUrl;
var LayoutImgPath = baseURL + 'assets/images/';
var _wp = wp,
  apiFetch = _wp.apiFetch;
var _wp$editor = wp.editor,
  RichText = _wp$editor.RichText,
  InspectorControls = _wp$editor.InspectorControls,
  BlockControls = _wp$editor.BlockControls;
var _wp$components = wp.components,
  TabPanel = _wp$components.TabPanel,
  Panel = _wp$components.Panel,
  PanelBody = _wp$components.PanelBody,
  PanelRow = _wp$components.PanelRow,
  Text = _wp$components.Text,
  TextareaControl = _wp$components.TextareaControl,
  TextControl = _wp$components.TextControl,
  ButtonGroup = _wp$components.ButtonGroup,
  Dashicon = _wp$components.Dashicon,
  Card = _wp$components.Card,
  Toolbar = _wp$components.Toolbar,
  Button = _wp$components.Button,
  SelectControl = _wp$components.SelectControl,
  Tooltip = _wp$components.Tooltip,
  CardBody = _wp$components.CardBody,
  RangeControl = _wp$components.RangeControl;
var categoryList = [];
wp.apiFetch({
  path: '/tribe/events/v1/categories/?per_page=50'
}).then(function (data) {
  if (_typeof(data.categories) != undefined) {
    categoryList = data.categories.map(function (val, key) {
      return {
        label: val.name,
        value: val.slug
      };
    });
    categoryList.push({
      label: "Select a Category",
      value: 'all'
    });
  }
});

/**
 * Register block
 */
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (registerBlockType('ect/shortcode', {
  // Block Title
  title: __('Events Shortcodes'),
  // Block Description
  description: __('The Events Calendar - Shortcode & Templates'),
  // Block Category
  category: 'common',
  // Block Icon
  icon: _icons__WEBPACK_IMPORTED_MODULE_0__["default"],
  // Block Keywords
  keywords: [__('the events calendar'), __('templates'), __('cool plugins')],
  attributes: {
    template: {
      type: 'string',
      "default": 'default'
    },
    category: {
      type: 'string',
      "default": 'all'
    },
    style: {
      type: 'string',
      "default": 'style-1'
    },
    order: {
      type: 'string',
      "default": 'ASC'
    },
    based: {
      type: 'string',
      "default": 'default'
    },
    storycontent: {
      type: 'string',
      "default": 'default'
    },
    limit: {
      type: 'string',
      "default": '10'
    },
    dateformat: {
      type: 'string',
      "default": 'default'
    },
    startDate: {
      type: 'string',
      "default": ''
    },
    endDate: {
      type: 'string',
      "default": ''
    },
    hideVenue: {
      type: 'string',
      "default": 'no'
    },
    time: {
      type: 'string',
      "default": 'future'
    },
    socialshare: {
      type: 'string',
      "default": 'no'
    }
  },
  // Defining the edit interface
  edit: function edit(props) {
    var layoutOptions = [{
      label: 'Default List Layout',
      value: 'default'
    }, {
      label: 'Timeline Layout',
      value: 'timeline-view'
    }, {
      label: 'Minimal List',
      value: 'minimal-list'
    }];
    var designsOptions = [{
      label: 'Style 1',
      value: 'style-1'
    }, {
      label: 'Style 2',
      value: 'style-2'
    }, {
      label: 'Style 3',
      value: 'style-3'
    }];
    var dateFormatsOptions = [{
      label: "Default (01 January 2019)",
      value: "default"
    }, {
      label: "Md,Y (Jan 01, 2019)",
      value: "MD,Y"
    }, {
      label: "Fd,Y (January 01, 2019)",
      value: "FD,Y"
    }, {
      label: "dM (01 Jan)",
      value: "DM"
    }, {
      label: "dML (01 Jan Monday)",
      value: "DML"
    }, {
      label: "dF (01 January)",
      value: "DF"
    }, {
      label: "Md (Jan 01)",
      value: "MD"
    }, {
      label: "Fd (January 01)",
      value: "FD"
    }, {
      label: "Md,YT (Jan 01, 2019 8:00am-5:00pm)",
      value: "MD,YT"
    }, {
      label: "Full (01 January 2019 8:00am-5:00pm)",
      value: "full"
    }, {
      label: "jMl (1 Jan Monday)",
      value: "jMl"
    }, {
      label: "d.FY (01. January 2019)",
      value: "d.FY"
    }, {
      label: "d.F (01. January)",
      value: "d.F"
    }, {
      label: "ldF (Monday 01 January)",
      value: "ldF"
    }, {
      label: "Mdl (Jan 01 Monday)",
      value: "Mdl"
    }, {
      label: "d.Ml (01. Jan Monday)",
      value: "d.Ml"
    }, {
      label: "dFT (01 January 8:00am-5:00pm)",
      value: "dFT"
    }];
    var venueOptions = [{
      label: 'NO',
      value: 'no'
    }, {
      label: 'YES',
      value: 'yes'
    }];
    var timeOptions = [{
      label: 'Upcoming',
      value: 'future'
    }, {
      label: 'Past',
      value: 'past'
    }, {
      label: 'All',
      value: 'all'
    }];
    var orderOptions = [{
      label: "ASC",
      value: "ASC"
    }, {
      label: "DESC",
      value: "DESC"
    }];
    return [!!props.isSelected && wp.element.createElement(InspectorControls, {
      key: "inspector"
    }, wp.element.createElement(TabPanel, {
      className: "ect-tab-settings",
      activeClass: "active-tab",
      tabs: [{
        name: 'ect_general_setting',
        title: 'Layout',
        className: 'tab-one',
        content: wp.element.createElement(React.Fragment, null, wp.element.createElement(PanelBody, null, wp.element.createElement(SelectControl, {
          label: __('Select Template'),
          options: layoutOptions,
          value: props.attributes.template,
          onChange: function onChange(value) {
            return props.setAttributes({
              template: value
            });
          }
        }), wp.element.createElement(React.Fragment, null, wp.element.createElement("div", {
          className: "ect_shortcode-button-group_label"
        }, __("Select Style")), wp.element.createElement(ButtonGroup, {
          className: "ect_shortcode-button-group"
        }, wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              style: 'style-1'
            });
          },
          className: props.attributes.style == 'style-1' ? 'active' : '',
          isSmall: true
        }, "Style 1"), wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              style: 'style-2'
            });
          },
          className: props.attributes.style == 'style-2' ? 'active' : '',
          isSmall: true
        }, "Style 2"), wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              style: 'style-3'
            });
          },
          className: props.attributes.style == 'style-3' ? 'active' : '',
          isSmall: true
        }, "Style 3"))), wp.element.createElement(SelectControl, {
          label: __('Date Formats'),
          description: __('yes/no'),
          options: dateFormatsOptions,
          value: props.attributes.dateformat,
          onChange: function onChange(value) {
            return props.setAttributes({
              dateformat: value
            });
          }
        }), wp.element.createElement(RangeControl, {
          label: "Limit the events",
          value: parseInt(props.attributes.limit),
          onChange: function onChange(value) {
            return props.setAttributes({
              limit: value.toString()
            });
          },
          min: 1,
          max: 100,
          step: 1
        })), wp.element.createElement(Panel, null, wp.element.createElement(PanelBody, {
          title: "Extra Settings",
          initialOpen: false
        }, wp.element.createElement(PanelRow, null, wp.element.createElement(ButtonGroup, {
          className: "ect_shortcode-button-group"
        }, wp.element.createElement("div", {
          className: "ect_shortcode-button-group_label"
        }, __("Hide Venue")), wp.element.createElement("div", null, wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              hideVenue: 'no'
            });
          },
          className: props.attributes.hideVenue == 'no' ? 'active' : '',
          isSmall: true
        }, "No"), wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              hideVenue: 'yes'
            });
          },
          className: props.attributes.hideVenue == 'yes' ? 'active' : '',
          isSmall: true
        }, "Yes")))), props.attributes.template != 'advance-list' && wp.element.createElement(PanelRow, null, wp.element.createElement(ButtonGroup, {
          className: "ect_shortcode-button-group"
        }, wp.element.createElement("div", {
          className: "ect_shortcode-button-group_label"
        }, __("Enable Social Share Buttons?")), wp.element.createElement("div", null, wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              socialshare: 'no'
            });
          },
          className: props.attributes.socialshare == 'no' ? 'active' : '',
          isSmall: true
        }, "No"), wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              socialshare: 'yes'
            });
          },
          className: props.attributes.socialshare == 'yes' ? 'active' : '',
          isSmall: true
        }, "Yes")))), wp.element.createElement("hr", null), wp.element.createElement(CardBody, {
          className: "ect-timeline-block-demo-button"
        }, wp.element.createElement("a", {
          target: "_blank",
          "class": "button button-primary",
          href: "https://eventscalendaraddons.com/plugin/events-shortcodes-pro/"
        }, "Get Pro"), wp.element.createElement("a", {
          target: "_blank",
          "class": "button button-primary",
          href: "https://eventscalendaraddons.com/demos/events-shortcodes-pro/?utm_source=ect_plugin&utm_medium=inside&utm_campaign=demo&utm_content=shortcode_block_setting"
        }, "View Demos"), wp.element.createElement("a", {
          target: "_blank",
          "class": "button button-primary",
          href: "https://eventscalendaraddons.com/go/ect-video-tutorial/?utm_source=ect_plugin&utm_medium=inside&utm_campaign=video_tutorial&utm_content=shortcode_block_setting"
        }, "Watch Videos")), wp.element.createElement(CardBody, {
          className: "ect-gt-block-review-tab"
        }, __("We hope you liked our plugin. Please share your valuable feedback.", "ect"), wp.element.createElement("br", null), wp.element.createElement("a", {
          href: "https://wordpress.org/support/plugin/template-events-calendar/reviews/#new-post",
          className: "components-button is-primary is-small",
          target: "_blank"
        }, "Rate Us", wp.element.createElement("span", null, " \u2605\u2605\u2605\u2605\u2605"))))), wp.element.createElement(Panel, null), wp.element.createElement(Panel, null))
      }, {
        name: 'events_query',
        title: 'Events Query',
        className: 'tab-two',
        content: wp.element.createElement(React.Fragment, null, wp.element.createElement(PanelBody, null, wp.element.createElement("div", {
          className: "ect_shortcode-button-group_label"
        }, __("Events Time")), wp.element.createElement(ButtonGroup, {
          className: "ect_shortcode-button-group"
        }, wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              time: 'future'
            });
          },
          className: props.attributes.time == 'future' ? 'active' : '',
          isSmall: true
        }, "Future"), wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              time: 'past'
            });
          },
          className: props.attributes.time == 'past' ? 'active' : '',
          isSmall: true
        }, "Past"), wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              time: 'all'
            });
          },
          className: props.attributes.time == 'all' ? 'active' : '',
          isSmall: true
        }, "All")), wp.element.createElement("div", {
          className: "ect_shortcode-button-group_label"
        }, __("Events Order")), wp.element.createElement(ButtonGroup, {
          className: "ect_shortcode-button-group"
        }, wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              order: 'ASC'
            });
          },
          className: props.attributes.order == 'ASC' ? 'active' : '',
          isSmall: true
        }, "ASC"), wp.element.createElement(Button, {
          onClick: function onClick(e) {
            props.setAttributes({
              order: 'DESC'
            });
          },
          className: props.attributes.order == 'DESC' ? 'active' : '',
          isSmall: true
        }, "DESC"))), wp.element.createElement(Panel, null, wp.element.createElement(PanelBody, {
          title: "\uD83D\uDD36Filter Events By",
          initialOpen: false
        }, wp.element.createElement(PanelRow, {
          className: "ect_shortcode-button-group_label"
        }, wp.element.createElement(SelectControl, {
          label: __('Select Category'),
          options: categoryList,
          value: props.attributes.category,
          onChange: function onChange(value) {
            return props.setAttributes({
              category: value
            });
          }
        })), wp.element.createElement(PanelRow, null, wp.element.createElement(TextControl, {
          label: __('Start Date | format(YY-MM-DD)'),
          value: props.attributes.startDate,
          onChange: function onChange(value) {
            return props.setAttributes({
              startDate: value
            });
          }
        })), wp.element.createElement(PanelRow, null, wp.element.createElement(TextControl, {
          label: __('End Date | format(YY-MM-DD)'),
          value: props.attributes.endDate,
          onChange: function onChange(value) {
            return props.setAttributes({
              endDate: value
            });
          }
        })), wp.element.createElement(PanelRow, null, wp.element.createElement("p", {
          className: "description"
        }, "Note:-Show events from date range e.g( 2017-01-01 to 2017-02-05). Please dates in this format(YY-MM-DD)")))))
      }]
    }, function (tab) {
      return wp.element.createElement(Card, null, tab.content);
    })), wp.element.createElement("div", {
      className: props.className
    }, wp.element.createElement(_layout_type__WEBPACK_IMPORTED_MODULE_1__["default"], {
      LayoutImgPath: LayoutImgPath,
      layout: props.attributes.template
    }), wp.element.createElement("div", {
      "class": "ect-shortcode-block"
    }, "[events-calendar-templates category=\"", props.attributes.category, "\" template=\"", props.attributes.template, "\" style=\"", props.attributes.style, "\" date_format=\"", props.attributes.dateformat, "\" start_date=\"", props.attributes.startDate, "\" end_date=\"", props.attributes.endDate, "\" limit=\"", props.attributes.limit, "\" order=\"", props.attributes.order, "\" hide-venue=\"", props.attributes.hideVenue, "\" time=\"", props.attributes.time, "\" socialshare=\"", props.attributes.socialshare, "\"]"))];
  },
  // Defining the front-end interface
  save: function save() {
    // Rendering in PHP
    return null;
  }
}));

/***/ }),

/***/ "./src/icons.js":
/*!**********************!*\
  !*** ./src/icons.js ***!
  \**********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
var EctIcon = function EctIcon() {
  return wp.element.createElement("svg", {
    id: "svg",
    version: "1.1",
    width: "24",
    height: "24",
    viewBox: "0 0 400 400",
    xmlns: "http://www.w3.org/2000/svg"
  }, wp.element.createElement("g", {
    id: "svgg"
  }, wp.element.createElement("path", {
    id: "path0",
    d: "M82.400 202.202 L 82.400 230.005 114.100 229.902 L 145.800 229.800 145.903 202.100 L 146.005 174.400 114.203 174.400 L 82.400 174.400 82.400 202.202 M162.400 202.202 L 162.400 230.005 194.100 229.902 L 225.800 229.800 225.903 202.100 L 226.005 174.400 194.203 174.400 L 162.400 174.400 162.400 202.202 M242.400 202.202 L 242.400 230.005 274.100 229.902 L 305.800 229.800 305.903 202.100 L 306.005 174.400 274.203 174.400 L 242.400 174.400 242.400 202.202 M82.400 274.202 L 82.400 302.005 114.100 301.902 L 145.800 301.800 145.903 274.100 L 146.005 246.400 114.203 246.400 L 82.400 246.400 82.400 274.202 M162.400 274.202 L 162.400 302.005 194.100 301.902 L 225.800 301.800 225.903 274.100 L 226.005 246.400 194.203 246.400 L 162.400 246.400 162.400 274.202 M242.400 274.202 L 242.400 302.005 274.100 301.902 L 305.800 301.800 305.903 274.100 L 306.005 246.400 274.203 246.400 L 242.400 246.400 242.400 274.202 ",
    stroke: "none",
    fill: "#222222",
    "fill-rule": "evenodd"
  }), wp.element.createElement("path", {
    id: "path1",
    d: "M86.400 28.400 L 86.400 54.400 44.400 54.400 L 2.400 54.400 2.400 226.200 L 2.400 398.001 200.100 397.900 L 397.800 397.800 397.900 226.100 L 398.001 54.400 356.000 54.400 L 314.000 54.400 314.000 28.400 L 314.000 2.400 282.200 2.400 L 250.400 2.400 250.400 28.400 L 250.400 54.400 200.200 54.400 L 150.000 54.400 150.000 28.400 L 150.000 2.400 118.200 2.400 L 86.400 2.400 86.400 28.400 M350.400 242.200 L 350.400 358.400 198.200 358.400 L 46.000 358.400 46.000 242.200 L 46.000 126.000 198.200 126.000 L 350.400 126.000 350.400 242.200 ",
    stroke: "none",
    fill: "#0c94c4",
    "fill-rule": "evenodd"
  })));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (EctIcon);

/***/ }),

/***/ "./src/layout-type.js":
/*!****************************!*\
  !*** ./src/layout-type.js ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
var LayoutType = function LayoutType(props) {
  if (!props.layout) {
    return null;
  }
  return wp.element.createElement("div", {
    className: "event-template"
  }, wp.element.createElement("img", {
    src: props.LayoutImgPath + "ect-icons.svg"
  }), wp.element.createElement("p", null, "Events Shortcodes"));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (LayoutType);

/***/ }),

/***/ "./src/style.scss":
/*!************************!*\
  !*** ./src/style.scss ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./style.scss */ "./src/style.scss");
/* harmony import */ var _block_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./block.js */ "./src/block.js");
// Include stylesheet


// Import Click to Tweet Block

})();

/******/ })()
;
//# sourceMappingURL=block.js.map