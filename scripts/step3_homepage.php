<?php
/**
 * Step 3: Update Home node (nid=2) with rich homepage content.
 * Set it as the front page.
 */

$nid = 2;
$node = \Drupal\node\Entity\Node::load($nid);

if (!$node) {
  echo "ERROR: Node {$nid} not found.\n";
  return;
}

$body = <<<HTML
<div class="homepage-hero">
  <h1>Where Sectors Meet. Where Ideas Move.</h1>
  <p class="homepage-subhead">Open Web Exchange is a multi-sector collaboration platform connecting researchers, nonprofits, government agencies, businesses, and community groups around shared challenges.</p>
</div>

<div class="homepage-mission">
  <h2>How It Works</h2>
  <div class="mission-cards">
    <div class="mission-card">
      <h3>&#128269; Discover Events</h3>
      <p>Find conferences, workshops, roundtables, and hackathons tailored to your sector and interests. Filter by topic, format, or date to find what matters most.</p>
    </div>
    <div class="mission-card">
      <h3>&#128101; Connect with Members</h3>
      <p>Browse profiles of researchers, nonprofit leaders, government program managers, and community organizers. Find collaborators across sectors working on your shared challenges.</p>
    </div>
    <div class="mission-card">
      <h3>&#129309; Collaborate Across Sectors</h3>
      <p>Break down silos between academia, government, civil society, and business. Open Web Exchange creates structured pathways for cross-sector partnerships and knowledge exchange.</p>
    </div>
  </div>
</div>

<div class="homepage-mcp">
  <h2>AI-Ready by Design</h2>
  <p>Open Web Exchange is built on <strong>Drupal CMS</strong> with native support for the <strong>Model Context Protocol (MCP)</strong> — the emerging standard that lets AI assistants interact with your platform as a structured data source.</p>
  <p>Our MCP endpoint at <code>/mcp/post</code> allows AI assistants to:</p>
  <ul>
    <li>Query upcoming events by topic, format, or date range</li>
    <li>Retrieve detailed speaker and presenter profiles</li>
    <li>Check event registration availability</li>
    <li>Register participants for events anonymously on behalf of users</li>
    <li>Suggest relevant events based on sector and interest areas</li>
  </ul>
  <p>This means your AI assistant — whether Claude, ChatGPT, or a custom agent — can help users discover and register for events without ever leaving their conversation interface. <strong>No screen-scraping. No fragile API integrations.</strong> Just a clean, standards-based protocol that any AI can use.</p>
  <p><em>Open Web Exchange was demonstrated at DrupalCon Atlanta 2025 as a showcase of AI-ready open web architecture.</em></p>
</div>

<div class="homepage-featured-events">
  <h2>Upcoming Events</h2>
  <p>From open data workshops to AI governance roundtables, our events bring together the people and organizations shaping the future of cross-sector collaboration.</p>
  <p><a href="/events" class="button button--primary">Browse All Events</a></p>
</div>

<div class="homepage-cta">
  <h2>Ready to Connect?</h2>
  <p>Explore our community of researchers, practitioners, and changemakers working across sectors to solve shared challenges.</p>
  <p>
    <a href="/events" class="button button--primary">Browse Upcoming Events</a>
    &nbsp;
    <a href="/people" class="button button--secondary">Meet Our Members</a>
  </p>
</div>
HTML;

$node->set('field_content', [
  'value' => $body,
  'format' => 'full_html',
]);
$node->setTitle('Open Web Exchange');
$node->save();

echo "Updated homepage node {$nid}: " . $node->getTitle() . "\n";

// Set front page
\Drupal::configFactory()->getEditable('system.site')
  ->set('page.front', '/node/' . $nid)
  ->save();

echo "Set front page to /node/{$nid}\n";
echo "Done.\n";
