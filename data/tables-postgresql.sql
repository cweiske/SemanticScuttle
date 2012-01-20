-- Semantic Scuttle - Tables creation SQL script
-- ! Dont forget to change table names according to $tableprefix defined in config.php !

--
-- Table structure for table "sc_bookmarks"
--

CREATE SEQUENCE bIds
  INCREMENT BY 1
  NO MAXVALUE
  NO MINVALUE
  CACHE 1;

CREATE TABLE sc_bookmarks (
  bId integer DEFAULT nextval('bIds'::text) PRIMARY KEY,
  uId integer NOT NULL,
  bIp varchar(40) DEFAULT NULL,
  bStatus smallint NOT NULL,
  bDatetime timestamp with time zone DEFAULT now() NOT NULL,
  bModified timestamp with time zone DEFAULT now() NOT NULL,
  bTitle varchar(255) DEFAULT '' NOT NULL,
  bAddress varchar(1500) DEFAULT '' NOT NULL,
  bDescription text,
  bPrivateNote text,
  bHash varchar(32) DEFAULT '' NOT NULL,
  bVotes integer NOT NULL,
  bVoting integer NOT NULL,
  bShort varchar(16) DEFAULT NULL
);

CREATE INDEX sc_bookmarks_usd ON sc_bookmarks (uId, bStatus, bDatetime);
CREATE INDEX sc_bookmarks_hui ON sc_bookmarks (bHash, uId, bId);
CREATE INDEX sc_bookmarks_du ON sc_bookmarks (bDatetime, uId);

--
-- Table structure for table "sc_bookmarks2tags"
--

CREATE SEQUENCE b2tIds
  INCREMENT BY 1
  NO MAXVALUE
  NO MINVALUE
  CACHE 1;

CREATE TABLE sc_bookmarks2tags (
  id integer DEFAULT nextval('b2tIds'::text) PRIMARY KEY,
  bId integer NOT NULL,
  tag varchar(100) DEFAULT '' NOT NULL
);

CREATE UNIQUE INDEX sc_bookmarks2tags_tag_bId on sc_bookmarks2tags (tag, bId);
CREATE INDEX sc_bookmarks2tags_bId on sc_bookmarks2tags (bId);

--
-- Table structure for table "sc_commondescription"
--

CREATE SEQUENCE cdIds
  INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE sc_commondescription (
  cdId integer DEFAULT nextval('cdIds'::text) PRIMARY KEY,
  uId integer NOT NULL,
  tag varchar(100) DEFAULT '' NOT NULL,
  bHash varchar(32) DEFAULT '' NOT NULL,
  cdTitle varchar(255) DEFAULT '' NOT NULL,
  cdDescription text,
  cdDatetime timestamp with time zone DEFAULT now() NOT NULL
);

CREATE UNIQUE INDEX sc_commondescription_tag_timestamp on sc_commondescription (tag, cdDatetime);
CREATE UNIQUE INDEX sc_commondescription_bookmark_timestamp on sc_commondescription (bHash, cdDatetime);

--
-- Table structure for table "sc_searchhistory"
--

CREATE SEQUENCE shIds
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE sc_searchhistory (
  shId integer DEFAULT nextval('shIds'::text) PRIMARY KEY,
  shTerms varchar(255) NOT NULL DEFAULT '',
  shRange varchar(32) NOT NULL DEFAULT '',
  shDatetime timestamp with time zone DEFAULT now() NOT NULL,
  shNbResults integer NOT NULL,
  uId integer NOT NULL
);

--
-- Table structure for table "sc_tags"
--

CREATE SEQUENCE tIds
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE sc_tags (
  tId integer DEFAULT nextval('tIds'::text) PRIMARY KEY,
  tag varchar(100) NOT NULL DEFAULT '',
  uId integer NOT NULL,
  tDescription text
);

CREATE UNIQUE INDEX sc_tags_tag_uId on sc_tags (tag, uId);

--
-- Table structure for table "sc_tags2tags"
--

CREATE SEQUENCE ttIds
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE sc_tags2tags (
  ttId integer DEFAULT nextval('ttIds'::text) PRIMARY KEY,
  tag1 varchar(100) NOT NULL DEFAULT '',
  tag2 varchar(100) NOT NULL DEFAULT '',
  relationType varchar(32) NOT NULL DEFAULT '',
  uId integer NOT NULL
);

CREATE UNIQUE INDEX sc_tags2tags_tag1_tag2_uId on sc_tags2tags (tag1, tag2, relationType, uId);

--
-- Table structure for table "sc_tagscache"
--

CREATE SEQUENCE tcIds
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE sc_tagscache (
  tcId integer DEFAULT nextval('tcIds'::text) PRIMARY KEY,
  tag1 varchar(100) NOT NULL DEFAULT '',
  tag2 varchar(100) NOT NULL DEFAULT '',
  relationType varchar(32) NOT NULL DEFAULT '',
  uId integer NOT NULL DEFAULT '0'
);

CREATE UNIQUE INDEX sc_tagscache_tag1_tag2_type_uId on sc_tagscache (tag1, tag2, relationType, uId);

--
-- Table structure for table "sc_tagsstats"
--

CREATE SEQUENCE tstIds
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE sc_tagsstats (
  tstId integer DEFAULT nextval('tstIds'::text) PRIMARY KEY,
  tag1 varchar(100) NOT NULL DEFAULT '',
  relationType varchar(32) NOT NULL DEFAULT '',
  uId integer NOT NULL,
  nb integer NOT NULL,
  depth integer NOT NULL,
  nbupdate integer NOT NULL
);

CREATE UNIQUE INDEX sc_tagsstats_tag1_type_uId on sc_tagsstats (tag1, relationType, uId);

--
-- Table structure for table "sc_users"
--

CREATE SEQUENCE uIds
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE sc_users (
  uId integer DEFAULT nextval('uIds'::text) PRIMARY KEY,
  username varchar(25) NOT NULL DEFAULT '',
  password varchar(40) NOT NULL DEFAULT '',
  uDatetime timestamp with time zone DEFAULT now() NOT NULL,
  uModified timestamp with time zone DEFAULT now() NOT NULL,
  name varchar(50) DEFAULT NULL,
  email varchar(50) NOT NULL DEFAULT '',
  homepage varchar(255) DEFAULT NULL,
  uContent text,
  privateKey varchar(33) DEFAULT NULL
);

CREATE UNIQUE INDEX privateKey on sc_users (privateKey);

--
-- Table structure for table "sc_users_sslclientcerts"
--

CREATE SEQUENCE ids
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE sc_users_sslclientcerts (
  id integer DEFAULT nextval('ids'::text) PRIMARY KEY,
  uId integer NOT NULL,
  sslSerial varchar(32) DEFAULT '' NOT NULL,
  sslClientIssuerDn varchar(1024) DEFAULT '' NOT NULL,
  sslName varchar(64) DEFAULT '' NOT NULL,
  sslEmail varchar(64) DEFAULT '' NOT NULL
);

--
-- Table structure for table "sc_version"
--

CREATE TABLE sc_version (
  schema_version integer NOT NULL
);

--
-- Table structure for table "sc_votes"
--

CREATE TABLE sc_votes (
  bId integer NOT NULL,
  uId integer NOT NULL,
  vote integer NOT NULL
);

CREATE UNIQUE INDEX bid_2 on sc_votes (bId, uId);
CREATE INDEX bid on sc_votes (bId);
CREATE INDEX uid on sc_votes (uId);

--
-- Table structure for table "sc_watched"
--

CREATE SEQUENCE wIds
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE sc_watched (
  wId integer DEFAULT nextval('wIds'::text) PRIMARY KEY,
  uId integer NOT NULL,
  watched integer NOT NULL
);

CREATE INDEX sc_watched_uId on sc_watched (uId);
