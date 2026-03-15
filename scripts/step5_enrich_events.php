<?php
/**
 * Step 5: Enrich Event nodes with photos, descriptions, and metadata.
 */

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;

$events = [
  8 => [
    'title' => 'DrupalCon Atlanta 2025',
    'photo_url' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&h=600&fit=crop',
    'format' => 'Conference',
    'location' => 'Georgia World Congress Center, Atlanta, GA',
    'link' => 'https://events.drupal.org/atlanta2025',
    'description' => '<p>DrupalCon Atlanta 2025 brings together thousands of Drupal developers, designers, site builders, and content strategists from around the world for the premier open-source web conference. This year\'s event focuses on Drupal CMS — the exciting new initiative making Drupal accessible to a broader audience — and the emerging frontier of AI-ready architecture.</p><p>Sessions span the full range of Drupal\'s ecosystem: from deep technical dives into core development to strategic discussions about content management, digital experience platforms, and open standards. The Open Web Exchange demo is featured as part of the AI + Drupal track, showcasing how the Model Context Protocol enables AI assistants to interact with Drupal sites as structured knowledge bases.</p><p>DrupalCon Atlanta is also a celebration of community — the Driesnote, contribution sprints, and the hallway track are where lasting collaborations begin. Whether you\'re a core contributor or attending your first DrupalCon, this is where the future of the open web takes shape.</p>',
  ],
  18 => [
    'title' => 'Open Data for Healthier Communities',
    'photo_url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&h=600&fit=crop',
    'format' => 'Workshop',
    'location' => 'Emory University School of Public Health, Atlanta, GA',
    'link' => '',
    'description' => '<p>This hands-on workshop brings together public health researchers, community health workers, government data officers, and civic technologists to explore how open health data can drive better outcomes in under-resourced communities. Participants will work directly with real datasets from Atlanta-area health agencies, learning how to access, analyze, and visualize community health data using open tools.</p><p>Morning sessions focus on data literacy and data rights — including how communities can advocate for better data collection practices and push back against uses of their data that don\'t serve their interests. Afternoon sessions are project-based: small cross-sector teams will develop prototype data tools addressing specific community health challenges identified by neighborhood partners.</p><p>The workshop is designed for practitioners, not just researchers. Bring your community\'s questions — and be prepared to leave with a working prototype, new collaborators, and a clearer roadmap for using open data in your work.</p>',
  ],
  19 => [
    'title' => 'Digital Equity Workshop: Closing the Access Gap',
    'photo_url' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=1200&h=600&fit=crop',
    'format' => 'Workshop',
    'location' => 'Atlanta Civic Center, Atlanta, GA',
    'link' => '',
    'description' => '<p>Digital equity isn\'t just about internet access — it\'s about whether people have the devices, skills, and support to participate fully in an increasingly digital society. This workshop convenes practitioners from nonprofits, government agencies, libraries, and community organizations to share strategies, learn from each other\'s failures, and build a more coordinated regional approach to closing the digital divide.</p><p>The workshop features case studies from successful digital equity programs in Atlanta and beyond, including the Digital Access Alliance\'s community technology center model and the City of Atlanta\'s digital navigator program. Participants will also hear from community members directly about the barriers they face — and what solutions actually work.</p><p>Key themes include: sustainable funding models for community tech centers, workforce development pathways in the digital economy, language access and culturally relevant technology programming, and advocacy strategies for federal and state broadband funding. Leave with a network of partners and a action plan for your organization.</p>',
  ],
  20 => [
    'title' => 'AI for Public Good: Opportunities and Guardrails',
    'photo_url' => 'https://images.unsplash.com/photo-1573164574572-cb89e39749b4?w=1200&h=600&fit=crop',
    'format' => 'Summit',
    'location' => 'Georgia Tech Global Learning Center, Atlanta, GA',
    'link' => '',
    'description' => '<p>Artificial intelligence is reshaping every sector — but who benefits, who bears the risks, and who gets to decide? This summit convenes researchers, policymakers, technologists, civil society advocates, and community members to grapple with both the genuine opportunities and the serious guardrails that responsible AI deployment requires.</p><p>Morning plenary sessions set the stage with an honest assessment of where AI is delivering on its public-benefit promise and where it is falling short — from predictive policing and child welfare algorithms to climate modeling and medical diagnosis. Afternoon breakout tracks allow participants to go deep on specific domains: AI in government services, AI for scientific research, AI governance frameworks, and community-led approaches to algorithmic accountability.</p><p>The summit is explicitly not a technology showcase. Demos and product pitches are excluded in favor of honest conversation about what conditions need to be in place for AI to actually serve the public good. The Model Context Protocol session demonstrates how open standards can prevent AI vendor lock-in in civic technology — a prerequisite for genuine public accountability.</p>',
  ],
  21 => [
    'title' => 'Climate Resilience Planning: A Cross-Sector Roundtable',
    'photo_url' => 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?w=1200&h=600&fit=crop',
    'format' => 'Roundtable',
    'location' => 'The Carter Center, Atlanta, GA',
    'link' => '',
    'description' => '<p>Climate resilience is fundamentally a cross-sector challenge — no single agency, organization, or community can address it alone. This roundtable brings together urban planners, community land trusts, environmental justice advocates, public health researchers, infrastructure managers, and climate scientists to develop integrated, neighborhood-level climate adaptation strategies for the Atlanta metro region.</p><p>The roundtable format is deliberately small and working-focused: 40 participants, 6 working tables, and a full day of structured dialogue culminating in shared commitments and a public report. Unlike larger conferences, this event is designed to produce tangible outcomes — not just conversation. Tables will work on specific challenges including heat island mitigation in low-income neighborhoods, flood resilience in communities of color, green infrastructure financing, and community data collection for climate vulnerability mapping.</p><p>Participants will hear from Leila Mansouri and the Southwest Atlanta Community Land Trust about their participatory climate data project, which is developing a community-led model for neighborhood resilience planning that centers the knowledge and priorities of residents most affected by climate change.</p>',
  ],
  22 => [
    'title' => 'Grant Strategy for Technology Initiatives',
    'photo_url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&h=600&fit=crop',
    'format' => 'Workshop',
    'location' => 'Community Foundation for Greater Atlanta, Atlanta, GA',
    'link' => '',
    'description' => '<p>Securing sustainable funding for technology-forward nonprofit and public sector initiatives requires a different approach than traditional program grants. This workshop is designed for development directors, executive directors, and program managers who are navigating the landscape of technology grants from private foundations, government agencies, and impact investors.</p><p>Morning sessions provide a structured overview of the current funding landscape for civic technology, digital equity, and public interest AI — including which funders are most active, what they\'re looking for, and what common mistakes applicants make. Participants will learn how to articulate technology initiatives in terms that resonate with program officers who may not have a technical background.</p><p>The afternoon is hands-on grant writing and strategy work. Participants will workshop their own grant concepts with expert coaches and peers, developing stronger narratives and more competitive proposals. Special attention will be given to multi-year technology sustainability planning — a critical weakness in many grant applications. Each participant leaves with a revised grant narrative and a personalized funder prospect list.</p>',
  ],
  23 => [
    'title' => 'Civic Technology Hackathon: Community Data Challenges',
    'photo_url' => 'https://images.unsplash.com/photo-1573164574572-cb89e39749b4?w=1200&h=600&fit=crop',
    'format' => 'Hackathon',
    'location' => 'Atlanta Tech Village, Atlanta, GA',
    'link' => '',
    'description' => '<p>This 48-hour civic hackathon challenges developers, designers, data scientists, and community advocates to build technology solutions that address real challenges identified by Atlanta-area community organizations. Unlike traditional hackathons, this event is community-problem-first: challenge statements come directly from neighborhood groups, social service agencies, and community land trusts — not from corporate sponsors.</p><p>Teams of 3–6 people work intensively over the weekend, with access to open government datasets, community data partners, and mentors from the civic technology community. Projects are evaluated not just on technical elegance but on genuine community usefulness, sustainability, and the degree to which affected communities were centered in the design process.</p><p>Past hackathon projects have included a multilingual eviction prevention resource finder (now maintained by the Atlanta Volunteer Lawyers Foundation), a community air quality monitoring dashboard (deployed in three neighborhoods), and an open API for Atlanta\'s afterschool program availability data. This year\'s challenges focus on climate resilience data, digital access mapping, and participatory budgeting tools.</p>',
  ],
  24 => [
    'title' => 'Research Exchange: Community Health Data Methods',
    'photo_url' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=1200&h=600&fit=crop',
    'format' => 'Symposium',
    'location' => 'Morehouse School of Medicine, Atlanta, GA',
    'link' => '',
    'description' => '<p>This symposium creates a structured space for community health researchers to share methodological innovations and learn from each other\'s work on community health data collection, analysis, and use. The focus is on methods that are both rigorous by academic standards and genuinely useful to the communities being studied — including participatory research designs, community-controlled data governance, and dissemination strategies that reach practitioners, not just academic journals.</p><p>Featured presentations will cover community-based participatory research methods for health data collection, the ethics and practicalities of community data agreements, using open-source tools for community health analysis, and how to work with community health workers as genuine research partners rather than data collectors. Dr. Amara Osei will present findings from her multi-site study on community health data infrastructure in under-resourced urban neighborhoods.</p><p>The symposium is designed for researchers at all career stages and intentionally includes community practitioners as full participants — not just as subjects or audience members. Graduate students are especially encouraged to attend and will have dedicated networking time with senior researchers and community partners.</p>',
  ],
  25 => [
    'title' => 'Digital Government Forum: Open APIs and Interoperability',
    'photo_url' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&h=600&fit=crop',
    'format' => 'Forum',
    'location' => 'Georgia State Capitol Complex, Atlanta, GA',
    'link' => '',
    'description' => '<p>Government agencies at every level are sitting on vast stores of public data — but too often that data is locked in siloed systems, proprietary formats, and bureaucratic processes that make it nearly impossible for community organizations, researchers, and civic technologists to use it. This forum brings together government data officers, civic technologists, policy advocates, and open standards practitioners to advance the case for open APIs and genuine data interoperability in government.</p><p>The morning program focuses on the policy and governance landscape: what legal, procurement, and organizational barriers prevent agencies from publishing open APIs, and what successful reform strategies look like. Georgia\'s own experience with FHIR health data standards is presented as a case study, with honest discussion of what went well and what didn\'t. Federal perspective is provided by representatives from the White House Office of Science and Technology Policy and the General Services Administration\'s digital services team.</p><p>Afternoon sessions are technical and practical: hands-on workshops on API design for public sector use, developer experience best practices, and security and privacy considerations for open government data. The Model Context Protocol is featured as an emerging standard that could enable AI assistants to interact with government data APIs in a standardized, accountable way — reducing the cost of civic technology development and preventing proprietary lock-in.</p>',
  ],
];

// Photo cache to avoid downloading the same image twice
$photo_cache = [];

foreach ($events as $nid => $data) {
  echo "\nProcessing: {$data['title']} (nid {$nid})\n";

  $photo_url = $data['photo_url'];

  if (isset($photo_cache[$photo_url])) {
    $media_entity = $photo_cache[$photo_url];
    echo "  Reusing cached media entity: " . $media_entity->id() . "\n";
  } else {
    // Download photo
    $photo_data = @file_get_contents($photo_url);
    if (!$photo_data) {
      echo "  WARNING: Could not download photo from {$photo_url}\n";
      $media_entity = NULL;
    } else {
      // Save as managed file
      $url_hash = substr(md5($photo_url), 0, 8);
      $filename = 'event-photo-' . $url_hash . '.jpg';
      $file_uri = 'public://events/' . $filename;

      // Ensure directory exists
      $events_dir = 'public://events';
      \Drupal::service('file_system')->prepareDirectory($events_dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);

      $file = \Drupal::service('file.repository')->writeData(
        $photo_data,
        $file_uri,
        \Drupal\Core\File\FileExists::Replace
      );

      if (!$file) {
        echo "  WARNING: Could not save file.\n";
        $media_entity = NULL;
      } else {
        echo "  Saved photo: {$file->getFileUri()}\n";

        // Create Media entity
        $media_entity = Media::create([
          'bundle' => 'image',
          'name' => $data['title'] . ' featured image',
          'field_media_image' => [
            'target_id' => $file->id(),
            'alt' => $data['title'],
          ],
          'status' => 1,
        ]);
        $media_entity->save();
        echo "  Created media entity: " . $media_entity->id() . "\n";
        $photo_cache[$photo_url] = $media_entity;
      }
    }
  }

  // Update event node
  $node = Node::load($nid);
  if (!$node) {
    echo "  ERROR: Node {$nid} not found.\n";
    continue;
  }

  $node->setTitle($data['title']);

  // Set description
  if ($node->hasField('field_description')) {
    $node->set('field_description', [
      'value' => $data['description'],
      'format' => 'full_html',
    ]);
  }

  // Set event format (plain text field)
  if ($node->hasField('field_event_format') && !empty($data['format'])) {
    $node->set('field_event_format', $data['format']);
    echo "  Set format: {$data['format']}\n";
  }

  // Set location
  if ($node->hasField('field_event__location_name') && !empty($data['location'])) {
    $node->set('field_event__location_name', $data['location']);
  }

  // Set link
  if ($node->hasField('field_event__link') && !empty($data['link'])) {
    $node->set('field_event__link', [
      'uri' => $data['link'],
      'title' => 'Event details',
    ]);
  }

  // Attach media
  if ($media_entity && $node->hasField('field_featured_image')) {
    $node->set('field_featured_image', ['target_id' => $media_entity->id()]);
  }

  $node->save();
  echo "  Updated node: {$data['title']}\n";
}

echo "\nEvent enrichment complete.\n";
