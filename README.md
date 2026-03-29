# TRMNL Home Assistant Todos

A [larapaper](https://github.com/usetrmnl/larapaper) recipe that displays todo/task lists from Home Assistant on a TRMNL e-ink screen.

## How it works

```
TRMNL device  <--image--  Larapaper  --POST-->  Home Assistant REST API
                                                 POST /api/services/todo/get_items
```

Larapaper polls the HA todo service API on a schedule, stores the response, and renders a Blade template into a 1-bit e-ink image. No custom HA integration needed — just a long-lived access token.

## Installation

1. Download or clone this repository
2. ZIP the `src/` folder: `cd src && zip -r ../ha-todos.zip . && cd ..`
3. In larapaper, go to **Plugins > Import** and upload `ha-todos.zip`
4. Configure the plugin with your HA details

## Configuration

| Field | Description |
|---|---|
| **Home Assistant URL** | e.g. `http://192.168.1.100:8123` (no trailing slash) |
| **Long-Lived Access Token** | Created in HA under Profile > Long-Lived Access Tokens |
| **Todo Entity IDs** | Comma-separated, e.g. `todo.shopping_list,todo.tasks` |
| **Custom List Names** | Optional. Comma-separated friendly names matching the entity IDs above, e.g. `Shopping,Tasks`. If omitted, names are derived from the entity ID. |

### Finding your todo entity IDs

In Home Assistant, go to **Developer Tools > States** and filter for `todo.` to see all available todo entities.

Or call the API directly:
```
curl -H "Authorization: Bearer YOUR_TOKEN" http://YOUR_HA:8123/api/states | jq '.[] | select(.entity_id | startswith("todo.")) | .entity_id'
```

## Features

- Multiple todo list support (grouped by list name)
- Custom friendly names for todo lists (optional)
- Due dates shown as secondary line per item
- Only incomplete items shown (filtered via HA API)
- Items sorted by due date (earliest first, no-date items last)
- Responsive layouts for full, half, and quadrant sizes
- Plugin name used as title bar label
- List header hidden when only one todo list is configured

## Network requirements

Larapaper must be able to reach your HA instance. If both run on the same network (e.g. Docker on the same host), use the local IP. For remote access, use Tailscale, Cloudflare Tunnel, or similar.

## License

GPL-3.0
