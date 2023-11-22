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

    console.log('segmentAttr',segmentAttr)
    const segment = JSON.parse(segmentAttr);
    console.log('segment',segment)

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

    console.log('parameters', parameters)

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

          console.log('segment.Theme', segment.Theme, mapTheme)

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
            });
          });
        } else {
        loader.load().then(async () => {

          var loadAdvancedMarker = false;

          if (segment.Markers.length) {

            segment.Markers.forEach((e, i) => {

              if (e.Parameters) {
                e.Parameters = JSON.parse(e.Parameters)

                if (e.Parameters && e.Parameters.advancedMarkerElement && !loadAdvancedMarker) {
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

        });
      }
  }

  markerHandler(map, segment, parameters, AdvancedMarkerElement) {

    console.log('markerHandler', segment, AdvancedMarkerElement)

    if (segment.Markers.length) {

      segment.Markers.forEach((e, i) => {

        var marker;

        console.log(i, e)

        if (e.Parameters) {

          let markerPosition = { lat: e.Latitude, lng: e.Longitude };

          if (e.Parameters.advancedMarkerElement && AdvancedMarkerElement) {
              console.log('Load marker: AdvancedMarkerElement')
              marker = new AdvancedMarkerElement({
                map,
                position: markerPosition,
              });


          } else {

            console.log('Load marker: Marker')
            marker = new google.maps.Marker({
              position: markerPosition,
              map,
              title: e.Title,
            });
          }

          // info window

          if (e.Parameters && e.Parameters.infoWindow) {

            const contentString =
            '<div id="content">' +
            '<div id="siteNotice">' +
            "</div>" +
            '<h1 id="firstHeading" class="firstHeading">Uluru</h1>' +
            '<div id="bodyContent">' +
            "<p><b>Uluru</b>, also referred to as <b>Ayers Rock</b>, is a large " +
            "sandstone rock formation in the southern part of the " +
            "Northern Territory, central Australia. It lies 335&#160;km (208&#160;mi) " +
            "south west of the nearest large town, Alice Springs; 450&#160;km " +
            "(280&#160;mi) by road. Kata Tjuta and Uluru are the two major " +
            "features of the Uluru - Kata Tjuta National Park. Uluru is " +
            "sacred to the Pitjantjatjara and Yankunytjatjara, the " +
            "Aboriginal people of the area. It has many springs, waterholes, " +
            "rock caves and ancient paintings. Uluru is listed as a World " +
            "Heritage Site.</p>" +
            '<p>Attribution: Uluru, <a href="https://en.wikipedia.org/w/index.php?title=Uluru&oldid=297882194">' +
            "https://en.wikipedia.org/w/index.php?title=Uluru</a> " +
            "(last visited June 22, 2009).</p>" +
            "</div>" +
            "</div>";

            var infowindow = new google.maps.InfoWindow({
              content: contentString,
              maxWidth: 200,
              ariaLabel: "Uluru",
            });

            marker.addListener("click", () => {
              infowindow.open({
                anchor: marker,
                map,
              });
            });
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
