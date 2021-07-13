<?php

/*
  clean.php

  This code is designed to take in the csv within
  https://github.com/philipperemy/name-dataset/blob/master/names_dataset/v2/first_names.zip
  and so clean it as to remove all names using Unicode or any character not A-Z
  or a-z.

  Call with:
  $ php ./clean.php >/tmp/first_names.all

  At time of writing, csv contains lines 1,642,461 lines of the form
  Ivan,100.000000
  محمد,100.000000
  The comma and onwards are discarded. If RTL characters are used, the line is
  discarded.
  With the other work done, there are 844,483 unique names left.

  No attempt is made to discover if remaining candidates are real names: Zzdn
  doesn't look like a name, but does appear to be a gamer's tag, so who am I to
  judge?
 */

// Prepare
$cleaned_arr = array();

// Get the original list into memory
$names_flatfile = file_get_contents('/tmp/first_names.all.csv');
if ($names_flatfile === false) {
  // PHP displays its own error
  exit;
}

// Convert flatfile to an array
$names_arr = explode("\n", $names_flatfile);

// Look at each line at least once
foreach($names_arr as $line) {
  // Look at only what appears before first comma. RTL names all use Unicode
  // so we're happy that this discards them. Also discard names zero or one
  // character long.
  $rv = explode(",", $line, 2);
  $candidate = $rv[0];
  if (mb_strlen($candidate) < 2)
    continue;

  // Look at each character. If any isn't A-Z or a-z then abandon this
  // candidate.
  $cleaned_candidate = '';
  for($a = 0; $a < mb_strlen($candidate); $a++) {
    $mbord = mb_ord($candidate[$a]);
    if (($mbord >= 65 && $mbord <= 90) || ($mbord >= 97 && $mbord <= 122)) {
      $cleaned_candidate .= chr($mbord);
    } else {
      $cleaned_candidate = '';
      break;
    }
  }

  // Check to see if there's a run of 3 or more identical characters after
  // accounting for case. If so, abandon this candidate too
  if (strlen($cleaned_candidate) > 1) {
    $lcase = strtolower($cleaned_candidate);
    $run = 1;
    for($a = 1; $a < strlen($lcase); $a++) {
      if ($lcase[$a] === $lcase[$a - 1])
	$run++;
      elseif ($run >= 3)
	break;
      else
	$run = 1;
    }
    if ($run >= 3)
      continue;

    // Make a note of a successful candidate
    $cleaned_arr[] = $cleaned_candidate;
  }
}

// Sort and make unique the candidates
sort($cleaned_arr);
$uniq_arr = array_unique($cleaned_arr);

// Display successful candidates
foreach($uniq_arr as $name)
  echo $name . "\n";
?>
