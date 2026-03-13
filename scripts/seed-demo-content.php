<?php

/**
 * @file
 * Drush script to seed demo content for the Open Web Exchange DrupalCon demo.
 *
 * Run with:  ddev drush php:script scripts/seed-demo-content.php
 *
 * Creates:
 *  - 10 topic taxonomy terms
 *  - 5 member profiles
 *  - 8 upcoming events across different formats and topics
 */

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\User;

// ── Topics ────────────────────────────────────────────────────────────────────

$topics_data = [
  'Open Data & Transparency',
  'Community Health',
  'Digital Equity',
  'Climate Resilience',
  'Civic Technology',
  'Nonprofit Innovation',
  'Government Modernisation',
  'Academic Research Exchange',
  'Economic Mobility',
  'AI for Public Good',
];

$topic_terms = [];
foreach ($topics_data as $name) {
  $term = Term::create([
    'vid' => 'topics',
    'name' => $name,
  ]);
  $term->save();
  $topic_terms[$name] = $term;
  echo "Created topic: $name\n";
}

// ── Member Profiles ───────────────────────────────────────────────────────────

$members_data = [
  [
    'name' => 'Dr. Amara Osei',
    'org' => 'City University School of Public Health',
    'sector' => 'academic',
    'bio' => 'Dr. Osei leads research on community health data infrastructure and open science practices.',
    'interests' => ['Community Health', 'Open Data & Transparency', 'Academic Research Exchange'],
  ],
  [
    'name' => 'Marcus Delacroix',
    'org' => 'Digital Access Alliance',
    'sector' => 'nonprofit',
    'bio' => 'Marcus coordinates digital literacy programs in underserved communities across three states.',
    'interests' => ['Digital Equity', 'Civic Technology', 'Economic Mobility'],
  ],
  [
    'name' => 'Priya Nair',
    'org' => 'Office of Innovation — City of Riverdale',
    'sector' => 'government',
    'bio' => 'Priya leads the city\'s open data initiative and public API programme.',
    'interests' => ['Open Data & Transparency', 'Government Modernisation', 'Civic Technology'],
  ],
  [
    'name' => 'Sam Thornton',
    'org' => 'Thornton Sustainability Consulting',
    'sector' => 'business',
    'bio' => 'Sam advises companies and municipalities on climate adaptation strategies.',
    'interests' => ['Climate Resilience', 'Nonprofit Innovation', 'AI for Public Good'],
  ],
  [
    'name' => 'Leila Mansouri',
    'org' => 'Horizons Community Foundation',
    'sector' => 'nonprofit',
    'bio' => 'Leila manages grant programmes focused on technology-driven community solutions.',
    'interests' => ['Nonprofit Innovation', 'Economic Mobility', 'Digital Equity'],
  ],
];

$member_nodes = [];
foreach ($members_data as $m) {
  $interest_ids = array_map(fn($t) => ['target_id' => $topic_terms[$t]->id()], $m['interests']);
  $node = Node::create([
    'type' => 'member_profile',
    'title' => $m['name'],
    'body' => ['value' => $m['bio'], 'format' => 'basic_html'],
    'field_organization' => $m['org'],
    'field_sector' => $m['sector'],
    'field_interests' => $interest_ids,
    'status' => 1,
  ]);
  $node->save();
  $member_nodes[$m['name']] = $node;
  echo "Created member profile: {$m['name']}\n";
}

// ── Events ────────────────────────────────────────────────────────────────────

$base_date = new DateTime('+7 days');

$events_data = [
  [
    'title' => 'Open Data for Healthier Communities',
    'format' => 'hybrid',
    'location' => 'Civic Center Auditorium, Room 201',
    'days_ahead' => 7,
    'duration_hours' => 2,
    'topics' => ['Open Data & Transparency', 'Community Health'],
    'speakers' => ['Dr. Amara Osei', 'Priya Nair'],
    'limit' => 80,
    'body' => 'A collaborative session exploring how municipalities can share public health datasets to improve community outcomes. Includes a live demonstration of the city open data API.',
    'schedule' => "09:00 — Welcome and framing\n09:20 — Keynote: Why open health data matters (Dr. Osei)\n10:00 — Demo: City of Riverdale open data API (Priya Nair)\n10:40 — Small group discussions\n11:20 — Report-back and next steps\n11:45 — Close",
  ],
  [
    'title' => 'Digital Equity Workshop: Closing the Access Gap',
    'format' => 'in_person',
    'location' => 'Eastside Community Library, Training Room',
    'days_ahead' => 14,
    'duration_hours' => 3,
    'topics' => ['Digital Equity', 'Economic Mobility'],
    'speakers' => ['Marcus Delacroix'],
    'limit' => 25,
    'body' => 'Hands-on workshop for community organisers, librarians, and social workers focused on practical strategies for expanding digital access and literacy in low-income neighbourhoods.',
    'schedule' => "13:00 — Introductions and context-setting\n13:30 — Landscape review: who is still offline and why\n14:15 — Breakout: barriers in your community\n15:00 — Strategy mapping exercise\n15:45 — Action planning and commitments\n16:00 — Close",
  ],
  [
    'title' => 'AI for Public Good: Opportunities and Guardrails',
    'format' => 'virtual',
    'location' => '',
    'days_ahead' => 10,
    'duration_hours' => 1,
    'topics' => ['AI for Public Good', 'Civic Technology', 'Government Modernisation'],
    'speakers' => ['Priya Nair', 'Sam Thornton'],
    'limit' => 200,
    'body' => 'A virtual roundtable examining current AI applications in government and civic contexts, the risks of algorithmic decision-making in public services, and emerging governance frameworks.',
    'schedule' => "12:00 — Panel introductions\n12:10 — Use case overview: AI in government (Priya Nair)\n12:25 — Ethical considerations (Sam Thornton)\n12:40 — Live Q&A\n13:00 — Close",
  ],
  [
    'title' => 'Climate Resilience Planning: A Cross-Sector Roundtable',
    'format' => 'hybrid',
    'location' => 'Metropolitan Planning Council, Boardroom',
    'days_ahead' => 21,
    'duration_hours' => 2,
    'topics' => ['Climate Resilience', 'Nonprofit Innovation'],
    'speakers' => ['Sam Thornton', 'Leila Mansouri'],
    'limit' => 40,
    'body' => 'Bringing together planners, community groups, and businesses to develop shared resilience strategies, identify funding opportunities, and align on priority climate adaptation actions.',
    'schedule' => "14:00 — Scene-setting: climate risk mapping\n14:30 — Panel: sector perspectives on resilience\n15:10 — Facilitated working groups\n15:50 — Synthesis and commitments\n16:00 — Close",
  ],
  [
    'title' => 'Grant Strategy for Technology Initiatives',
    'format' => 'virtual',
    'location' => '',
    'days_ahead' => 28,
    'duration_hours' => 1,
    'topics' => ['Nonprofit Innovation', 'Digital Equity'],
    'speakers' => ['Leila Mansouri'],
    'limit' => 150,
    'body' => 'Practical guidance on identifying, applying for, and managing grants for nonprofit and public sector technology projects. Covers federal, foundation, and corporate funding sources.',
    'schedule' => "10:00 — Funding landscape overview\n10:20 — Anatomy of a strong tech grant application\n10:40 — Common pitfalls and how to avoid them\n10:50 — Q&A\n11:00 — Close",
  ],
  [
    'title' => 'Civic Technology Hackathon: Community Data Challenges',
    'format' => 'in_person',
    'location' => 'Innovation Hub, 450 Market Street',
    'days_ahead' => 35,
    'duration_hours' => 8,
    'topics' => ['Civic Technology', 'Open Data & Transparency', 'Economic Mobility'],
    'speakers' => ['Priya Nair', 'Marcus Delacroix'],
    'limit' => 60,
    'body' => 'A full-day hackathon where technologists, community members, and policymakers collaborate on data-driven solutions to local challenges. Datasets provided by the City of Riverdale.',
    'schedule' => "08:30 — Registration and team formation\n09:00 — Challenge briefing and dataset access\n09:30 — Hacking begins\n12:30 — Lunch and mid-point check-in\n13:00 — Hacking resumes\n16:00 — Project presentations\n17:00 — Judging and prizes\n17:30 — Close",
  ],
  [
    'title' => 'Research Exchange: Community Health Data Methods',
    'format' => 'in_person',
    'location' => 'City University, Harrington Hall 302',
    'days_ahead' => 42,
    'duration_hours' => 3,
    'topics' => ['Academic Research Exchange', 'Community Health', 'Open Data & Transparency'],
    'speakers' => ['Dr. Amara Osei'],
    'limit' => 30,
    'body' => 'A peer exchange for researchers and practitioners exploring methodological questions in community health data: collection, consent, privacy, and sharing frameworks.',
    'schedule' => "09:00 — Welcome (Dr. Osei)\n09:15 — Paper presentation: participatory data collection\n10:00 — Methods showcase: three rapid-fire presentations\n11:00 — Structured discussion: ethics and consent\n11:45 — Collaboration opportunities\n12:00 — Close",
  ],
  [
    'title' => 'Digital Government Forum: Open APIs and Interoperability',
    'format' => 'hybrid',
    'location' => 'City Hall, Council Chambers',
    'days_ahead' => 50,
    'duration_hours' => 4,
    'topics' => ['Government Modernisation', 'Open Data & Transparency', 'Civic Technology'],
    'speakers' => ['Priya Nair'],
    'limit' => 100,
    'body' => 'A public forum examining how government agencies can use open APIs and interoperable data standards to improve service delivery, transparency, and collaboration with civic technologists.',
    'schedule' => "09:00 — Opening remarks\n09:30 — Keynote: The case for open government APIs (Priya Nair)\n10:30 — Panel: practitioners on implementation challenges\n11:30 — Public comment period\n12:00 — Working lunch: standards discussion\n13:00 — Close",
  ],
];

foreach ($events_data as $e) {
  $start = new DateTime("+{$e['days_ahead']} days");
  $start->setTime(9, 0, 0);
  $end = clone $start;
  $end->modify("+{$e['duration_hours']} hours");

  $topic_refs = array_map(
    fn($t) => ['target_id' => $topic_terms[$t]->id()],
    $e['topics']
  );
  $speaker_refs = array_map(
    fn($s) => ['target_id' => $member_nodes[$s]->id()],
    $e['speakers']
  );

  $fields = [
    'type' => 'event',
    'title' => $e['title'],
    'body' => ['value' => $e['body'], 'format' => 'basic_html'],
    'field_event_date' => $start->format('Y-m-d\TH:i:s'),
    'field_event_end_date' => $end->format('Y-m-d\TH:i:s'),
    'field_event_format' => $e['format'],
    'field_event_location' => $e['location'],
    'field_topics' => $topic_refs,
    'field_speakers' => $speaker_refs,
    'field_registration_limit' => $e['limit'],
    'field_event_schedule' => ['value' => $e['schedule'], 'format' => 'plain_text'],
    'status' => 1,
  ];

  if ($e['format'] !== 'in_person') {
    $fields['field_virtual_link'] = [
      'uri' => 'https://meet.example.org/' . strtolower(preg_replace('/\W+/', '-', $e['title'])),
      'title' => 'Join virtual session',
    ];
  }

  $node = Node::create($fields);
  $node->save();
  echo "Created event: {$e['title']}\n";
}

echo "\nDemo content seeded successfully.\n";
echo "Run `drush uli` to log in and explore the site.\n";
