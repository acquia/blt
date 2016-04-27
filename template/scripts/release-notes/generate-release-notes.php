<?php
if ($argc < 3) {
  print 'You must pass in arguments for the following variables:' . PHP_EOL
  . 'service(git or stash), username:password, project:repository:branch, [month/day/year], [limit]' . PHP_EOL . PHP_EOL
  . 'example: php generate-release-notes.php github dan:password acquia-pso:test-repo:master > example-release-notes.md' . PHP_EOL
  . 'example: php generate-release-notes.php github dan:password acquia-pso:test-repo:master 4/10/2014 > example-release-notes.md' . PHP_EOL
  . 'example: php generate-release-notes.php stash:stash.client.com dan:password client-project:test-repo:master 4/10/2014 100 > example-release-notes.md' . PHP_EOL . PHP_EOL
  . 'note: by default, PRs are gathered from 30 days previous and up to 100 PRs' . PHP_EOL;
  exit;
}

// Parse Service
$service_array = explode(':', $argv[1]);

$service = array(
  'type' => $service_array[0],
);

if (count($service_array) > 1) {
  $service['base_path'] = $service_array[1];
}

unset($service_array);

// Set Username / Password
$user_array = explode(':', $argv[2]);

$user = array(
  'name' => $user_array[0],
  'password' => $user_array[1],
);

unset($user_array);

// Split Project / Repo / Branch into variables.
$repo_array = explode(':', $argv[3]);

$repository = array(
  'project' => $repo_array[0],
  'repository' => $repo_array[1],
  'branch' => $repo_array[2],
);

unset($repo_array);

// Parse date.
$since = ($argc < 5) ? strtotime('30 days ago') : strtotime($argv[4]);

// Default to pulling 100 PRs.
$limit = ($argc < 6) ? 100 : $argv[5];

// Print the header.
print '# Release notes for ' . date("F j, Y") . PHP_EOL . PHP_EOL;

// Proceed based on service.
switch ($service['type']) {
  case 'github':
    process_github();
    break;

  case 'stash':
    process_stash();
    break;
}

function process_github() {
  global $repository, $since, $limit;

  // Create a date like 2014-12-23T00:00:00Z.
  $since_github = date('Y-m-d', $since) . 'T00:00:00Z';

  // We can only get 100 results at a time, so we need to split the calls into chunks of 100.
  $calls = ceil($limit / 100);

  $url = 'https://api.github.com/repos/' . $repository['project'] . '/' . $repository['repository'] . '/pulls?state=closed&since=' . $since_github . '&per_page=' . $limit . '&base=' . $repository['branch'];

  for ($page = 1; $page <= $calls; $page++) {

    $prs = fetch_pr($url . '&page=' . $page);

    // Print each Pull Request.
    foreach ($prs as $pr) {
      // We don't want to print PRs that are not merged.
      if (is_null($pr['merged_at'])) {
        continue;
      }

      // Check our date is within the time period.
      $closed_date = strtotime($pr['closed_at']);

      if ($closed_date < $since) {
        continue;
      }

      print_pr_compact($pr['title'], $pr['body'], $closed_date, $pr['html_url']);
    }
  }
}

function process_stash() {
  global $service, $repository, $since, $limit;

  $url = 'https://' . $service['base_path'] . '/rest/api/1.0/projects/' . $repository['project'] . '/repos/' . $repository['repository'] . '/pull-requests?state=merged' . '&limit=' . $limit . '&at=refs/heads/' . $repository['branch'];

  $json = fetch_pr($url);

  // Print each Pull Request.
  foreach ($json['values'] as $pr) {
    // Check our date is within the time period.
    // Stash uses epoch time with milliseconds.
    $closed_date = date($pr['updatedDate'] / 1000);

    if ($closed_date < $since) {
      continue;
    }

    $link = 'https://' . $service['base_path'] . $pr['link']['url'];

    print_pr($pr['title'], $pr['description'], $closed_date, $link);
  }
}

function fetch_pr($url) {
  global $user;
  // Download the json file.
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_USERPWD, $user['name'] . ':' . $user['password']);
  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Acquia-PS');
  curl_setopt($ch, CURLOPT_URL, $url);

  $json_raw = curl_exec($ch);
  $chinfo = curl_getinfo($ch);

  // We bail if we don't get a successful connection.
  if ($chinfo['http_code'] !== 200) {
    print 'HTTP Error: ' . $chinfo['http_code'] . PHP_EOL;
    print 'URL: ' . $url . PHP_EOL;
    print $json_raw . PHP_EOL;
    exit;
  }

  curl_close($ch);

  // Decode the JSON.
  return json_decode($json_raw, TRUE);
}

function print_pr($title, $description, $date, $link) {
  // Print the PR Title.
  print '## ' . $title . PHP_EOL;
  // Print the PR Time and URL.
  print date("F j, Y", $date) . ' ([' . $link . ']' . '(' . $link . '))' . PHP_EOL . PHP_EOL;
  // Print the PR Body.
  print $description . PHP_EOL . PHP_EOL;
}

function print_pr_compact($title, $description, $date, $link) {
  $date_formatted =  date("F j, Y", $date);
  print "* $date_formatted: [$title]($link) \n";
}

?>
