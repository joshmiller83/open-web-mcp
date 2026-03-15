<?php
/**
 * Step 4: Enrich Person profile nodes with photos and better bios.
 *
 * Downloads stock photos from Unsplash and attaches them as Drupal media.
 */

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;

$persons = [
  13 => [
    'name' => 'Dr. Amara Osei',
    'role' => 'Public Health Researcher',
    'organization' => 'Center for Urban Health Equity',
    'sector' => 'Academic',
    'photo_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=600&h=600&fit=crop',
    'bio' => '<p>Dr. Amara Osei is a public health researcher specializing in community health data systems and digital health equity. As a principal investigator at the Center for Urban Health Equity, she leads multi-site studies on how open data infrastructure can close persistent health disparities in under-resourced communities.</p><p>Her work bridges academic research and on-the-ground practice — developing tools that community health workers actually use. She is particularly focused on how AI-powered analysis can surface patterns in community health data that traditional epidemiological methods miss, while ensuring those systems remain accountable and interpretable to the communities they serve.</p><p>Dr. Osei is a frequent speaker on Open Data for Healthier Communities and serves on the advisory board of two national public health informatics initiatives. She holds a PhD in Epidemiology from Johns Hopkins and an MPH from Spelman College.</p>',
  ],
  14 => [
    'name' => 'Marcus Delacroix',
    'role' => 'Executive Director',
    'organization' => 'Digital Access Alliance',
    'sector' => 'Nonprofit',
    'photo_url' => 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=600&h=600&fit=crop',
    'bio' => '<p>Marcus Delacroix leads the Digital Access Alliance, a nonprofit coalition working to ensure that every community has the connectivity, devices, and digital skills needed to participate fully in civic and economic life. Under his leadership, the Alliance has grown from a local Atlanta initiative to a 14-state network serving over 200 community organizations.</p><p>Marcus brings a practitioner\'s lens to digital equity policy — he spent a decade running after-school technology programs before moving into advocacy and coalition-building. He is a leading voice on how government digital services must be designed for the populations they claim to serve, not just for tech-fluent users.</p><p>He co-chairs the National Digital Inclusion Alliance\'s policy committee and advises the FCC on broadband adoption programs. His current focus is building sustainable funding models for community technology centers in the post-pandemic environment.</p>',
  ],
  15 => [
    'name' => 'Priya Nair',
    'role' => 'Program Manager, Digital Services',
    'organization' => 'Georgia Department of Community Health',
    'sector' => 'Government',
    'photo_url' => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=600&h=600&fit=crop',
    'bio' => '<p>Priya Nair manages digital transformation initiatives for the Georgia Department of Community Health, where she oversees the modernization of public-facing health services and the department\'s transition to open APIs and interoperable data standards. She joined state government after a decade in the private sector specifically to bring modern product thinking to government services.</p><p>Priya is passionate about the intersection of open government data and civic technology — believing that public agencies have both a responsibility and an opportunity to be the most open, accessible data providers in their ecosystems. She has led Georgia\'s adoption of FHIR health data standards and open API frameworks that allow community organizations to build services on top of state health data.</p><p>She is an active contributor to the Code for America network and speaks regularly on Digital Government and Open APIs. Priya holds an MS in Information Systems from Georgia Tech and a BA in Political Science from Emory University.</p>',
  ],
  16 => [
    'name' => 'Sam Thornton',
    'role' => 'Director of Civic Technology',
    'organization' => 'Meridian Labs',
    'sector' => 'Private Sector',
    'photo_url' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=600&h=600&fit=crop',
    'bio' => '<p>Sam Thornton directs the Civic Technology practice at Meridian Labs, a technology consultancy that helps government agencies, nonprofits, and foundations use data and AI to improve outcomes. Sam has spent 15 years building technology solutions at the intersection of public interest and private capability — from workforce development platforms to predictive analytics tools for child welfare agencies.</p><p>At Meridian Labs, Sam leads engagements focused on responsible AI deployment in public sector contexts — helping clients think through governance frameworks, bias auditing, and community engagement alongside the technical implementation. He is a strong advocate for the Model Context Protocol and open standards that prevent vendor lock-in in government technology.</p><p>Sam is a founding member of the AI for Public Good working group at the Center for Democracy & Technology and has testified before Congress on algorithmic accountability. He holds an MBA from Wharton and a BS in Computer Science from Carnegie Mellon.</p>',
  ],
  17 => [
    'name' => 'Leila Mansouri',
    'role' => 'Community Organizer & Data Advocate',
    'organization' => 'Southwest Atlanta Community Land Trust',
    'sector' => 'Community',
    'photo_url' => 'https://images.unsplash.com/photo-1499952127939-9bbf5af6c51c?w=600&h=600&fit=crop',
    'bio' => '<p>Leila Mansouri is a community organizer with the Southwest Atlanta Community Land Trust, where she has spent eight years building resident power around housing stability, environmental justice, and community ownership of land and data. She approaches data advocacy from the ground up — helping residents understand how data about their neighborhoods is collected, used, and sometimes weaponized.</p><p>Leila is a nationally recognized voice on data sovereignty and community data rights. She argues that open data only serves equity if communities have real agency over how their data is governed — not just access to dashboards built by outsiders. She has developed a community data literacy curriculum used by over 40 neighborhood organizations across the Southeast.</p><p>Her current work focuses on Climate Resilience Planning and how community land trusts can use participatory data collection to build neighborhood-level climate adaptation plans. She serves on the board of the National Community Land Trust Network and is a 2023 Obama Foundation Scholar.</p>',
  ],
];

foreach ($persons as $nid => $data) {
  echo "\nProcessing: {$data['name']} (nid {$nid})\n";

  // Download photo
  $photo_data = @file_get_contents($data['photo_url']);
  if (!$photo_data) {
    echo "  WARNING: Could not download photo from {$data['photo_url']}\n";
    $media_entity = NULL;
  } else {
    // Save as managed file
    $filename = 'person-' . $nid . '-' . preg_replace('/[^a-z0-9]/', '-', strtolower($data['name'])) . '.jpg';
    $file_uri = 'public://persons/' . $filename;

    // Ensure directory exists
    $persons_dir = 'public://persons';
    \Drupal::service('file_system')->prepareDirectory($persons_dir, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY);

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
        'name' => $data['name'] . ' profile photo',
        'field_media_image' => [
          'target_id' => $file->id(),
          'alt' => $data['name'],
        ],
        'status' => 1,
      ]);
      $media_entity->save();
      echo "  Created media entity: " . $media_entity->id() . "\n";
    }
  }

  // Update person node
  $node = Node::load($nid);
  if (!$node) {
    echo "  ERROR: Node {$nid} not found.\n";
    continue;
  }

  $node->setTitle($data['name']);

  // Set bio/description
  if ($node->hasField('field_description')) {
    $node->set('field_description', [
      'value' => $data['bio'],
      'format' => 'full_html',
    ]);
  }

  // Set role/job title
  if ($node->hasField('field_person__role_job_title')) {
    $node->set('field_person__role_job_title', $data['role']);
  }

  // Set organization
  if ($node->hasField('field_organization')) {
    $node->set('field_organization', $data['organization']);
  }

  // Set sector (plain text field)
  if ($node->hasField('field_sector')) {
    $node->set('field_sector', $data['sector']);
    echo "  Set sector: {$data['sector']}\n";
  }

  // Attach media
  if ($media_entity && $node->hasField('field_featured_image')) {
    $node->set('field_featured_image', ['target_id' => $media_entity->id()]);
  }

  $node->save();
  echo "  Updated node: {$data['name']}\n";
}

echo "\nPerson enrichment complete.\n";
