-- ****************************************************************************
-- ============================================================================
-- RMCLeads Database Creation Script
-- ============================================================================
-- ****************************************************************************




-- *****************************************************************************
-- SECTION::Admin Users
	CREATE TABLE tblAdminUsers (
		ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		username VARCHAR(20),
		passwordHash VARCHAR(100),
		firstName VARCHAR(50),
		email VARCHAR(50),
		lastName VARCHAR(50),
		imageFilename VARCHAR(100),
		isDeveloper BIT DEFAULT 0,
		roles VARCHAR(20),
		isActive BIT DEFAULT 1,
		lastLoginDate DATETIME,
		loginCount INT DEFAULT 0,
		dateLastHotExport DATETIME
	) ENGINE = INNODB;
GO
	INSERT INTO tblAdminUsers
	       (username, passwordHash, firstName, lastName, email, roles, isDeveloper)
	VALUES ('john', '4d5ffbd59842cf7ee515b6fcdc14fe54f3ab5595',	'John', 'Larson', 'john@jpl-consulting.com', 'AM', 1),
		   ('evan', 'e79a8581c65a2a45e5162f01187db0842c0dbc4c', 'Evan', 'Seehausen', 'evan.seehausen@gmail.com', 'AM', 1);
GO
	CREATE TABLE tblAdminUserQuickLogins(
		ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		AdminUserID INT,
		loginKey VARCHAR(20),
		remainingUses INT DEFAULT 1,
		dateExpires DATETIME,
		FOREIGN KEY (AdminUserID) REFERENCES tblAdminUsers(ID) ON UPDATE CASCADE ON DELETE CASCADE
	) ENGINE = INNODB;
GO
-- END SECTION::Admin Users
-- *****************************************************************************



-- ****************************************************************************
-- ============================================================================
-- ============================================================================
-- SECTION::Utility Tables

	CREATE TABLE tblAddresses (
		ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		toName VARCHAR(100),
		address1 VARCHAR(100),
		address2 VARCHAR(100),
		city VARCHAR(100),
		state VARCHAR(2),
		ZIP VARCHAR(10),
		reducedForm VARCHAR(200),
		CountryID INT,
		isLocated BIT DEFAULT 0,
		latitude FLOAT,
		longitude FLOAT,
		dateAdded DATETIME
	) ENGINE = INNODB;
GO
CREATE TRIGGER tblAddressesOnInsert BEFORE INSERT ON `tblAddresses`
	FOR EACH ROW SET NEW.dateAdded = IFNULL(NEW.dateAdded, NOW());
GO


	CREATE TABLE tblApplicationTaskLocks (
		ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		name VARCHAR(100),
		dateLocked DATETIME,
		dateToUnlock DATETIME
	) ENGINE = INNODB;
GO


	CREATE TABLE tblErrorLogs (
		ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		what VARCHAR(100),
		dayOf DATETIME,
		instances INT DEFAULT 1
	) ENGINE = INNODB;
GO


	CREATE TABLE tblSQLQueries (
               ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
               logGroup INT,
               rQ VARCHAR(500),
               codeFrom VARCHAR(100),
               runtime DECIMAL(9, 6),
               dateAdded DATETIME
       );
GO
	CREATE TRIGGER tblSQLQueriesOnInsert BEFORE INSERT ON `tblSQLQueries`
       FOR EACH ROW SET NEW.dateAdded = IFNULL(NEW.dateAdded, NOW());
GO
 
-- END SECTION::Utility Tables
-- ============================================================================
-- ****************************************************************************



	
	
-- ****************************************************************************
-- ============================================================================
-- ============================================================================
-- SECTION::Leads
CREATE TABLE tblLeads (
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	searchName VARCHAR(100),
	searchGeo VARCHAR(100), -- optional term to append to the search query
	site VARCHAR(200),
	contactName VARCHAR(50),
	contactPhone VARCHAR(50),
	contactEmail VARCHAR(50),
	location VARCHAR(200),
	isActive BIT DEFAULT 1, -- actively monitoring?
	isHot BIT DEFAULT 0, -- have search results indicated this is worth reaching out to?
	isAlerted BIT DEFAULT 0, -- have we alerted interested parties that this lead is hot?
	dateHot DATETIME, -- when this became interesting
	isExported BIT DEFAULT 0, -- sent off to SalesForce?
	isRejected BIT DEFAULT 0, -- deemed uninteresting, keep for duplicate prevention
	notes TEXT,
	monitorPeriod INT DEFAULT 7,
	monitorPages INT DEFAULT 1,
	flagThreshold INT DEFAULT 1,
	monitorSEMrush BIT DEFAULT 0,
	monitorPriority INT DEFAULT 0,
	SEMrushAdBudget INT,
	nextScanDate DATETIME,
	AdminUserID INT, -- the person who owns the lead
	dateAdded DATETIME,
	FOREIGN KEY (AdminUserID) REFERENCES tblAdminUsers(ID) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE = INNODB;
GO
CREATE TRIGGER tblLeadsOnInsert BEFORE INSERT ON `tblLeads`
	FOR EACH ROW SET NEW.dateAdded = IFNULL(NEW.dateAdded, NOW()),
					 NEW.nextScanDate = IFNULL(NEW.nextScanDate, NOW());
GO

CREATE TABLE tblReputationSites (
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	site VARCHAR(200),
	scoreFunction VARCHAR(50) -- callback to compute Lead reputation score for appearing on this site
);
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('yelp.com', '5star');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('tripadvisor.com', '5star');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('yellowpages.com', '5star');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('urbanspoon.com', 'percentage');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('local.yahoo.com', '5starScrape');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('ripoffreport.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('complaintsboard.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('pissedconsumer.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('scam.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('consumeraffairs.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('800notes.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('mugshot.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('healthgrades.com', '5star');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('ratemds.com', '5starScrape');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('ucomparehealthcare.com', '5starScrape');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('vitals.com', '4starScrape');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('gawker.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('huffingtonpost.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('thedirty.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('jezebel.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('topix.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('tmz.com', 'immediateFlag');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('citysearch.com', 'percentageScrape');
GO
INSERT INTO tblReputationSites(site, scoreFunction) VALUES('bbb.org', 'gradeScrape');
GO

CREATE TABLE tblReputationAPIs (
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(200),
	scoreFunction VARCHAR(50),
	baseURL VARCHAR(100),
	dailyRequestLimit INT,
	todayRequestCount INT DEFAULT 0,
	countDay INT DEFAULT 0
) ENGINE = INNODB;
GO
INSERT INTO tblReputationAPIs(name, scoreFunction, baseURL, dailyRequestLimit) VALUES('googlePlaces', '5star', 'https://maps.googleapis.com/maps/api/place/', 100000);
GO

CREATE TABLE tblSearchResults (
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	LeadID INT,
	title VARCHAR(200),
	URL VARCHAR(200),
	blurb VARCHAR(500),
	manualScore INT, -- human flagging: +1, 0, or -1
	dateAdded DATETIME,
	FOREIGN KEY (LeadID) REFERENCES tblLeads(ID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;
GO
CREATE TRIGGER tblSearchResultsOnInsert BEFORE INSERT ON `tblSearchResults`
	FOR EACH ROW SET NEW.dateAdded = IFNULL(NEW.dateAdded, NOW());
GO


CREATE TABLE tblLeadScans (
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	LeadID INT,
	flagCount INT,
	pagesScanned INT,
	runTime DECIMAL(6, 3),
	dateAdded DATETIME, -- when the search was done to generate this score
	FOREIGN KEY (LeadID) REFERENCES tblLeads(ID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;
GO
CREATE TRIGGER tblLeadScansOnInsert BEFORE INSERT ON `tblLeadScans`
	FOR EACH ROW SET NEW.dateAdded = IFNULL(NEW.dateAdded, NOW());
GO

CREATE TABLE tblLeadScanResults (
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	LeadScanID INT,
	SearchResultID INT,
	position INT, -- which position did this monitor result appear at this monitoring time?
	rating DECIMAL(8, 2), -- site-specific rating as extracted from SERP or site scrape
	score INT, -- computed rating: +1, 0, or -1 for good, neutral or bad
	FOREIGN KEY (LeadScanID) REFERENCES tblLeadScans(ID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;
GO



-- END SECTION::Leads
-- ============================================================================
-- ****************************************************************************




-- ****************************************************************************
-- ============================================================================
-- ============================================================================
-- SECTION::SEMrush Reports
CREATE TABLE tblSEMrushReports(
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	LeadID INT, -- null if for a tool report
	dateOf DATETIME,
	keywordLimit INT,
	traffic INT,
	keywords INT,
	dateAdded DATETIME,
	FOREIGN KEY (LeadID) REFERENCES tblLeads(ID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;
GO
CREATE TRIGGER tblSEMrushReportsOnInsert BEFORE INSERT ON `tblSEMrushReports`
	FOR EACH ROW SET NEW.dateAdded = IFNULL(NEW.dateAdded, NOW());
GO

CREATE TABLE tblSEMrushKeywords(
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	SEMrushReportID INT,
	keyword VARCHAR(200),
	position INT,
	traffic DECIMAL(5, 3),
	volume INT,
	FOREIGN KEY (SEMrushReportID) REFERENCES tblSEMrushReports(ID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;
GO
-- END SECTION::SEMrush Reports
-- ============================================================================
-- ****************************************************************************




-- ****************************************************************************
-- ============================================================================
-- ============================================================================
-- SECTION::Proxy IPs:
CREATE TABLE tblProxyIPs (
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	address VARCHAR(30) UNIQUE, -- e.g. "173.234.249.149:8800"
	dateAdded DATETIME
) ENGINE = INNODB;
GO
CREATE TRIGGER tblProxyIPsOnInsert BEFORE INSERT ON `tblProxyIPs`
	FOR EACH ROW SET NEW.dateAdded = IFNULL(NEW.dateAdded, NOW());
GO

CREATE TABLE tblProxyIPSites (
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	site VARCHAR(200),
	ProxyIPID INT,
	status CHAR(1) DEFAULT 'A', -- ' [A]vailable, currently in [U]se, [T]hrottled, [D]efunct
	dateNextAvailable DATETIME,
	dateLastUsed DATETIME,
	dateLastThrottled DATETIME,
	throttleCount INT DEFAULT 0,
	throttleStreak INT DEFAULT 0,
	lastThrottledLeadID INT,
	isActive BIT DEFAULT 1,
	dateAdded DATETIME,
	FOREIGN KEY (ProxyIPID) REFERENCES tblProxyIPs(ID) ON UPDATE CASCADE ON DELETE CASCADE,
	FOREIGN KEY (lastThrottledLeadID) REFERENCES tblLeads(ID) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE = INNODB;
GO
CREATE TRIGGER tblProxyIPSitesOnInsert BEFORE INSERT ON `tblProxyIPSites`
	FOR EACH ROW SET NEW.dateAdded = IFNULL(NEW.dateAdded, NOW()),
				     NEW.dateNextAvailable = IFNULL(NEW.dateNextAvailable, NOW());
GO

-- END SECTION::Proxy IPs
-- ============================================================================
-- ****************************************************************************




-- ****************************************************************************
-- ============================================================================
-- ============================================================================
-- SECTION::Ripoff Reports
CREATE TABLE tblRipoffReports (
	ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	LeadID INT,
	URL VARCHAR(300),
	number INT,
	dateSubmitted DATETIME,
	dateUpdated DATETIME,
	reporterName VARCHAR(50),
	reporterLocation VARCHAR(50),
	title VARCHAR(200),
	content TEXT,
	isResolved BIT DEFAULT 0,
	dateAdded DATETIME,
	FOREIGN KEY (LeadID) REFERENCES tblLeads(ID) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = INNODB;
GO
CREATE TRIGGER tblRipoffReports BEFORE INSERT ON `tblRipoffReports`
	FOR EACH ROW SET NEW.dateAdded = IFNULL(NEW.dateAdded, NOW());
GO
-- END SECTION::Ripoff Reports
-- ============================================================================
-- ****************************************************************************


	
	
-- ****************************************************************************
-- ============================================================================
-- ============================================================================
-- SECTION::Settings:


	CREATE TABLE tblSettingSections (
		ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		name VARCHAR(100),
		sortOrder INT DEFAULT 0,
		isDeveloperOnly BIT DEFAULT 0
	) ENGINE = INNODB;
GO
	INSERT INTO tblSettingSections (name, sortOrder, isDeveloperOnly) VALUES('Settings', 99, 1);
GO
	INSERT INTO tblSettingSections (name, sortOrder, isDeveloperOnly) VALUES('Monitoring', 1, 0);
GO

	CREATE TABLE tblSettings (
		ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		SettingSectionID INT,
		name VARCHAR(100) UNIQUE,
		label VARCHAR(200),
		dataType VARCHAR(1),
		settingType VARCHAR(2),
		sortOrder INT DEFAULT 0,
		value TEXT
	) ENGINE = INNODB;
GO

INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(1, 'lastMonitorRunTime', 'Most Recent Monitor Job Was Run On', 'd', '', 1, '');
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(1, 'lastHotLeadNotificationTime', 'Most recent time hot leads were emailed off', 'd', '', 2, '');
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(1, 'proxyUseDelay', 'Delay (in minutes) between uses of a given proxy', 'f', '', 3, 20);
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(1, 'throttledProxyTimeout', 'Timeout (in minutes) for proxy when throttled', 'f', '', 4, 120);
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(1, 'throttleToDefunctWindow', 'Window (in minutes) between 2 throttles to qualify proxy as defunct', 'i', '', 5, 130);
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(2, 'monitorPeriod', 'Default days between reputation scorings', 'i', '', 1, 7);
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(2, 'monitorPages', 'Default number of result pages to search', 'i', '', 2, 1);
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(2, 'hotLeadFlagThreshold', 'Default number of negative results to trigger lead alert', 'i', '', 4, 1);
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(2, 'leadsToScanPerMinute', 'Number of leads to scan per minute', 'i', '', 3, 1);
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(2, 'hotLeadNotificationEmail', 'Hot Lead Notification Email', 's', '', 5, 'john@jpl-consulting.com');
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(2, 'hotLeadNotificationSendTime', 'Time at which new hot leads should be emailed', 's', '', 6, '8:00am');
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(2, 'scrapeFailureNotificationEmail', 'Scrape Failure Notification Email', 's', '', 7, 'john@jpl-consulting.com');
GO
INSERT INTO tblSettings(SettingSectionID, name, label, dataType, settingType, sortOrder, value)
VALUES(2, 'negativeWordList', 'Negative words or phrases to check for in results (one per line)', 's', 'ta', 10, '');
GO



-- END SECTION::Settings
-- ============================================================================
-- ****************************************************************************

