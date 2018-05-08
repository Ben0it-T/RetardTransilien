-- SQL Dump


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `transilien`
--

-- --------------------------------------------------------

--
-- Structure de la table `transilien_agency`
--

CREATE TABLE `transilien_agency` (
  `agency_id` varchar(20) NOT NULL,
  `agency_name` varchar(40) NOT NULL,
  `agency_url` varchar(0) DEFAULT NULL,
  `agency_timezone` varchar(0) DEFAULT NULL,
  `agency_lang` varchar(0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `transilien_calendar`
--

CREATE TABLE `transilien_calendar` (
  `service_id` int(4) NOT NULL,
  `monday` int(1) NOT NULL,
  `tuesday` int(1) NOT NULL,
  `wednesday` int(1) NOT NULL,
  `thursday` int(1) NOT NULL,
  `friday` int(1) NOT NULL,
  `saturday` int(1) NOT NULL,
  `sunday` int(1) NOT NULL,
  `start_date` int(4) NOT NULL,
  `end_date` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `transilien_calendar_dates`
--

CREATE TABLE `transilien_calendar_dates` (
  `service_id` int(4) NOT NULL,
  `date` int(4) NOT NULL,
  `exception_type` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `transilien_data`
--

CREATE TABLE `transilien_data` (
  `tripid` varchar(40) NOT NULL,
  `date` date NOT NULL,
  `serviceid` varchar(10) NOT NULL,
  `headsign` varchar(10) NOT NULL,
  `routeid` varchar(15) NOT NULL,
  `departuretime` time NOT NULL,
  `arrivaltime` time NOT NULL,
  `incidenttype` char(1) NOT NULL,
  `delay` text NOT NULL,
  `median` float(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `transilien_ongoing`
--

CREATE TABLE `transilien_ongoing` (
  `varname` varchar(250) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `transilien_ongoing`
--

INSERT INTO `transilien_ongoing` (`varname`, `value`) VALUES
('ongoing_cancelled', ''),
('ongoing_data', ''),
('ongoing_delayed', ''),
('ongoing_ontime', ''),
('ongoing_status', '1'),
('ongoing_ts', ''),
('ongoing_unknow', '');

-- --------------------------------------------------------

--
-- Structure de la table `transilien_param`
--

CREATE TABLE `transilien_param` (
  `varname` varchar(250) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `transilien_param`
--

INSERT INTO `transilien_param` (`varname`, `value`) VALUES
('app_status', '1'),
('db_update', '2018-01-01');

-- --------------------------------------------------------

--
-- Structure de la table `transilien_routes`
--

CREATE TABLE `transilien_routes` (
  `route_id` varchar(15) NOT NULL,
  `agency_id` varchar(10) NOT NULL,
  `route_short_name` varchar(50) NOT NULL,
  `route_long_name` varchar(100) NOT NULL,
  `route_desc` varchar(0) DEFAULT NULL,
  `route_type` int(4) NOT NULL,
  `route_url` varchar(0) DEFAULT NULL,
  `route_color` varchar(6) DEFAULT NULL,
  `route_text_color` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `transilien_stops`
--

CREATE TABLE `transilien_stops` (
  `stop_id` varchar(30) NOT NULL,
  `stop_name` varchar(150) NOT NULL,
  `stop_desc` varchar(0) DEFAULT NULL,
  `stop_lat` float(8,6) NOT NULL,
  `stop_lon` float(8,6) NOT NULL,
  `zone_id` varchar(0) DEFAULT NULL,
  `stop_url` varchar(0) DEFAULT NULL,
  `location_type` int(1) NOT NULL,
  `parent_station` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `transilien_stop_times`
--

CREATE TABLE `transilien_stop_times` (
  `trip_id` varchar(40) NOT NULL,
  `arrival_time` time NOT NULL,
  `departure_time` time NOT NULL,
  `stop_id` varchar(30) NOT NULL,
  `stop_sequence` int(1) NOT NULL,
  `stop_headsign` varchar(0) DEFAULT NULL,
  `pickup_type` int(1) NOT NULL,
  `drop_off_type` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `transilien_trips`
--

CREATE TABLE `transilien_trips` (
  `route_id` varchar(15) NOT NULL,
  `service_id` int(4) NOT NULL,
  `trip_id` varchar(40) NOT NULL,
  `trip_headsign` varchar(10) NOT NULL,
  `direction_id` int(4) NOT NULL,
  `block_id` varchar(0) DEFAULT NULL,
  `shape_id` varchar(0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `transilien_uic`
--

CREATE TABLE `transilien_uic` (
  `uic` char(8) NOT NULL,
  `tr3` char(3) NOT NULL,
  `name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;





-- --------------------------------------------------------

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `transilien_agency`
--
ALTER TABLE `transilien_agency`
  ADD PRIMARY KEY (`agency_id`);

--
-- Index pour la table `transilien_calendar`
--
ALTER TABLE `transilien_calendar`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `start_date` (`start_date`),
  ADD KEY `end_date` (`end_date`);

--
-- Index pour la table `transilien_calendar_dates`
--
ALTER TABLE `transilien_calendar_dates`
  ADD PRIMARY KEY (`service_id`,`date`),
  ADD KEY `date` (`date`);

--
-- Index pour la table `transilien_data`
--
ALTER TABLE `transilien_data`
  ADD UNIQUE KEY `trip_id-date` (`tripid`,`date`),
  ADD KEY `date` (`date`);

--
-- Index pour la table `transilien_ongoing`
--
ALTER TABLE `transilien_ongoing`
  ADD PRIMARY KEY (`varname`);

--
-- Index pour la table `transilien_param`
--
ALTER TABLE `transilien_param`
  ADD PRIMARY KEY (`varname`);

--
-- Index pour la table `transilien_routes`
--
ALTER TABLE `transilien_routes`
  ADD PRIMARY KEY (`route_id`),
  ADD KEY `agency_id` (`agency_id`),
  ADD KEY `route_type` (`route_type`);

--
-- Index pour la table `transilien_stops`
--
ALTER TABLE `transilien_stops`
  ADD PRIMARY KEY (`stop_id`);

--
-- Index pour la table `transilien_stop_times`
--
ALTER TABLE `transilien_stop_times`
  ADD PRIMARY KEY (`trip_id`,`stop_id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `stop_id` (`stop_id`),
  ADD KEY `departure_time` (`departure_time`);

--
-- Index pour la table `transilien_trips`
--
ALTER TABLE `transilien_trips`
  ADD PRIMARY KEY (`trip_id`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Index pour la table `transilien_uic`
--
ALTER TABLE `transilien_uic`
  ADD PRIMARY KEY (`uic`),
  ADD KEY `tr3` (`tr3`) USING BTREE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
