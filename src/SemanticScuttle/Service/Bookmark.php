<?php
/**
 * SemanticScuttle - your social bookmark manager.
 *
 * PHP version 5.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */

/**
 * SemanticScuttle bookmark service.
 *
 * @category Bookmarking
 * @package  SemanticScuttle
 * @author   Benjamin Huynh-Kim-Bang <mensonge@users.sourceforge.net>
 * @author   Christian Weiske <cweiske@cweiske.de>
 * @author   Eric Dane <ericdane@users.sourceforge.net>
 * @license  GPL http://www.gnu.org/licenses/gpl.html
 * @link     http://sourceforge.net/projects/semanticscuttle
 */
class SemanticScuttle_Service_Bookmark extends SemanticScuttle_DbService
{
    /**
     * Returns the single service instance
     *
     * @param DB $db Database object
     *
     * @return SemanticScuttle_Service
     */
    public static function getInstance($db)
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new self($db);
        }
        return $instance;
    }



    public function __construct($db)
    {
        $this->db = $db;
        $this->tablename = $GLOBALS['tableprefix'] .'bookmarks';
    }



    function _getbookmark($fieldname, $value, $all = false)
    {
        if (!$all) {
            $userservice = SemanticScuttle_Service_Factory :: get('User');
            $sId = $userservice->getCurrentUserId();
            $range = ' AND uId = '. $sId;
        } else {
            $range = '';
        }

        $query = 'SELECT * FROM '. $this->getTableName() .' WHERE '. $fieldname .' = "'. $this->db->sql_escape($value) .'"'. $range;

        if (!($dbresult = & $this->db->sql_query_limit($query, 1, 0))) {
            message_die(GENERAL_ERROR, 'Could not get bookmark', '', __LINE__, __FILE__, $query, $this->db);
        }

        if ($row =& $this->db->sql_fetchrow($dbresult)) {
            $output = $row;
        } else {
            $output =  false;
        }
        $this->db->sql_freeresult($dbresult);
        return $output;
    }



    /**
     * Load a single bookmark and return it.
     * When a user is logged on, the returned array will contain
     * keys "hasVoted" and "vote".
     *
     * @param integer $bid          Bookmark ID
     * @param boolean $include_tags If tags shall be loaded
     *
     * @return mixed Array with bookmark data or false in case
     *               of an error.
     */
    function getBookmark($bid, $include_tags = false)
    {
        if (!is_numeric($bid)) {
            return false;
        }

        $userservice = SemanticScuttle_Service_Factory::get('User');

        $query_1 = 'B.*';
        $query_2 = $this->getTableName() . ' as B';

        //Voting system
        //needs to be directly after FROM bookmarks
        if ($GLOBALS['enableVoting'] && $userservice->isLoggedOn()) {
            $cuid = $userservice->getCurrentUserId();
            $vs = SemanticScuttle_Service_Factory::get('Vote');
            $query_1 .= ', !ISNULL(V.bId) as hasVoted, V.vote as vote';
            $query_2 .= ' LEFT JOIN ' . $vs->getTableName() . ' AS V'
                . ' ON B.bId = V.bId'
                . ' AND V.uId = ' . (int)$cuid;
        }

        $sql = 'SELECT ' . $query_1 . ' FROM '
            . $query_2
            .' WHERE B.bId = '. $this->db->sql_escape($bid);

        if (!($dbresult = & $this->db->sql_query($sql))) {
            message_die(
                GENERAL_ERROR, 'Could not get bookmark',
                '', __LINE__, __FILE__, $sql, $this->db
            );
        }

        if ($row = $this->db->sql_fetchrow($dbresult)) {
            if ($include_tags) {
                $b2tservice = SemanticScuttle_Service_Factory::get('Bookmark2Tag');
                $row['tags'] = $b2tservice->getTagsForBookmark($bid);
            }
            $output = $row;
        } else {
            $output = false;
        }
        $this->db->sql_freeresult($dbresult);
        return $output;
    }



    function getBookmarkByAddress($address)
    {
        $hash = md5($address);
        return $this->getBookmarkByHash($hash);
    }



    function getBookmarkByHash($hash)
    {
        return $this->_getbookmark('bHash', $hash, true);
    }



    /**
     * Counts bookmarks for a user.
     *
     * @param integer $uId   User ID
     * @param string  $range Range of bookmarks:
     *                       'public', 'shared', 'private'
     *                       or 'all'
     *
     * @return integer Number of bookmarks
     */
    public function countBookmarks($uId, $range = 'public')
    {
        $sql = 'SELECT COUNT(*) FROM '. $this->getTableName();
        $sql.= ' WHERE uId = ' . intval($uId);
        switch ($range) {
        case 'all':
            //no constraints
            break;
        case 'private':
            $sql .= ' AND bStatus = 2';
            break;
        case 'shared':
            $sql .= ' AND bStatus = 1';
            break;
        case 'public':
        default:
            $sql .= ' AND bStatus = 0';
            break;
        }

        if (!($dbresult = $this->db->sql_query($sql))) {
            message_die(
                GENERAL_ERROR, 'Could not get vars',
                '', __LINE__, __FILE__, $sql, $this->db
            );
        }
        $count = $this->db->sql_fetchfield(0, 0);
        $this->db->sql_freeresult($dbresult);
        return $count;
    }



    /**
     * Check if a bookmark may be edited by the current user
     *
     * @param integer|array $bookmark Bookmark uId or bookmark array
     *
     * @return boolean True if allowed
     */
    function editAllowed($bookmark)
    {
        if (!is_numeric($bookmark)
            && (!is_array($bookmark)
                || !isset($bookmark['bId'])
                || !is_numeric($bookmark['bId'])
            )
        ) {
            return false;
        }

        if (!is_array($bookmark)
             && !($bookmark = $this->getBookmark($bookmark))
        ) {
            return false;
        }

        $userservice = SemanticScuttle_Service_Factory::get('User');
        $user = $userservice->getCurrentUser();

        //user has to be either admin, or owner
        if ($GLOBALS['adminsCanModifyBookmarksFromOtherUsers']
            && $userservice->isAdmin($user)
        ) {
            return true;
        } else {
            return ($bookmark['uId'] == $user['uId']);
        }
    }



    /**
     * Checks if a bookmark for the given URL exists
     * already
     *
     * @param string  $address URL of bookmark to check
     * @param integer $uid     User id the bookmark has to belong to.
     *                         null for all users
     *
     * @return boolean True when the bookmark with the given URL
     *                 exists for the user, false if not.
     */
    function bookmarkExists($address = false, $uid = null)
    {
        if (!$address) {
            return false;
        }

        $address = $this->normalize($address);

        $crit = array('bHash' => md5($address));
        if (isset ($uid)) {
            $crit['uId'] = $uid;
        }

        $sql = 'SELECT COUNT(*) FROM '. $GLOBALS['tableprefix'] .'bookmarks WHERE '. $this->db->sql_build_array('SELECT', $crit);
        if (!($dbresult = & $this->db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not get vars', '', __LINE__, __FILE__, $sql, $this->db);
        }
        if($this->db->sql_fetchfield(0, 0) > 0) {
            $output = true;
        } else {
            $output = false;
        }
        $this->db->sql_freeresult($dbresult);
        return $output;
    }



    /**
     * Adds a bookmark to the database.
     *
     * @param string  $address     Full URL of the bookmark
     * @param string  $title       Bookmark title
     * @param string  $description Long bookmark description
     * @param string  $privateNote Private note for the user.
     * @param string  $status      Bookmark visibility:
     *                             0 - public
     *                             1 - shared
     *                             2 - private
     * @param array   $tags        Array of tags
     * @param string  $date        Date when the bookmark has been created
     *                             originally. Used in combination with
     *                             $fromImport. Has to be a strtotime()
     *                             interpretable string.
     * @param boolean $fromApi     True when api call is responsible.
     * @param boolean $fromImport  True when the bookmark is from an import.
     * @param integer $sId         ID of user who creates the bookmark.
     *
     * @return integer Bookmark ID
     */
    public function addBookmark(
        $address, $title, $description, $privateNote, $status, $tags,
        $date = null, $fromApi = false, $fromImport = false, $sId = null
    ) {
        if ($sId === null) {
            $userservice = SemanticScuttle_Service_Factory::get('User');
            $sId = $userservice->getCurrentUserId();
        }

        $address = $this->normalize($address);

        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else if (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }

        /*
         * Note that if date is NULL, then it's added with a date and
         * time of now, and if it's present,
         * it's expected to be a string that's interpretable by strtotime().
         */
        if (is_null($date) || $date == '') {
            $time = time();
        } else {
            $time = strtotime($date);
        }
        $datetime = gmdate('Y-m-d H:i:s', $time);

        // Set up the SQL insert statement and execute it.
        $values = array(
            'uId' => intval($sId),
            'bIp' => $ip,
            'bDatetime' => $datetime,
            'bModified' => $datetime,
            'bTitle' => $title,
            'bAddress' => $address,
            'bDescription' => $description,
            'bPrivateNote' => $privateNote,
            'bStatus' => intval($status),
            'bHash' => md5($address)
        );

        $sql = 'INSERT INTO '. $this->getTableName()
            .' ' . $this->db->sql_build_array('INSERT', $values);
        $this->db->sql_transaction('begin');

        if (!($dbresult = $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR,
                'Could not insert bookmark',
                '', __LINE__, __FILE__, $sql, $this->db
            );
        }

        // Get the resultant row ID for the bookmark.
        $bId = $this->db->sql_nextid($dbresult);
        if (!isset($bId) || !is_int($bId)) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR,
                'Could not insert bookmark',
                '', __LINE__, __FILE__, $sql, $this->db
            );
        }

        $uriparts  = explode('.', $address);
        $extension = end($uriparts);
        unset($uriparts);

        $b2tservice = SemanticScuttle_Service_Factory::get('Bookmark2Tag');
        $attachok   = $b2tservice->attachTags(
            $bId, $tags, $fromApi, $extension, false, $fromImport
        );
        if (!$attachok) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR,
                'Could not insert bookmark',
                '', __LINE__, __FILE__, $sql, $this->db
            );
        }
        $this->db->sql_transaction('commit');

        // Everything worked out, so return the new bookmark's bId.
        return $bId;
    }//public function addBookmark(..)



    /**
     * Update an existing bookmark.
     *
     * @param integer $bId         Bookmark ID
     * @param string  $address     Full URL of the bookmark
     * @param string  $title       Bookmark title
     * @param string  $description Long bookmark description
     * @param string  $privateNote Private note for the user.
     * @param string  $status      Bookmark visibility:
     *                             0 - public
     *                             1 - shared
     *                             2 - private
     * @param array   $categories  Array of tags
     * @param string  $date        Date when the bookmark has been created
     *                             originally. Used in combination with
     *                             $fromImport. Has to be a strtotime()
     *                             interpretable string.
     * @param boolean $fromApi     True when api call is responsible.
     *
     * @return boolean True if all went well, false if not.
     */
    public function updateBookmark(
        $bId, $address, $title, $description, $privateNote, $status,
        $categories, $date = NULL, $fromApi = false
    ) {
        if (!is_numeric($bId)) {
            return false;
        }

        // Get the client's IP address and the date; note that the date is in GMT.
        if (getenv('HTTP_CLIENT_IP'))
        $ip = getenv('HTTP_CLIENT_IP');
        else
        if (getenv('REMOTE_ADDR'))
        $ip = getenv('REMOTE_ADDR');
        else
        $ip = getenv('HTTP_X_FORWARDED_FOR');

        $moddatetime = gmdate('Y-m-d H:i:s', time());

        $address = $this->normalize($address);

        //check if a new address ($address) doesn't already exist for another bookmark from the same user
        $bookmark = $this->getBookmark($bId);
        if($bookmark['bAddress'] != $address && $this->bookmarkExists($address, $bookmark['uId'])) {
            message_die(GENERAL_ERROR, 'Could not update bookmark (URL already existing = '.$address.')', '', __LINE__, __FILE__);
            return false;
        }

        // Set up the SQL update statement and execute it.
        $updates = array('bModified' => $moddatetime, 'bTitle' => $title, 'bAddress' => $address, 'bDescription' => $description, 'bPrivateNote' => $privateNote, 'bStatus' => $status, 'bHash' => md5($address));

        if (!is_null($date)) {
            $datetime = gmdate('Y-m-d H:i:s', strtotime($date));
            $updates[] = array('bDateTime' => $datetime);
        }

        $sql = 'UPDATE '. $GLOBALS['tableprefix'] .'bookmarks SET '. $this->db->sql_build_array('UPDATE', $updates) .' WHERE bId = '. intval($bId);
        $this->db->sql_transaction('begin');

        if (!($dbresult = & $this->db->sql_query($sql))) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not update bookmark', '', __LINE__, __FILE__, $sql, $this->db);
        }

        $uriparts = explode('.', $address);
        $extension = end($uriparts);
        unset($uriparts);

        $b2tservice = SemanticScuttle_Service_Factory :: get('Bookmark2Tag');
        if (!$b2tservice->attachTags($bId, $categories, $fromApi, $extension)) {
            $this->db->sql_transaction('rollback');
            message_die(GENERAL_ERROR, 'Could not update bookmark', '', __LINE__, __FILE__, $sql, $this->db);
        }

        $this->db->sql_transaction('commit');
        // Everything worked out, so return true.
        return true;
    }



    /**
     * Only get the bookmarks that are visible to the current user.
     * Our rules:
     * - if the $user is NULL, that means get bookmarks from ALL users,
     *   so we need to make sure to check the logged-in user's
     *   watchlist and get the contacts-only bookmarks from
     *   those users.
     *   If the user isn't logged-in, just get the public bookmarks.
     *
     * - if the $user is set and isn't the logged-in user, then get
     *   that user's bookmarks, and if that user is on the logged-in
     *   user's watchlist, get the public AND contacts-only
     *   bookmarks; otherwise, just get the public bookmarks.
     *
     * - if the $user is set and IS the logged-in user, then
     *   get all bookmarks.
     *
     * In case voting is enabled and a user is logged in,
     *  each bookmark array contains two additional keys:
     * 'hasVoted' and 'vote'.
     *
     * @param integer $start     Page number
     * @param integer $perpage   Number of bookmarks per page
     * @param integer $user      User ID
     * @param mixed   $tags      Array of tags or tags separated
     *                           by "+" signs
     * @param string  $terms     Search terms separated by spaces
     * @param string  $sortOrder One of the following values:
     *                           "date_asc", "date_desc",
     *                           "title_desc", "title_asc",
     *                           "url_desc", "url_asc",
     *                           "voting_asc", "voting_desc"
     * @param boolean $watched   True if only watched bookmarks
     *                           shall be returned (FIXME)
     * @param integer $startdate Filter for creation date.
     *                           SQL-DateTime value
     *                           "YYYY-MM-DD hh:ii:ss'
     * @param integer $enddate   Filter for creation date.
     *                           SQL-DateTime value
     *                           "YYYY-MM-DD hh:ii:ss'
     * @param string  $hash      Filter by URL hash
     *
     * @return array Array with two keys: 'bookmarks' and 'total'.
     *               First contains an array of bookmarks, 'total'
     *               the total number of bookmarks (without paging).
     */
    public function getBookmarks(
        $start = 0, $perpage = null, $user = null, $tags = null,
        $terms = null, $sortOrder = null, $watched = null,
        $startdate = null, $enddate = null, $hash = null
    ) {
        $userservice    = SemanticScuttle_Service_Factory::get('User');
        $b2tservice     = SemanticScuttle_Service_Factory::get('Bookmark2Tag');
        $tag2tagservice = SemanticScuttle_Service_Factory::get('Tag2Tag');
        $sId            = $userservice->getCurrentUserId();

        if ($userservice->isLoggedOn()) {
            // All public bookmarks, user's own bookmarks
            // and any shared with user
            $privacy = ' AND ((B.bStatus = 0) OR (B.uId = '. $sId .')';
            $watchnames = $userservice->getWatchNames($sId, true);
            foreach ($watchnames as $watchuser) {
                $privacy .= ' OR (U.username = "'. $watchuser .'" AND B.bStatus = 1)';
            }
            $privacy .= ')';
        } else {
            // Just public bookmarks
            $privacy = ' AND B.bStatus = 0';
        }

        // Set up the tags, if need be.
        if (!is_array($tags) && !is_null($tags)) {
            $tags = explode('+', trim($tags));
        }

        $tagcount = count($tags);
        for ($i = 0; $i < $tagcount; $i ++) {
            $tags[$i] = trim($tags[$i]);
        }

        // Set up the SQL query.
        $query_1 = 'SELECT DISTINCT ';
        if (SQL_LAYER == 'mysql4') {
            $query_1 .= 'SQL_CALC_FOUND_ROWS ';
        }
        $query_1 .= 'B.*, U.'. $userservice->getFieldName('username');

        $query_2 = ' FROM '. $userservice->getTableName() .' AS U'
            . ', '. $this->getTableName() .' AS B';

        $query_3 = ' WHERE B.uId = U.'. $userservice->getFieldName('primary') . $privacy;

        if ($GLOBALS['enableVoting'] && $GLOBALS['hideBelowVoting'] !== null
            && !$userservice->isAdmin($userservice->getCurrentUserId())
        ) {
            $query_3 .= ' AND B.bVoting >= ' . (int)$GLOBALS['hideBelowVoting'];
        }

        if (is_null($watched)) {
            if (!is_null($user)) {
                $query_3 .= ' AND B.uId = '. $user;
            }
        } else {
            $arrWatch = $userservice->getWatchlist($user);
            if (count($arrWatch) > 0) {
                $query_3_1 = '';
                foreach($arrWatch as $row) {
                    $query_3_1 .= 'B.uId = '. intval($row) .' OR ';
                }
                $query_3_1 = substr($query_3_1, 0, -3);
            } else {
                $query_3_1 = 'B.uId = -1';
            }
            $query_3 .= ' AND ('. $query_3_1 .') AND B.bStatus IN (0, 1)';
        }

        $query_5 = '';
        if($hash == null) {
            $query_5.= ' GROUP BY B.bHash';
        }


        //Voting system
        //needs to be directly after FROM bookmarks
        if ($GLOBALS['enableVoting'] && $userservice->isLoggedOn()) {
            $cuid = $userservice->getCurrentUserId();
            $vs   = SemanticScuttle_Service_Factory::get('Vote');
            $query_1 .= ', !ISNULL(V.bId) as hasVoted, V.vote as vote';
            $query_2 .= ' LEFT JOIN ' . $vs->getTableName() . ' AS V'
                . ' ON B.bId = V.bId'
                . ' AND V.uId = ' . (int)$cuid;
        }

        switch($sortOrder) {
        case 'date_asc':
            $query_5 .= ' ORDER BY B.bModified ASC ';
            break;
        case 'title_desc':
            $query_5 .= ' ORDER BY B.bTitle DESC ';
            break;
        case 'title_asc':
            $query_5 .= ' ORDER BY B.bTitle ASC ';
            break;
        case 'voting_desc':
            $query_5 .= ' ORDER BY B.bVoting DESC ';
            break;
        case 'voting_asc':
            $query_5 .= ' ORDER BY B.bVoting ASC ';
            break;
        case 'url_desc':
            $query_5 .= ' ORDER BY B.bAddress DESC ';
            break;
        case 'url_asc':
            $query_5 .= ' ORDER BY B.bAddress ASC ';
            break;
        default:
            $query_5 .= ' ORDER BY B.bModified DESC ';
        }

        // Handle the parts of the query that depend on any tags that are present.
        $query_4 = '';
        for ($i = 0; $i < $tagcount; $i ++) {
            $query_2 .= ', '. $b2tservice->getTableName() .' AS T'. $i;
            $query_4 .= ' AND (';

            $allLinkedTags = $tag2tagservice->getAllLinkedTags($this->db->sql_escape($tags[$i]), '>', $user);

            while (is_array($allLinkedTags) && count($allLinkedTags)>0) {
                $query_4 .= ' T'. $i .'.tag = "'. array_pop($allLinkedTags) .'"';
                $query_4 .= ' OR';
            }

            $query_4 .= ' T'. $i .'.tag = "'. $this->db->sql_escape($tags[$i]) .'"';

            $query_4 .= ') AND T'. $i .'.bId = B.bId';
            //die($query_4);
        }

        // Search terms
        if ($terms) {
            // Multiple search terms okay
            $aTerms = explode(' ', $terms);
            $aTerms = array_map('trim', $aTerms);

            // Search terms in tags as well when none given
            if (!count($tags)) {
                $query_2 .= ' LEFT JOIN '. $b2tservice->getTableName() .' AS T ON B.bId = T.bId';
                $dotags = true;
            } else {
                $dotags = false;
            }

            $query_4 = '';
            for ($i = 0; $i < count($aTerms); $i++) {
                $query_4 .= ' AND (B.bTitle LIKE "%'. $this->db->sql_escape($aTerms[$i]) .'%"';
                $query_4 .= ' OR B.bDescription LIKE "%'. $this->db->sql_escape($aTerms[$i]) .'%"';
                $query_4 .= ' OR B.bPrivateNote LIKE "'. $this->db->sql_escape($aTerms[$i]) .'%"'; //warning : search in private notes of everybody but private notes won't appear if not allowed.
                $query_4 .= ' OR U.username = "'. $this->db->sql_escape($aTerms[$i]) .'"'; //exact match for username
                if ($dotags) {
                    $query_4 .= ' OR T.tag LIKE "'. $this->db->sql_escape($aTerms[$i]) .'%"';
                }
                $query_4 .= ')';
            }
        }

        // Start and end dates
        if ($startdate) {
            $query_4 .= ' AND B.bDatetime > "'. $startdate .'"';
        }
        if ($enddate) {
            $query_4 .= ' AND B.bDatetime < "'. $enddate .'"';
        }

        // Hash
        if ($hash) {
            $query_4 .= ' AND B.bHash = "'. $hash .'"';
        }


        $query = $query_1 . $query_2 . $query_3 . $query_4 . $query_5;

        if (!($dbresult = & $this->db->sql_query_limit($query, intval($perpage), intval($start)))) {
            message_die(GENERAL_ERROR, 'Could not get bookmarks', '', __LINE__, __FILE__, $query, $this->db);
        }

        if (SQL_LAYER == 'mysql4') {
            $totalquery = 'SELECT FOUND_ROWS() AS total';
        } else {
            if ($hash) {
                $totalquery = 'SELECT COUNT(*) AS total'. $query_2 . $query_3 . $query_4;
            } else {
                $totalquery = 'SELECT COUNT(DISTINCT bAddress) AS total'. $query_2 . $query_3 . $query_4;
            }
        }

        if (!($totalresult = & $this->db->sql_query($totalquery)) || (!($row = & $this->db->sql_fetchrow($totalresult)))) {
            message_die(GENERAL_ERROR, 'Could not get total bookmarks', '', __LINE__, __FILE__, $totalquery, $this->db);
        }

        $total = $row['total'];
        $this->db->sql_freeresult($totalresult);

        $bookmarks = array();
        while ($row = & $this->db->sql_fetchrow($dbresult)) {
            $row['tags'] = $b2tservice->getTagsForBookmark(intval($row['bId']));
            $bookmarks[] = $row;
        }

        $this->db->sql_freeresult($dbresult);
        $output = array ('bookmarks' => $bookmarks, 'total' => $total);
        return $output;
    }



    /**
     * Delete the bookmark with the given id.
     * Also deletes tags and votes for the given bookmark.
     *
     * @param integer $bookmark Bookmark ID
     *
     * @return boolean True if all went well, false if not
     */
    public function deleteBookmark($bookmark)
    {
        $bookmark = (int)$bookmark;

        $query = 'DELETE FROM ' . $GLOBALS['tableprefix'] . 'bookmarks'
            . ' WHERE bId = '. $bookmark;
        $this->db->sql_transaction('begin');
        if (!($dbres = $this->db->sql_query($query))) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR, 'Could not delete bookmark',
                '', __LINE__, __FILE__, $query, $this->db
            );
        }

        $query = 'DELETE FROM ' . $GLOBALS['tableprefix'] . 'bookmarks2tags'
            . ' WHERE bId = '. $bookmark;
        $this->db->sql_transaction('begin');
        if (!($dbres = $this->db->sql_query($query))) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR, 'Could not delete tags for bookmark',
                '', __LINE__, __FILE__, $query, $this->db
            );
        }

        $query = 'DELETE FROM '. $GLOBALS['tableprefix'] .'votes'
            . ' WHERE bid = '. $bookmark;
        $this->db->sql_transaction('begin');
        if (!($dbres = $this->db->sql_query($query))) {
            $this->db->sql_transaction('rollback');
            message_die(
                GENERAL_ERROR, 'Could not delete votes for bookmark',
                '', __LINE__, __FILE__, $query, $this->db
            );
        }

        $this->db->sql_transaction('commit');

        return true;
    }



    public function deleteBookmarksForUser($uId)
    {
        $query = 'DELETE FROM '. $GLOBALS['tableprefix'] .'bookmarks WHERE uId = '. intval($uId);

        if (!($dbresult = & $this->db->sql_query($query))) {
            message_die(GENERAL_ERROR, 'Could not delete bookmarks', '', __LINE__, __FILE__, $query, $this->db);
        }

        return true;
    }



    function countOthers($address)
    {
        if (!$address) {
            return false;
        }

        $userservice = SemanticScuttle_Service_Factory :: get('User');
        $sId = $userservice->getCurrentUserId();

        if ($userservice->isLoggedOn()) {
            // All public bookmarks, user's own bookmarks and any shared with user
            $privacy = ' AND ((B.bStatus = 0) OR (B.uId = '. $sId .')';
            $watchnames = $userservice->getWatchNames($sId, true);
            foreach($watchnames as $watchuser) {
                $privacy .= ' OR (U.username = "'. $watchuser .'" AND B.bStatus = 1)';
            }
            $privacy .= ')';
        } else {
            // Just public bookmarks
            $privacy = ' AND B.bStatus = 0';
        }

        $sql = 'SELECT COUNT(*) FROM '. $userservice->getTableName() .' AS U, '. $GLOBALS['tableprefix'] .'bookmarks AS B WHERE U.'. $userservice->getFieldName('primary') .' = B.uId AND B.bHash = "'. md5($address) .'"'. $privacy;
        if (!($dbresult = & $this->db->sql_query($sql))) {
            message_die(GENERAL_ERROR, 'Could not get vars', '', __LINE__, __FILE__, $sql, $this->db);
        }

        $output = $this->db->sql_fetchfield(0, 0) - 1;
        $this->db->sql_freeresult($dbresult);
        return $output;
    }



    /**
     * Normalizes a given address.
     * Prepends http:// if there is no protocol specified,
     * and removes the trailing slash
     *
     * @param string $address URL to check
     *
     * @return string Fixed URL
     */
    public function normalize($address)
    {
        //you know, there is "callto:" and "mailto:"
        if (strpos($address, ':') === false) {
            $address = 'http://'. $address;
        }

        // Delete final /
        if (substr($address, -1) == '/') {
            $address = substr($address, 0, count($address)-2);
        }

        return $address;
    }



    /**
     * Delete all bookmarks.
     * Mainly used in unit tests.
     *
     * @return void
     */
    public function deleteAll()
    {
        $query = 'TRUNCATE TABLE `'. $this->getTableName() .'`';
        $this->db->sql_query($query);
    }

}

?>
