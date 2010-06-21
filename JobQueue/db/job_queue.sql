CREATE TABLE `job_queue` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `class` tinyint(3) unsigned NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL,
  `state` longblob NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
