(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["js/app"],{

/***/ "./assets/js/app.js":
/*!**************************!*\
  !*** ./assets/js/app.js ***!
  \**************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(jQuery) {__webpack_require__(/*! core-js/modules/es.array.find.js */ "./node_modules/core-js/modules/es.array.find.js");

__webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");

__webpack_require__(/*! core-js/modules/web.timers.js */ "./node_modules/core-js/modules/web.timers.js");

__webpack_require__(/*! core-js/modules/es.array.for-each.js */ "./node_modules/core-js/modules/es.array.for-each.js");

__webpack_require__(/*! core-js/modules/web.dom-collections.for-each.js */ "./node_modules/core-js/modules/web.dom-collections.for-each.js");

__webpack_require__(/*! core-js/modules/es.array.slice.js */ "./node_modules/core-js/modules/es.array.slice.js");

__webpack_require__(/*! @fortawesome/fontawesome-free/js/all.js */ "./node_modules/@fortawesome/fontawesome-free/js/all.js");

__webpack_require__(/*! jquery-ui/ui/widgets/datepicker */ "./node_modules/jquery-ui/ui/widgets/datepicker.js");

__webpack_require__(/*! jquery-ui/ui/i18n/datepicker-uk */ "./node_modules/jquery-ui/ui/i18n/datepicker-uk.js");

__webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.esm.js");

var WOW = __webpack_require__(/*! wowjs */ "./node_modules/wowjs/dist/wow.js");

jQuery(function ($) {
  /*$('.js-datepicker').datepicker({
      changeMonth: true,
      changeYear: true,
      yearRange: "1930:2021",
      dateFormat: settings.date_format,
      firstDay: 1,
  })
      .datepicker( $.datepicker.regional[ settings.language ] );
  */
  var message = $('.toast');

  if (message.length) {
    $(message).toast({
      'autohide': false
    }).toast('show');
    $(message).find('.flash-close').click(function () {
      $(message).toast('dispose');
    });
  } // loader


  var loader = function loader() {
    setTimeout(function () {
      if ($('#loader').length > 0) {
        $('#loader').removeClass('show');
      }
    }, 1);
  };

  loader();
  window.wow = new WOW.WOW({
    live: false
  });
  window.wow.init();
  tinymce.init({
    selector: '.html-editor',
    setup: function setup(editor) {
      editor.on('change', function () {
        editor.save();
      });
    }
  }); // Back to top button

  $(window).scroll(function () {
    if ($(this).scrollTop() > 200) {
      $('.back-to-top').fadeIn('slow');
    } else {
      $('.back-to-top').fadeOut('slow');
    }
  });
  $('.back-to-top').click(function () {
    $('html, body').animate({
      scrollTop: 0
    }, 1500, 'easeInOutExpo');
    return false;
  });

  (function () {
    'use strict'; // Fetch all the forms we want to apply custom Bootstrap validation styles to

    var forms = document.querySelectorAll('.needs-validation'); // Loop over them and prevent submission

    Array.prototype.slice.call(forms).forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        form.classList.add('was-validated');
      }, false);
    });
  })();
});
/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js")))

/***/ })

},[["./assets/js/app.js","runtime","vendors~js/admin~js/app~js/article~js/datagrid","vendors~js/app"]]]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvanMvYXBwLmpzIl0sIm5hbWVzIjpbInJlcXVpcmUiLCJXT1ciLCJqUXVlcnkiLCIkIiwibWVzc2FnZSIsImxlbmd0aCIsInRvYXN0IiwiZmluZCIsImNsaWNrIiwibG9hZGVyIiwic2V0VGltZW91dCIsInJlbW92ZUNsYXNzIiwid2luZG93Iiwid293IiwibGl2ZSIsImluaXQiLCJ0aW55bWNlIiwic2VsZWN0b3IiLCJzZXR1cCIsImVkaXRvciIsIm9uIiwic2F2ZSIsInNjcm9sbCIsInNjcm9sbFRvcCIsImZhZGVJbiIsImZhZGVPdXQiLCJhbmltYXRlIiwiZm9ybXMiLCJkb2N1bWVudCIsInF1ZXJ5U2VsZWN0b3JBbGwiLCJBcnJheSIsInByb3RvdHlwZSIsInNsaWNlIiwiY2FsbCIsImZvckVhY2giLCJmb3JtIiwiYWRkRXZlbnRMaXN0ZW5lciIsImV2ZW50IiwiY2hlY2tWYWxpZGl0eSIsInByZXZlbnREZWZhdWx0Iiwic3RvcFByb3BhZ2F0aW9uIiwiY2xhc3NMaXN0IiwiYWRkIl0sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUFBQUEsbUJBQU8sQ0FBQyx1R0FBRCxDQUFQOztBQUNBQSxtQkFBTyxDQUFDLDBGQUFELENBQVA7O0FBQ0FBLG1CQUFPLENBQUMsMEZBQUQsQ0FBUDs7QUFDQUEsbUJBQU8sQ0FBQyxvRUFBRCxDQUFQOztBQUVBLElBQU1DLEdBQUcsR0FBR0QsbUJBQU8sQ0FBQywrQ0FBRCxDQUFuQjs7QUFFQUUsTUFBTSxDQUFDLFVBQVNDLENBQVQsRUFBVztFQUNkO0FBQ0o7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNJLElBQUlDLE9BQU8sR0FBR0QsQ0FBQyxDQUFDLFFBQUQsQ0FBZjs7RUFDQSxJQUFJQyxPQUFPLENBQUNDLE1BQVosRUFBb0I7SUFDaEJGLENBQUMsQ0FBQ0MsT0FBRCxDQUFELENBQVdFLEtBQVgsQ0FBaUI7TUFDYixZQUFZO0lBREMsQ0FBakIsRUFFR0EsS0FGSCxDQUVTLE1BRlQ7SUFHQUgsQ0FBQyxDQUFDQyxPQUFELENBQUQsQ0FBV0csSUFBWCxDQUFnQixjQUFoQixFQUFnQ0MsS0FBaEMsQ0FBc0MsWUFBVTtNQUM1Q0wsQ0FBQyxDQUFDQyxPQUFELENBQUQsQ0FBV0UsS0FBWCxDQUFpQixTQUFqQjtJQUNILENBRkQ7RUFHSCxDQWxCYSxDQW9CZDs7O0VBQ0EsSUFBSUcsTUFBTSxHQUFHLFNBQVRBLE1BQVMsR0FBWTtJQUNyQkMsVUFBVSxDQUFDLFlBQVk7TUFDbkIsSUFBSVAsQ0FBQyxDQUFDLFNBQUQsQ0FBRCxDQUFhRSxNQUFiLEdBQXNCLENBQTFCLEVBQTZCO1FBQ3pCRixDQUFDLENBQUMsU0FBRCxDQUFELENBQWFRLFdBQWIsQ0FBeUIsTUFBekI7TUFDSDtJQUNKLENBSlMsRUFJUCxDQUpPLENBQVY7RUFLSCxDQU5EOztFQU9BRixNQUFNO0VBR05HLE1BQU0sQ0FBQ0MsR0FBUCxHQUFhLElBQUlaLEdBQUcsQ0FBQ0EsR0FBUixDQUFZO0lBQUVhLElBQUksRUFBRTtFQUFSLENBQVosQ0FBYjtFQUNBRixNQUFNLENBQUNDLEdBQVAsQ0FBV0UsSUFBWDtFQUNBQyxPQUFPLENBQUNELElBQVIsQ0FBYTtJQUNURSxRQUFRLEVBQUUsY0FERDtJQUVUQyxLQUFLLEVBQUUsZUFBVUMsTUFBVixFQUFrQjtNQUNyQkEsTUFBTSxDQUFDQyxFQUFQLENBQVUsUUFBVixFQUFvQixZQUFZO1FBQzVCRCxNQUFNLENBQUNFLElBQVA7TUFDSCxDQUZEO0lBR0g7RUFOUSxDQUFiLEVBakNjLENBMkNkOztFQUNBbEIsQ0FBQyxDQUFDUyxNQUFELENBQUQsQ0FBVVUsTUFBVixDQUFpQixZQUFZO0lBQ3pCLElBQUluQixDQUFDLENBQUMsSUFBRCxDQUFELENBQVFvQixTQUFSLEtBQXNCLEdBQTFCLEVBQStCO01BQzNCcEIsQ0FBQyxDQUFDLGNBQUQsQ0FBRCxDQUFrQnFCLE1BQWxCLENBQXlCLE1BQXpCO0lBQ0gsQ0FGRCxNQUVPO01BQ0hyQixDQUFDLENBQUMsY0FBRCxDQUFELENBQWtCc0IsT0FBbEIsQ0FBMEIsTUFBMUI7SUFDSDtFQUNKLENBTkQ7RUFPQXRCLENBQUMsQ0FBQyxjQUFELENBQUQsQ0FBa0JLLEtBQWxCLENBQXdCLFlBQVk7SUFDaENMLENBQUMsQ0FBQyxZQUFELENBQUQsQ0FBZ0J1QixPQUFoQixDQUF3QjtNQUFDSCxTQUFTLEVBQUU7SUFBWixDQUF4QixFQUF3QyxJQUF4QyxFQUE4QyxlQUE5QztJQUNBLE9BQU8sS0FBUDtFQUNILENBSEQ7O0VBS0EsQ0FBQyxZQUFZO0lBQ1QsYUFEUyxDQUdUOztJQUNBLElBQUlJLEtBQUssR0FBR0MsUUFBUSxDQUFDQyxnQkFBVCxDQUEwQixtQkFBMUIsQ0FBWixDQUpTLENBTVQ7O0lBQ0FDLEtBQUssQ0FBQ0MsU0FBTixDQUFnQkMsS0FBaEIsQ0FBc0JDLElBQXRCLENBQTJCTixLQUEzQixFQUNLTyxPQURMLENBQ2EsVUFBVUMsSUFBVixFQUFnQjtNQUNyQkEsSUFBSSxDQUFDQyxnQkFBTCxDQUFzQixRQUF0QixFQUFnQyxVQUFVQyxLQUFWLEVBQWlCO1FBQzdDLElBQUksQ0FBQ0YsSUFBSSxDQUFDRyxhQUFMLEVBQUwsRUFBMkI7VUFDdkJELEtBQUssQ0FBQ0UsY0FBTjtVQUNBRixLQUFLLENBQUNHLGVBQU47UUFDSDs7UUFFREwsSUFBSSxDQUFDTSxTQUFMLENBQWVDLEdBQWYsQ0FBbUIsZUFBbkI7TUFDSCxDQVBELEVBT0csS0FQSDtJQVFILENBVkw7RUFXSCxDQWxCRDtBQW1CSCxDQTNFSyxDQUFOLEMiLCJmaWxlIjoianMvYXBwLmpzIiwic291cmNlc0NvbnRlbnQiOlsicmVxdWlyZSgnQGZvcnRhd2Vzb21lL2ZvbnRhd2Vzb21lLWZyZWUvanMvYWxsLmpzJyk7XG5yZXF1aXJlKCdqcXVlcnktdWkvdWkvd2lkZ2V0cy9kYXRlcGlja2VyJyk7XG5yZXF1aXJlKCdqcXVlcnktdWkvdWkvaTE4bi9kYXRlcGlja2VyLXVrJyk7XG5yZXF1aXJlKCdib290c3RyYXAnKTtcblxuY29uc3QgV09XID0gcmVxdWlyZSgnd293anMnKTtcblxualF1ZXJ5KGZ1bmN0aW9uKCQpe1xuICAgIC8qJCgnLmpzLWRhdGVwaWNrZXInKS5kYXRlcGlja2VyKHtcbiAgICAgICAgY2hhbmdlTW9udGg6IHRydWUsXG4gICAgICAgIGNoYW5nZVllYXI6IHRydWUsXG4gICAgICAgIHllYXJSYW5nZTogXCIxOTMwOjIwMjFcIixcbiAgICAgICAgZGF0ZUZvcm1hdDogc2V0dGluZ3MuZGF0ZV9mb3JtYXQsXG4gICAgICAgIGZpcnN0RGF5OiAxLFxuICAgIH0pXG4gICAgICAgIC5kYXRlcGlja2VyKCAkLmRhdGVwaWNrZXIucmVnaW9uYWxbIHNldHRpbmdzLmxhbmd1YWdlIF0gKTtcbiovXG4gICAgbGV0IG1lc3NhZ2UgPSAkKCcudG9hc3QnKTtcbiAgICBpZiAobWVzc2FnZS5sZW5ndGgpIHtcbiAgICAgICAgJChtZXNzYWdlKS50b2FzdCh7XG4gICAgICAgICAgICAnYXV0b2hpZGUnOiBmYWxzZVxuICAgICAgICB9KS50b2FzdCgnc2hvdycpO1xuICAgICAgICAkKG1lc3NhZ2UpLmZpbmQoJy5mbGFzaC1jbG9zZScpLmNsaWNrKGZ1bmN0aW9uKCl7XG4gICAgICAgICAgICAkKG1lc3NhZ2UpLnRvYXN0KCdkaXNwb3NlJyk7XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIC8vIGxvYWRlclxuICAgIHZhciBsb2FkZXIgPSBmdW5jdGlvbiAoKSB7XG4gICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgaWYgKCQoJyNsb2FkZXInKS5sZW5ndGggPiAwKSB7XG4gICAgICAgICAgICAgICAgJCgnI2xvYWRlcicpLnJlbW92ZUNsYXNzKCdzaG93Jyk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sIDEpO1xuICAgIH07XG4gICAgbG9hZGVyKCk7XG5cblxuICAgIHdpbmRvdy53b3cgPSBuZXcgV09XLldPVyh7IGxpdmU6IGZhbHNlIH0pO1xuICAgIHdpbmRvdy53b3cuaW5pdCgpO1xuICAgIHRpbnltY2UuaW5pdCh7XG4gICAgICAgIHNlbGVjdG9yOiAnLmh0bWwtZWRpdG9yJyxcbiAgICAgICAgc2V0dXA6IGZ1bmN0aW9uIChlZGl0b3IpIHtcbiAgICAgICAgICAgIGVkaXRvci5vbignY2hhbmdlJywgZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgIGVkaXRvci5zYXZlKCk7XG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfVxuICAgIH0pO1xuXG5cbiAgICAvLyBCYWNrIHRvIHRvcCBidXR0b25cbiAgICAkKHdpbmRvdykuc2Nyb2xsKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgaWYgKCQodGhpcykuc2Nyb2xsVG9wKCkgPiAyMDApIHtcbiAgICAgICAgICAgICQoJy5iYWNrLXRvLXRvcCcpLmZhZGVJbignc2xvdycpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgJCgnLmJhY2stdG8tdG9wJykuZmFkZU91dCgnc2xvdycpO1xuICAgICAgICB9XG4gICAgfSk7XG4gICAgJCgnLmJhY2stdG8tdG9wJykuY2xpY2soZnVuY3Rpb24gKCkge1xuICAgICAgICAkKCdodG1sLCBib2R5JykuYW5pbWF0ZSh7c2Nyb2xsVG9wOiAwfSwgMTUwMCwgJ2Vhc2VJbk91dEV4cG8nKTtcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgIH0pO1xuXG4gICAgKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgJ3VzZSBzdHJpY3QnXG5cbiAgICAgICAgLy8gRmV0Y2ggYWxsIHRoZSBmb3JtcyB3ZSB3YW50IHRvIGFwcGx5IGN1c3RvbSBCb290c3RyYXAgdmFsaWRhdGlvbiBzdHlsZXMgdG9cbiAgICAgICAgdmFyIGZvcm1zID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnLm5lZWRzLXZhbGlkYXRpb24nKVxuXG4gICAgICAgIC8vIExvb3Agb3ZlciB0aGVtIGFuZCBwcmV2ZW50IHN1Ym1pc3Npb25cbiAgICAgICAgQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoZm9ybXMpXG4gICAgICAgICAgICAuZm9yRWFjaChmdW5jdGlvbiAoZm9ybSkge1xuICAgICAgICAgICAgICAgIGZvcm0uYWRkRXZlbnRMaXN0ZW5lcignc3VibWl0JywgZnVuY3Rpb24gKGV2ZW50KSB7XG4gICAgICAgICAgICAgICAgICAgIGlmICghZm9ybS5jaGVja1ZhbGlkaXR5KCkpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgICAgICAgICAgICAgICAgICBldmVudC5zdG9wUHJvcGFnYXRpb24oKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgIGZvcm0uY2xhc3NMaXN0LmFkZCgnd2FzLXZhbGlkYXRlZCcpXG4gICAgICAgICAgICAgICAgfSwgZmFsc2UpXG4gICAgICAgICAgICB9KVxuICAgIH0pKClcbn0pOyJdLCJzb3VyY2VSb290IjoiIn0=