(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["js/article"],{

/***/ "./assets/js/article.js":
/*!******************************!*\
  !*** ./assets/js/article.js ***!
  \******************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function(jQuery) {/* harmony import */ var core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.regexp.exec.js */ "./node_modules/core-js/modules/es.regexp.exec.js");
/* harmony import */ var core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.string.replace.js */ "./node_modules/core-js/modules/es.string.replace.js");
/* harmony import */ var core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var cyrillic_to_translit_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! cyrillic-to-translit-js */ "./node_modules/cyrillic-to-translit-js/CyrillicToTranslit.js");
/* harmony import */ var cyrillic_to_translit_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(cyrillic_to_translit_js__WEBPACK_IMPORTED_MODULE_2__);



jQuery(function ($) {
  $('#article_title').keyup(function () {
    var cyrillicToTranslit = new cyrillic_to_translit_js__WEBPACK_IMPORTED_MODULE_2___default.a();
    $('#article_slug').val(convertToSlug(cyrillicToTranslit.transform($(this).val())));
  });
});

function convertToSlug(Text) {
  return Text.toLowerCase().replace(/ /g, "-").replace(/[^\w-]+/g, "");
}
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js")))

/***/ })

},[["./assets/js/article.js","runtime","vendors~js/admin~js/app~js/article~js/datagrid","vendors~js/article"]]]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvanMvYXJ0aWNsZS5qcyJdLCJuYW1lcyI6WyJqUXVlcnkiLCIkIiwia2V5dXAiLCJjeXJpbGxpY1RvVHJhbnNsaXQiLCJDeXJpbGxpY1RvVHJhbnNsaXQiLCJ2YWwiLCJjb252ZXJ0VG9TbHVnIiwidHJhbnNmb3JtIiwiVGV4dCIsInRvTG93ZXJDYXNlIiwicmVwbGFjZSJdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQUFBO0FBRUFBLE1BQU0sQ0FBQyxVQUFTQyxDQUFULEVBQVc7RUFDZEEsQ0FBQyxDQUFDLGdCQUFELENBQUQsQ0FBb0JDLEtBQXBCLENBQTBCLFlBQVU7SUFDaEMsSUFBTUMsa0JBQWtCLEdBQUcsSUFBSUMsOERBQUosRUFBM0I7SUFFQUgsQ0FBQyxDQUFDLGVBQUQsQ0FBRCxDQUFtQkksR0FBbkIsQ0FDSUMsYUFBYSxDQUFDSCxrQkFBa0IsQ0FBQ0ksU0FBbkIsQ0FBNkJOLENBQUMsQ0FBQyxJQUFELENBQUQsQ0FBUUksR0FBUixFQUE3QixDQUFELENBRGpCO0VBR0gsQ0FORDtBQU9ILENBUkssQ0FBTjs7QUFVQSxTQUFTQyxhQUFULENBQXVCRSxJQUF2QixFQUE2QjtFQUN6QixPQUFPQSxJQUFJLENBQUNDLFdBQUwsR0FDRkMsT0FERSxDQUNNLElBRE4sRUFDWSxHQURaLEVBRUZBLE9BRkUsQ0FFTSxVQUZOLEVBRWtCLEVBRmxCLENBQVA7QUFHSCxDIiwiZmlsZSI6ImpzL2FydGljbGUuanMiLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgQ3lyaWxsaWNUb1RyYW5zbGl0IGZyb20gJ2N5cmlsbGljLXRvLXRyYW5zbGl0LWpzJztcclxuXHJcbmpRdWVyeShmdW5jdGlvbigkKXtcclxuICAgICQoJyNhcnRpY2xlX3RpdGxlJykua2V5dXAoZnVuY3Rpb24oKXtcclxuICAgICAgICBjb25zdCBjeXJpbGxpY1RvVHJhbnNsaXQgPSBuZXcgQ3lyaWxsaWNUb1RyYW5zbGl0KCk7XHJcblxyXG4gICAgICAgICQoJyNhcnRpY2xlX3NsdWcnKS52YWwoXHJcbiAgICAgICAgICAgIGNvbnZlcnRUb1NsdWcoY3lyaWxsaWNUb1RyYW5zbGl0LnRyYW5zZm9ybSgkKHRoaXMpLnZhbCgpKSlcclxuICAgICAgICApO1xyXG4gICAgfSk7XHJcbn0pO1xyXG5cclxuZnVuY3Rpb24gY29udmVydFRvU2x1ZyhUZXh0KSB7XHJcbiAgICByZXR1cm4gVGV4dC50b0xvd2VyQ2FzZSgpXHJcbiAgICAgICAgLnJlcGxhY2UoLyAvZywgXCItXCIpXHJcbiAgICAgICAgLnJlcGxhY2UoL1teXFx3LV0rL2csIFwiXCIpO1xyXG59Il0sInNvdXJjZVJvb3QiOiIifQ==