# DrupalCon Demo Script — Open Web Exchange

> **Presenter guide** for the Open Web Exchange MCP-powered Drupal demo.
> Walk through five AI-driven scenarios showing Drupal as a machine-readable collaboration platform.

---

## Setup Checklist

Before the demo, verify:

- [ ] `ddev start` — site is running at https://open-web-mcp.ddev.site
- [ ] `ddev drush php:script scripts/seed-demo-content.php` — demo content loaded
- [ ] MCP endpoint responds: `curl -u admin:password https://open-web-mcp.ddev.site/mcp`
- [ ] Claude Desktop (or Claude Code) connected to the MCP server
- [ ] Browser open on the Events calendar: https://open-web-mcp.ddev.site/events
- [ ] Browser tab open on admin: https://open-web-mcp.ddev.site/admin/content

---

## Demo Narrative

### Opening (1 min)

> "Most people think of Drupal as a website builder. But Drupal's real superpower is structured content — content that both humans and machines can read, query, and act on.
>
> The Open Web Exchange is a fictional organisation that runs collaborative events for researchers, nonprofits, government agencies, and community groups.
>
> Today we'll show how Drupal CMS — combined with the MCP module — lets AI tools like Claude interact with this platform as a structured knowledge and workflow system."

---

## Scenario 1: Discover Events by Topic (2 min)

**Talking point:** "A community health researcher wants to find relevant events before registering."

**In Claude (via MCP), type:**
```
What Open Web Exchange events are coming up on the topic of Community Health?
```

**What happens:**
Claude calls the `query_events` tool with `topic: "Community Health"` and returns a formatted list of matching events.

**Expected response includes:**
- "Open Data for Healthier Communities" (hybrid, 7 days out)
- "Research Exchange: Community Health Data Methods" (in-person, 42 days out)

**Talking points:**
- Show the structured JSON response the MCP tool returns
- Point to the Events view at `/events` — same data, human-readable
- "This is the same content. The MCP module makes it machine-readable without any custom API code."

---

## Scenario 2: Get Full Event Details (2 min)

**Talking point:** "Once Claude finds an interesting event, it can retrieve full details — including live registration availability."

**In Claude, type:**
```
Tell me more about the "Open Data for Healthier Communities" event.
  Who is speaking, what's the agenda, and how many spots are left?
```

**What happens:**
Claude calls `get_event_details` with the event ID from the previous search.

**Expected response includes:**
- Hybrid format, location + virtual link
- Topics: Open Data & Transparency, Community Health
- Speakers: Dr. Amara Osei (City University), Priya Nair (City of Riverdale)
- Full session schedule
- Availability: e.g., "42 of 80 seats registered — 38 remaining"

**Talking points:**
- "The availability is live — it queries current registrations in real time."
- "A hallucination-free response: Claude is reading from authoritative Drupal content."

---

## Scenario 3: Register for an Event (2 min)

**Talking point:** "MCP tools can also perform write actions — not just reads."

**In Claude, type:**
```
Please register user 3 for the "Open Data for Healthier Communities" event.
```

**What happens:**
Claude calls `register_for_event` with `event_id` and `user_id: 3`.

**Expected response:**
```json
{
  "success": true,
  "message": "Successfully registered for \"Open Data for Healthier Communities\".",
  "registration_id": 101
}
```

**Then show in Drupal admin:**
- Navigate to `/admin/content` and filter by type "Registration"
- The new registration record appears immediately

**Then try registering the same user again:**
```
Register user 3 for the same event again.
```

**Expected response:**
```json
{
  "success": false,
  "message": "You are already registered for this event."
}
```

**Talking points:**
- "Drupal enforces the business rules — Claude doesn't need to know about them."
- "This is a real write operation. The MCP tool calls the same PHP service layer as the web UI."

---

## Scenario 4: Personalised Recommendations (3 min)

**Talking point:** "Now let's show AI-powered personalisation. This is where the content model really pays off."

**First, show the member profile in Drupal:**
- Open https://open-web-mcp.ddev.site/members
- Click on "Marcus Delacroix" — interests: Digital Equity, Civic Technology, Economic Mobility

**In Claude, type:**
```
What events would you recommend for user 2 based on their interests?
  Please explain why each one matches.
```

**What happens:**
Claude calls `suggest_events` with `user_id: 2`. The recommendation service:
1. Loads Marcus's Member Profile
2. Extracts his topic interests
3. Scores upcoming events by topic overlap
4. Excludes events he is already registered for
5. Returns a ranked list with explanations

**Expected response:**
- "Digital Equity Workshop" — 2 matching topics (Digital Equity, Economic Mobility)
- "Civic Technology Hackathon" — 2 matching topics (Civic Technology, Economic Mobility)
- Explanation: "Matches 2 of your topic interests"

**Talking points:**
- "No vector database, no ML model. Pure structured content + taxonomy = meaningful recommendations."
- "This is how Drupal's content model becomes an intelligent system when combined with AI."

---

## Scenario 5: Cross-Platform Workflow (2 min)

**Talking point:** "Let's put it all together — one natural language request, multiple MCP tool calls."

**In Claude, type:**
```
I'm helping Leila Mansouri plan her attendance at the Open Web Exchange.
  Find events that match her interests, tell me the speaker details for
  the most relevant one, and register her for it.
```

**What happens:**
Claude orchestrates multiple MCP tool calls:
1. `suggest_events` — finds Leila's profile (user_id: 5), returns ranked events
2. `get_speaker_info` — retrieves speaker profile for the top event
3. `register_for_event` — registers Leila for the chosen event

**Talking points:**
- "Three separate MCP tool calls, coordinated by the AI, from one natural language request."
- "Drupal handled the data model, the business logic, and the write operation."
- "Claude provided the conversational interface and the reasoning layer."

---

## Closing (1 min)

> "What you've just seen is Drupal functioning as an **open collaboration platform** — not just a website.
>
> The same structured content that editors manage through the Drupal admin interface is now accessible to AI tools through well-defined, authenticated MCP endpoints.
>
> This means Drupal can serve as the knowledge and workflow backbone for AI-powered experiences — without rebuilding your data model or writing custom API code.
>
> The Drupal MCP module, combined with Drupal's content architecture, makes this possible today."

---

## Fallback: Live Queries via REST

If the MCP connection fails during the demo, the same data is accessible via the REST views:

```bash
# All upcoming events (JSON)
curl https://open-web-mcp.ddev.site/api/events?_format=json

# Filter by format
curl "https://open-web-mcp.ddev.site/api/events?_format=json&format=hybrid"

# Member directory
curl https://open-web-mcp.ddev.site/api/members?_format=json
```

---

## Drush Fallback Commands

If demonstrating from the terminal instead of Claude:

```bash
# List upcoming events tagged "Community Health"
ddev drush owe:events "Community Health"

# Show personalised recommendations for user 2
ddev drush owe:recommend 2

# List virtual events
ddev drush owe:events --format=virtual
```

---

## Q&A Talking Points

**"How is this different from a REST API?"**
> MCP is bidirectional and action-oriented — tools can read *and* write. The AI client can chain multiple tool calls autonomously. REST APIs are passive; MCP tools are active capabilities.

**"What about security?"**
> MCP requires authentication. Tool inputs are validated in PHP before any database operation. The same Drupal access control rules apply — the AI can only do what the authenticated user is permitted to do.

**"Can this work with other AI tools?"**
> Yes. MCP is an open standard. Any MCP-compatible client — Claude, other LLMs, or custom AI applications — can connect to the same Drupal endpoint.

**"Does this require a lot of custom code?"**
> The five MCP tools in this demo are approximately 400 lines of PHP. The content model is standard Drupal configuration. The heavy lifting is done by the MCP module and Drupal's existing entity and views systems.
