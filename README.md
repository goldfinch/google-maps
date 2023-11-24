TODO

* Marker Font Icon [checked] - hide scale w/h ancho x/y

1)

app/_config/component-maps.yml
```
---
Name: app-component-maps
---

Goldfinch\Component\Maps\Models\MapSegment:
  segment_types:
    office:
      label: 'Office map'
      settings: true
      markers: true
```

2)

.env
```
APP_GOOGLE_MAPS_KEY=""
```

3)

app/_schema/map-{segment_type}.json
```
{
    "type": "array",
    "options": {},
    "items": {
        "type": "object",
        "properties": {
            "example": {
                "title": "Example",
                "type": "string",
                "default": "default example text"
              }
        }
      }

  }
```

4)

themes/{theme}/templates/Components/Maps/{segment_type}.ss

```
my custom template for specific segment type
```

---

InfoWindow template

```
themes/{theme}/templates/Components/Maps/InfoWindows/my_infowindow_template.ss
```

---- 


1) Option one (import)

package.json
```
"@googlemaps/js-api-loader": "^1.16.2",
```

app.js
```
import GoogleMap from '..../vendor/goldfinch/component-maps/client/src/src/map-mod';

document.addEventListener('DOMContentLoaded', () => {
  new GoogleMap();
});
```

2) Option two (Silverstripe requirement)
```
<% require javascript('goldfinch/component-maps:client/dist/map.js') %>
```
or
```
Requirements::javascript('goldfinch/component-maps:client/dist/map.js');
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
