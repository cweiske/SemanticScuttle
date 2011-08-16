<?php
/* Export data with semantic format (SIOC: http://sioc-project.org/, FOAF, SKOS, Annotea Ontology) */

$httpContentType = 'text/xml';
require_once '../www-header.php';

/* Service creation: only useful services are created */
$userservice =SemanticScuttle_Service_Factory::get('User');
$bookmarkservice =SemanticScuttle_Service_Factory::get('Bookmark');

?>
<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\"\n?>"; ?>
<rdf:RDF
	xmlns="http://xmlns.com/foaf/0.1/"
	xmlns:foaf="http://xmlns.com/foaf/0.1/"
	xmlns:rss="http://purl.org/rss/1.0/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:dcterms="http://purl.org/dc/terms/"
	xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:sioc="http://rdfs.org/sioc/ns#"
	xmlns:sioc_t="http://rdfs.org/sioc/types#"	
	xmlns:bm="http://www.w3.org/2002/01/bookmark#"
	xmlns:skos="http://www.w3.org/2004/02/skos/core#">

<?php
//site and community are described using FOAF and SIOC ontology
?>
<sioc:Site rdf:about="<?php echo ROOT?>" >
  <rdf:label><?php echo $GLOBALS['sitename']?></rdf:label>
</sioc:Site>

<?php //<sioc_t:BookmarkFolder />?>

<?php
//users are described using FOAF and SIOC ontology
$users = $userservice->getObjectUsers();

$usersArray = array(); // useful for bookmarks display
foreach($users as $user) {
  $usersArray[$user->getId()] = $user->getUserName();
}
?>

<?php foreach($users as $user) :?>
<sioc:User rdf:about="<?php echo createUrl('profile', $user->getUserName())?>">
  <sioc:name><?php echo $user->getUserName() ?></sioc:name>
  <sioc:member_of rdf:resource="<?php echo ROOT?>" />
</sioc:User>
<?php endforeach; ?>

<?php
/*
No page for usergroup (users/admin) for the moment
  <sioc:Usergroup rdf:ID="authors">
  <sioc:name>Authors at PlanetRDF.com</sioc:name>
  <sioc:has_member rdf:nodeID="sioc-id2245901" />
 </sioc:Usergroup>
*/
?>

<?php 
//bookmarks are described using Annotea ontology
$bookmarks =& $bookmarkservice->getBookmarks(0, NULL, NULL, NULL);
?>

<?php foreach($bookmarks['bookmarks'] as $bookmark): ?>
<bm:Bookmark rdf:about="<?php echo createUrl('history', $bookmark['bHash']) ?>">
  <dc:title><?php echo filter($bookmark['bTitle']) ?></dc:title>
  <dc:created><?php echo filter($bookmark['bDatetime']) ?></dc:created>
  <dc:description><?php echo filter(strip_tags($bookmark['bDescription'])) ?></dc:description>
  <dc:date><?php echo $bookmark['bModified'] ?></dc:date>
  <bm:recalls rdf:resource="<?php echo filter($bookmark['bAddress']) ?>"/>
  <sioc:owner_of rdf:resource="<?php echo createUrl('profile', $usersArray[$bookmark['uId']]) ?>"/>
  <?php foreach($bookmark['tags'] as $tag): ?>
    <sioc:topic>
      <skos:concept rdf:about="<?php echo createUrl('bookmarks', $usersArray[$bookmark['uId']].'/'.$tag) ?>" />
    </sioc:topic>     
  <?php endforeach; ?>  
</bm:Bookmark>

<?php endforeach; ?>

<?php 
// tags and concepts are described using SKOS ontology
//concept for user/admins, preflabel, definition, top concept
?>

</rdf:RDF>

