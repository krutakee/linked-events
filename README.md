# LinkedEvents

A WordPress plugin for managing events and displaying event listings with shortcodes.

## Features

- Create events from the Events menu in the WordPress dashboard.
- Set an event date, category, description, and optional details URL.
- Display upcoming or past events in a paginated, filterable list.
- Show a compact list of upcoming events.
- Choose which event categories are included when visitors view past events.

## Installation

1. Download this repository using Code > Download ZIP.
2. In WordPress, go to Plugins > Add New Plugin > Upload Plugin and select the ZIP, or extract the plugin folder into wp-content/plugins/.
3. Activate LinkedEvents from Plugins.
4. Add and publish events from Events > Add New Event. Set an event date and, if desired, a category and details URL.

## Shortcodes

Add either shortcode to a page or post.

### Event list

    [le_events_list]

Shows events with date-based sorting, category filtering, and pagination (8 per page by default). Upcoming events are shown by default. When past events exist, visitors can switch between upcoming and past events.

Attributes:

| Attribute | Default | Description |
| --- | --- | --- |
| per_page | 8 | Number of events per page. |
| show_past | false | Set to true to show all events initially instead of only upcoming events. |
| order | ASC | Date order: ASC (earliest first) or DESC (latest first). |

Example:

    [le_events_list per_page="10" show_past="true" order="ASC"]

### Upcoming events

    [le_upcoming_events]

Shows a compact list of upcoming events (3 by default).

Attributes:

| Attribute | Default | Description |
| --- | --- | --- |
| count | 3 | Number of upcoming events to show. |
| view_all_url | /events | URL used by the view all link. |
| order | ASC | Date order: ASC (earliest first) or DESC (latest first). |

Example:

    [le_upcoming_events count="5" view_all_url="/events" order="ASC"]

## Settings

Go to Settings > LinkedEvents to choose which event categories appear when visitors select past events. If no categories are selected, all categories are used.
