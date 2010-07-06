CREATE TABLE `job_queue` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `class` tinyint(3) unsigned NOT NULL,
      `priority` tinyint(3) unsigned NOT NULL,
      `execute_at` datetime default NULL,
      `state` longblob NOT NULL,
      PRIMARY KEY  (`id`),
      KEY `execute_at` (`execute_at`)
) DEFAULT CHARSET=utf8;
