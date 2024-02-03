TODO
* use fielder
* Marker Font Icon [checked] - hide scale w/h ancho x/y


2)

.env
```
APP_GOOGLE_MAPS_KEY=""
```

---

InfoWindow template

```
php taz make:map-infowindow:template
```

3) Create segment in admin selecting type. Then go to Segment settings (might work in main tab too) and hit save, to save default 'Parameters' to the database. (might need to set default Parameters automatically to skip this step)
---- 


1) Option one (import)

package.json
```
"@googlemaps/js-api-loader": "^1.16.2",
```

app.js
```
import GoogleMap from '..../vendor/goldfinch/google-maps/client/src/src/map-mod';
// import GoogleMap from '@goldfinch-maps/src/map-mod';

document.addEventListener('DOMContentLoaded', () => {
  new GoogleMap();
});
```

--- with alias @goldfinch-maps
```
resolve: {
  alias: [
    { find: '@goldfinch-maps', replacement: fileURLToPath(new URL('./vendor/goldfinch/google-maps/client/src', import.meta.url)) },
  ],
},
```

2) Option two (Silverstripe requirement)
```
<% require javascript('goldfinch/google-maps:client/dist/map.js') %>
```
or
```
Requirements::javascript('goldfinch/google-maps:client/dist/map.js');
```

---- for Map

(Maps JavaScript API)
https://console.cloud.google.com/apis/library/maps-backend.googleapis.com

---- For Preview Thumbs requires additionl librariy

(Maps Static API)
https://console.cloud.google.com/apis/library/static-maps-backend.googleapis.com

---- disable blue border on map focus

```scss
// Google Maps disable blue border
.gm-style iframe + div {
  border: none !important;
}
```

---

# Callbacks

*[Map events](https://developers.google.com/maps/documentation/javascript/events)
*[Marker events](https://developers.google.com/maps/documentation/javascript/reference/marker#Marker-Events)
*[AdvancedMarkerElement events](https://developers.google.com/maps/documentation/javascript/reference/advanced-markers#AdvancedMarkerElement-Events)
*[Info Window events](https://developers.google.com/maps/documentation/javascript/reference/info-window#InfoWindow-Events)
```
window.goldfinch = {}

window.goldfinch.map_callback = (map, mapSettings, segment, parameters) => {
  // ..
}
window.goldfinch.marker_callback = (marker, markerParams, e, map, segment, parameters) => {
  // ..
}
window.goldfinch.infoWindow_callback = (infoWindow, infowindowParams, marker, map, e, segment, parameters) => {
  // ..
}
```
