#!/usr/bin/env php
<?php
echo "Executing .git/hooks/commit-msg...\n";
$repo_root = getcwd();

$original_argv = $_SERVER['argv'];
$commit_msg = rtrim(file_get_contents($original_argv[1]), "\n");

// Construct pseudo `blt commit-msg $commit_msg` call.
$_SERVER['argv'] = [
  $repo_root . '/bin/blt',
  'internal:git-hook:execute:commit-msg',
  $commit_msg,
];
$_SERVER['argc'] = count($_SERVER['argv']);

require __DIR__ . '/../../bin/blt-robo.php';
