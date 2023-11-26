import { Loader } from "@googlemaps/js-api-loader"

class GoogleMap {

  constructor() {

    document.querySelectorAll('[data-map-segment]').forEach((e, i) => {
      this.initSegment(e)
    })

  }

  initSegment(mapElement) {

    const segmentAttr = mapElement.getAttribute('data-segment');
    const parametersAttr = mapElement.getAttribute('data-parameters');

    if (!mapElement || !segmentAttr) {
      return;
    }

    const segment = JSON.parse(segmentAttr);

    var mapSettings = {
      center: { lat: segment.Latitude, lng: segment.Longitude },
      zoom: segment.Zoom,
      backgroundColor: "#fff",

      // fullscreenControlOptions
      // mapId
      // restriction
      // rotateControlOptions
      // scaleControlOptions
      // streetView
      // tilt
      // disableDefaultUI

      // zoomControlOptions: {
      //   position: google.maps.ControlPosition.LEFT_CENTER,
      // },
      // mapTypeControlOptions: {
      //   mapTypeIds: ["roadmap"],
      // },
      // streetViewControlOptions: {
      //   position: google.maps.ControlPosition.LEFT_TOP,
      // },

      // mapTypeId: "hybrid",
      // mapTypeId: "roadmap",
      // mapTypeId: "satellite",
      // mapTypeId: "terrain",
    }

    if (parametersAttr) {
      var parameters = JSON.parse(parametersAttr)
    }

    if (typeof parameters !== 'undefined') {

        var mapTheme = parameters.map_theme
        var mapDynamic = parameters.map_dynamic

        var mapOverview = parameters.map_inset_overview

        // pseudo parameters
        delete parameters.map_theme
        delete parameters.map_dynamic
        delete parameters.map_height
        delete parameters.map_inset_overview

        if (parameters.mapTypeId === '-') {
          delete parameters.mapTypeId
        }

        if (parameters.scrollwheel !== '') {
          parameters.scrollwheel = JSON.parse(parameters.scrollwheel)
        }

        if (mapTheme.theme == 'custom' && mapTheme.styles) {
          parameters.styles = JSON.parse(mapTheme.styles)
        } else if (segment.Theme) {
          parameters.styles = JSON.parse(segment.Theme)
        }

        // cleaner
        Object.keys(parameters).forEach(function(key, index) {
          if (parameters[key] === '') {
            delete parameters[key]
          }
        });

        // delete parameters.clickableIcons
        // delete parameters.disableDefaultUI
        // delete parameters.keyboardShortcuts
        // delete parameters.mapTypeControl
        // delete parameters.noClear
        // delete parameters.streetViewControl
        // delete parameters.scrollwheel
        // delete parameters.scaleControl
        // delete parameters.isFractionalZoomEnabled
        // delete parameters.mapTypeId
        // delete parameters.zoomControl
        // delete parameters.rotateControl
        // delete parameters.gestureHandling
        // delete parameters.fullscreenControl

        mapSettings = {
          ...mapSettings,
          ...parameters
        }
      }

      const loader = new Loader({
        apiKey: segment.Key,
        version: 'weekly',
      });

      if (mapDynamic.enabled) {

        const wrapper = document.getElementById("wrapper");

        if (mapDynamic.preview) {
          const url = "https://maps.googleapis.com/maps/api/staticmap";
          wrapper.style.backgroundImage = `url(${url}?center=${mapSettings.center.lat},${mapSettings.center.lng}&zoom=${mapSettings.zoom}&scale=2&size=${wrapper.clientWidth}x${wrapper.clientHeight}&key=${segment.Key})`;
        }

        wrapper.addEventListener("click", () => {
          wrapper.remove();
          loader.load().then(() => {
            var map = new google.maps.Map(mapElement, mapSettings);
            this.mapOverviewInit(map, mapOverview, mapElement, mapSettings)
            this.markerHandler(map, segment, parameters, null)

            // callback
            if (window.goldfinch && window.goldfinch.map_callback) {
              window.goldfinch.map_callback(map, mapSettings, segment, parameters);
            }
          });
        });
      } else {
      loader.load().then(async () => {

        var loadAdvancedMarker = false;

        if (segment.Markers.length) {

          segment.Markers.forEach((e, i) => {

            if (e.Parameters) {
              e.Parameters = JSON.parse(e.Parameters)

              if (e.Parameters && e.Parameters.marker_type.markerType == 'AdvancedMarker' && !loadAdvancedMarker) {
                loadAdvancedMarker = true;
              }
            }

          });
        }

        const { Map } = await google.maps.importLibrary("maps");

        if (parameters.mapId && loadAdvancedMarker) {
          var { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
        } else {
          var AdvancedMarkerElement = null
        }

        var map = new Map(mapElement, mapSettings);

        this.mapOverviewInit(map, mapOverview, mapElement, mapSettings)
        this.markerHandler(map, segment, parameters, AdvancedMarkerElement)

        // callback
        if (window.goldfinch && window.goldfinch.map_callback) {
          window.goldfinch.map_callback(map, mapSettings, segment, parameters);
        }

      });
    }
  }

  markerHandler(map, segment, parameters, AdvancedMarkerElement) {

    if (segment.Markers.length) {

      segment.Markers.forEach((e, i) => {

        var marker;

        if (e.Parameters) {

          let markerPosition = { lat: e.Latitude, lng: e.Longitude };

          var markerParams = {
            position: markerPosition,
            map,
            title: e.Title,
          };

          if (typeof e.Parameters !== 'object') {

            e.Parameters = JSON.parse(e.Parameters)
          }

          if (e.Parameters.marker_type.markerType == 'AdvancedMarker' && AdvancedMarkerElement) {

            // AdvancedMarker
            // console.log('AdvancedMarker')

            if (e.Parameters.marker_type.markerCustomHTML && e.InfoWindow) {

              const customContent = document.createElement('div');
              customContent.classList.add('custom-map-marker-wrapper');
              customContent.innerHTML = e.InfoWindow;
              markerParams.content = customContent;
            }

            markerParams.gmpDraggable = e.Parameters.marker_type.draggable;

            // init marker
            marker = new AdvancedMarkerElement(markerParams);

            // callback
            if (window.goldfinch && window.goldfinch.marker_callback) {
              window.goldfinch.marker_callback(marker, markerParams, e, map, segment, parameters);
            }

          } else {

            // console.log('Marker')

            // Marker

            if (
              e.Parameters.marker_type.markerFont &&
              e.Parameters.marker_type.markerFontFamily &&
              e.Parameters.marker_type.markerFontCode
            ) {

              markerParams.icon = ' ';
              markerParams.label = {
                fontFamily: e.Parameters.marker_type.markerFontFamily,
                text: decodeURIComponent(JSON.parse('"' + e.Parameters.marker_type.markerFontCode.replace(/\"/g, '\\"') + '"')),
              };

              if (e.Parameters.marker_type.markerFontColor) {
                markerParams.label.color = e.Parameters.marker_type.markerFontColor;
              }

              if (e.Parameters.marker_type.markerFontSize) {
                markerParams.label.fontSize = e.Parameters.marker_type.markerFontSize;
              }

            } else if (e.Icon) {

              let iconSet = {
                url: e.Icon,
                // This marker is 150 pixels wide by 150 pixels high.
                // size: new google.maps.Size(150, 150),
                // The origin for this image is (0, 0).
                // origin: new google.maps.Point(0, 0),
                // optimized: true
              };

              if (e.Parameters.marker_type.markerScaleWidth || e.Parameters.marker_type.markerScaleHeight) {
                iconSet.scaledSize = new google.maps.Size(e.Parameters.marker_type.markerScaleWidth, e.Parameters.marker_type.markerScaleHeight)
              }

              // The anchor for this image is the base of the flagpole at (0, 150).
              if (e.Parameters.marker_type.markerAnchorX || e.Parameters.marker_type.markerAnchorY) {
                iconSet.anchor = new google.maps.Point(e.Parameters.marker_type.markerAnchorX, e.Parameters.marker_type.markerAnchorY)
              }

              markerParams.icon = iconSet;
              // markerParams.optimized = false;
            }

            // animation
            if (e.Parameters && e.Parameters.marker_type.markerAnimation) {
              let aniationCall = 'google.maps.Animation.' + e.Parameters.marker_type.markerAnimation;
              markerParams.animation = eval(aniationCall);
            }

            // init marker
            marker = new google.maps.Marker(markerParams);

            // callback
            if (window.goldfinch && window.goldfinch.marker_callback) {
              window.goldfinch.marker_callback(marker, markerParams, e, map, segment, parameters);
            }
          }

          // info window

          if (e.Parameters && e.Parameters.info_window.infoWindow && e.InfoWindow) {

            var infowindowParams ={
              content: e.InfoWindow,
              ariaLabel: e.Title,
            }

            if (e.Parameters.info_window.infoWindowMaxWidth) {
              infowindowParams.maxWidth = e.Parameters.info_window.infoWindowMaxWidth;
            }

            if (e.Parameters.info_window.infoWindowMinWidth) {
              infowindowParams.minWidth = e.Parameters.info_window.infoWindowMinWidth;
            }

            var infowindow = new google.maps.InfoWindow(infowindowParams);

            marker.addListener("click", () => {
              infowindow.open({
                anchor: marker,
                map,
              });
            });

            // callback
            if (window.goldfinch && window.goldfinch.infoWindow_callback) {
              window.goldfinch.infoWindow_callback(infowindow, infowindowParams, marker, map, e, segment, parameters);
            }
          }

        }

      })
    }

  }

  mapOverviewInit(map, mapOverview, mapElement, mapSettings) {


    if (mapOverview) {

      const OVERVIEW_DIFFERENCE = 5;
      const OVERVIEW_MIN_ZOOM = 3;
      const OVERVIEW_MAX_ZOOM = 10;

      let overviewEl = mapElement.getAttribute('data-map-segment');
      overviewEl = document.querySelector('[data-map-overview="' + overviewEl + '"]');

      // here in case dynamic load is enabled
      overviewEl.style.display = 'block'

      var overview = new google.maps.Map(overviewEl, {
        ...mapSettings,
        gestureHandling: "none",
        fullscreenControl: false,
        mapTypeControl: false,
        rotateControl: false,
        scaleControl: false,
        streetViewControl: false,
        zoomControl: false,
      });

      function clamp(num, min, max) {
        return Math.min(Math.max(num, min), max);
      }

      map.addListener("bounds_changed", () => {
        overview.setCenter(map.getCenter());
        overview.setZoom(
          clamp(
            map.getZoom() - OVERVIEW_DIFFERENCE,
            OVERVIEW_MIN_ZOOM,
            OVERVIEW_MAX_ZOOM,
          ),
        );
      });
    }

  }

}

export default GoogleMap;
