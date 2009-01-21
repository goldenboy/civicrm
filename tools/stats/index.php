<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title>CiviCRM usage statistics</title>
  <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="Content-Language" content="en" />
  <meta name="Author" content="Piotr Szotkowski" />
</head>
<body>
<h1>CiviCRM usage statistics</h1>
<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

$user = $pass = false;
require_once 'config.php';
mysql_connect('localhost', $user, $pass);
mysql_select_db('stats');

require_once 'graphs.php';

$charts = array(
    array('title' => 'Distinct installations',
          'query' => 'SELECT COUNT(DISTINCT hash) data, YEAR(time) year, MONTH(time) month FROM stats GROUP BY year, month ORDER BY year, month',
          'type'  => 'trend'),
    array('title' => 'UF usage',
          'query' => 'SELECT COUNT(DISTINCT hash) data, YEAR(time) year, MONTH(time) month, uf compare FROM stats GROUP BY year, month, uf ORDER BY year, month',
          'type'  => 'compare'),
    array('title' => 'CiviCRM versions',
          'query' => 'SELECT COUNT(DISTINCT hash) data, YEAR(time) year, MONTH(time) month, SUBSTR(version, 1, 3) compare FROM stats WHERE version LIKE "_.%" GROUP BY year, month, SUBSTR(version, 1, 3) ORDER BY year, month',
          'type'  => 'compare'),
    array('title' => 'Drupal versions',
          'query' => 'SELECT COUNT(DISTINCT hash) data, YEAR(time) year, MONTH(time) month, SUBSTR(ufv, 1, 1) compare FROM stats WHERE uf = "Drupal" GROUP BY year, month, SUBSTR(ufv, 1, 1) ORDER BY year, month',
          'type'  => 'compare'),
    array('title' => 'Joomla versions',
          'query' => 'SELECT COUNT(DISTINCT hash) data, YEAR(time) year, MONTH(time) month, SUBSTR(ufv, 1, 3) compare FROM stats WHERE uf = "Joomla" GROUP BY year, month, SUBSTR(ufv, 1, 3) ORDER BY year, month',
          'type'  => 'compare'),
    array('title' => 'MySQL versions',
          'query' => 'SELECT COUNT(DISTINCT hash) data, YEAR(time) year, MONTH(time) month, SUBSTR(MySQL, 1, 3) compare FROM stats GROUP BY year, month, SUBSTR(MySQL, 1, 3) ORDER BY year, month',
          'type'  => 'compare'),
    array('title' => 'PHP versions',
          'query' => 'SELECT COUNT(DISTINCT hash) data, YEAR(time) year, MONTH(time) month, SUBSTR(PHP, 1, 3) compare FROM stats GROUP BY year, month, SUBSTR(PHP, 1, 3) ORDER BY year, month',
          'type'  => 'compare'),
    array('title' => 'Default languages',
          'query' => 'SELECT COUNT(DISTINCT hash) data, YEAR(time) year, MONTH(time) month, lang compare FROM stats GROUP BY year, month, lang ORDER BY year, month',
          'type'  => 'compare'),
);

switch ($_GET['current']) {
case false:
    print '<p><a href="?current=1">include partial data for current month</a></p>'; break;
case true:
    print '<p><a href="?">drop partial data for current month</a></p>'; break;
}

foreach ($charts as $chart) {
    switch ($chart['type']) {
    case 'trend':
        $result = trend($chart['query']);
        print "<h2>{$chart['title']} (last: {$result['last']})</h2>";
        print "<p><img src='{$result['url']}' /></p>"; break;
    case 'compare':
        $result = compare($chart['query']);
        print "<h2>{$chart['title']}</h2>";
        print "<p><img src='{$result['url']}' /> <img src='{$result['last']}' /></p>"; break;
    }
}

$fields = array('Activity', 'Case', 'Contact', 'Contribution', 'ContributionPage', 'ContributionProduct', 'Discount', 'Event', 'Friend', 'Grant', 'Mailing', 'Membership', 'MembershipBlock', 'Participant', 'Pledge', 'PledgeBlock', 'PriceSetEntity', 'Relationship', 'UFGroup', 'Widget');

mysql_query('CREATE TEMPORARY TABLE latest_ids SELECT MAX(id) id FROM stats GROUP BY hash');
mysql_query('CREATE INDEX latest_ids_id ON latest_ids (id)');
mysql_query('CREATE TEMPORARY TABLE latest_stats SELECT * FROM stats WHERE id IN (SELECT * FROM latest_ids)');

foreach ($fields as $field) {
    $stat = mysql_fetch_object(mysql_query("SELECT MAX(`$field`) max, ROUND(AVG(`$field`)) avg FROM latest_stats"));
    $tops = mysql_query("SELECT `$field` field, COUNT(*) count FROM latest_stats WHERE `$field` IS NOT NULL GROUP BY field ORDER BY count DESC LIMIT 3");
    print "<h2>$field</h2>";
    print "<p>max: {$stat->max}, avg: {$stat->avg}, most popular counts: ";
    while ($top = mysql_fetch_object($tops)) {
        print "{$top->field} ({$top->count}), ";
    }
    print '</p>';
    print '<table><tr><th colspan="3">range</th><th>count</th></tr>';
    $high = -1;
    $pieces = $stat->max > 10 ? 10 : $stat->max;
    for ($i = 1; $i <= $pieces; $i++) {
        $low  = $high + 1;
        $high = round($i * $stat->max / $pieces);
        $count = mysql_fetch_object(mysql_query("SELECT COUNT(*) count FROM latest_stats WHERE `$field` BETWEEN $low AND $high"));
        print "<tr style='text-align: right'><td>$low</td><td>–</td><td>$high</td><td>$count->count</td></tr>";
    }
    print '</table>';
}

?>
</body>
</html>
