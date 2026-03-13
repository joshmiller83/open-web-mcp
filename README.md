# Open Web Exchange — MCP-Powered Drupal Event Platform

> **DrupalCon Demo** — A demonstration of [Drupal CMS](https://www.drupal.org/drupal-cms) as an open collaboration platform, combining structured content, event management workflows, and AI integration through the [Drupal MCP module](https://www.drupal.org/project/mcp).

---

## What This Demo Shows

The **Open Web Exchange** is a fictional multi-sector organisation that brings together researchers, nonprofits, government agencies, businesses, and community groups to exchange ideas and solve shared challenges.

The platform demonstrates:

| Capability | Description |
|---|---|
| **Structured Event Management** | Events with hybrid formats, session schedules, speaker references, and registration limits |
| **Topic-Driven Discovery** | Taxonomy-powered event filtering and interest tracking |
| **Member Profiles** | Participant backgrounds, sector affiliations, and topic interests |
| **Registration Workflows** | Capacity-aware event registration with duplicate prevention |
| **MCP Integration** | AI tools querying and acting on Drupal content via the Model Context Protocol |
| **Personalised Recommendations** | Interest-based event suggestions excluding already-registered events |

---

## Architecture

```
open-web-mcp/
├── composer.json                          # Drupal CMS + MCP module dependencies
├── .ddev/                                 # DDEV local development configuration
├── config/sync/                           # Drupal configuration exports (YAML)
│   ├── node.type.event.yml                # Event content type
│   ├── node.type.member_profile.yml       # Member Profile content type
│   ├── node.type.registration.yml         # Registration record content type
│   ├── taxonomy.vocabulary.topics.yml     # Topics vocabulary
│   ├── field.storage.node.*               # Field storage definitions
│   ├── field.field.node.*                 # Field instance configurations
│   ├── views.view.events.yml              # Events calendar + REST API view
│   └── views.view.member_profiles.yml     # Member directory view
├── web/modules/custom/open_web_exchange/  # Custom module
│   ├── src/
│   │   ├── EventQueryService.php          # Event querying and formatting
│   │   ├── RegistrationService.php        # Registration management
│   │   ├── RecommendationService.php      # Interest-based recommendations
│   │   ├── Commands/                      # Drush commands (owe:events, owe:recommend)
│   │   └── Plugin/McpTool/               # MCP tool plugins
│   │       ├── QueryEventsTool.php        # Search events by topic/format/date
│   │       ├── GetEventDetailsTool.php    # Full event details + availability
│   │       ├── RegisterForEventTool.php   # Register a user for an event
│   │       ├── SuggestEventsTool.php      # Personalised recommendations
│   │       └── GetSpeakerInfoTool.php     # Speaker/facilitator profiles
└── scripts/
    └── seed-demo-content.php              # Drush script to populate demo data
```

---

## Content Model

### Event

| Field | Type | Description |
|---|---|---|
| `title` | Text | Event name |
| `body` | Long text | Description |
| `field_event_date` | Datetime | Start date and time |
| `field_event_end_date` | Datetime | End date and time |
| `field_event_format` | List (string) | `in_person`, `virtual`, or `hybrid` |
| `field_event_location` | Text | Physical venue |
| `field_virtual_link` | Link | Meeting URL for virtual/hybrid events |
| `field_topics` | Term reference (×n) | Topics from the Topics vocabulary |
| `field_speakers` | Node reference (×n) | Member Profiles |
| `field_registration_limit` | Integer | Maximum registrations (blank = unlimited) |
| `field_event_schedule` | Long text | Session agenda |

### Member Profile

| Field | Type | Description |
|---|---|---|
| `title` | Text | Participant name |
| `body` | Long text | Professional bio |
| `field_organization` | Text | Organisation name |
| `field_sector` | List (string) | `research`, `nonprofit`, `government`, `business`, `community`, `academic` |
| `field_interests` | Term reference (×n) | Topics of interest |

### Registration

| Field | Type | Description |
|---|---|---|
| `title` | Text | Auto-generated label |
| `field_event_ref` | Node reference | The event registered for |
| `field_registrant` | User reference | The registering member |

### Topics Vocabulary

A flat taxonomy vocabulary with terms such as:
- Open Data & Transparency
- Community Health
- Digital Equity
- Climate Resilience
- Civic Technology
- Nonprofit Innovation
- Government Modernisation
- Academic Research Exchange
- Economic Mobility
- AI for Public Good

---

## MCP Tools

The `open_web_exchange` module registers five MCP tools via the `@McpTool` plugin annotation system:

### `query_events`
Search upcoming events with optional filters.

**Input:**
```json
{
  "topic": "Open Data",
  "format": "hybrid",
  "from_date": "2025-06-01T00:00:00",
  "limit": 5
}
```

**Output:** List of events with IDs, dates, formats, topics, and URLs.

---

### `get_event_details`
Retrieve full details for a single event including schedule, speakers, and live availability.

**Input:**
```json
{ "event_id": 42 }
```

**Output:** Full event data plus `availability` object (`registered`, `limit`, `remaining`, `status`).

---

### `register_for_event`
Register a member for an event. Validates capacity and prevents duplicate registrations.

**Input:**
```json
{ "event_id": 42, "user_id": 7 }
```

**Output:** `{ "success": true, "message": "...", "registration_id": 99 }` or error.

---

### `suggest_events`
Return personalised event recommendations based on a member's topic interests.

**Input:**
```json
{ "user_id": 7, "limit": 5 }
```

**Output:** Scored list of events with `match_reason` explaining each recommendation.

---

### `get_speaker_info`
Retrieve a speaker or facilitator's Member Profile.

**Input:**
```json
{ "profile_id": 12 }
```

**Output:** Name, organisation, sector, bio, and topic interests.

---

## Local Development Setup

### Prerequisites

- [DDEV](https://ddev.readthedocs.io/en/stable/#installation) v1.23+
- [Composer](https://getcomposer.org/) v2
- Docker Desktop (or compatible runtime)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/joshmiller83/open-web-mcp.git
cd open-web-mcp

# 2. Start DDEV
ddev start

# 3. Install PHP dependencies
ddev composer install

# 4. Install Drupal with the exported configuration
ddev drush site:install --existing-config -y

# 5. Load demo content
ddev drush php:script scripts/seed-demo-content.php

# 6. Get a one-time login link
ddev drush uli
```

The site will be available at **https://open-web-mcp.ddev.site**

### Useful Drush Commands

```bash
# List upcoming events
ddev drush owe:events

# Filter events by topic
ddev drush owe:events "Climate Resilience"

# Filter events by format
ddev drush owe:events --format=virtual

# Show recommendations for a user (replace 2 with actual UID)
ddev drush owe:recommend 2

# Clear caches
ddev drush cr

# Export configuration changes
ddev drush cex -y
```

---

## MCP Endpoint

Once the MCP module is installed and configured, the MCP endpoint is available at:

```
https://open-web-mcp.ddev.site/mcp
```

Connect Claude Desktop, Claude Code, or any MCP-compatible client by adding this server to your MCP configuration:

```json
{
  "mcpServers": {
    "open-web-exchange": {
      "url": "https://open-web-mcp.ddev.site/mcp",
      "auth": {
        "type": "basic",
        "username": "admin",
        "password": "your-password"
      }
    }
  }
}
```

See [DEMO.md](DEMO.md) for a full walkthrough of the AI-powered demo scenarios.

---

## Key Message

This demo shows how Drupal CMS can function as an open collaboration platform where structured content, event workflows, and AI integrations combine to support knowledge exchange across communities.

Through the MCP module, Drupal becomes not only a content management system but also a **machine-readable collaboration platform** — one that both humans and AI tools can interact with through well-defined, structured interfaces.
