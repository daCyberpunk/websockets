<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "websockets".
 *
 * Auto generated 07-03-2016 14:58
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'WebSockets',
  'description' => 'Provides real time, bi-directional connection between clients and server over WebSockets.',
  'category' => 'services',
  'version' => '2.3.0',
  'state' => 'beta',
  'uploadfolder' => 0,
  'createDirs' => '',
  'clearcacheonload' => 0,
  'author' => 'Taras Zakus',
  'author_email' => 'Taras.Zakus@pixabit-interactive.de',
  'author_company' => 'Pixabit Interactive',
  'constraints' => 
  array (
    'depends' => 
    array (
      'php' => '5.3.0-0.0.0',
      'typo3' => '6.0.0-0.0.0',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
);

