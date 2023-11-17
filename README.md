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
