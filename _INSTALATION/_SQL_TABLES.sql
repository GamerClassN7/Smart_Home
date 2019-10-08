-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost:3306
-- Vytvořeno: Úte 08. říj 2019, 18:05
-- Verze serveru: 10.1.41-MariaDB-0+deb9u1
-- Verze PHP: 7.0.33-0+deb9u5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `smart-home`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `automation`
--

CREATE TABLE `automation` (
  `automation_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `on_days` varchar(255) NOT NULL,
  `if_something` varchar(255) NOT NULL,
  `do_something` varchar(255) NOT NULL,
  `executed` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `locked` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `dashboard`
--

CREATE TABLE `dashboard` (
  `dashboard_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subdevice_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `devices`
--

CREATE TABLE `devices` (
  `device_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `sleep_time` int(13) NOT NULL,
  `owner` int(13) NOT NULL,
  `permission` varchar(255) NOT NULL,
  `approved` int(11) NOT NULL,
  `icon` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `notifications`
--

CREATE TABLE `notifications` (
  `id` int(13) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `records`
--

CREATE TABLE `records` (
  `record_id` int(11) NOT NULL,
  `subdevice_id` int(11) NOT NULL,
  `value` smallint(6) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `execuded` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `scenes`
--

CREATE TABLE `scenes` (
  `scene_id` int(11) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `do_something` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `subdevices`
--

CREATE TABLE `subdevices` (
  `subdevice_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `startPage` int(11) NOT NULL,
  `at_home` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `startPage`) VALUES
(2, 'Admin', '08abb3ff83dfae60fb4591125fc49dc80cf7ef28224c2d5df86e2d0d037c553bc7f30e859348fd745c9c07a4edde4863e866a7d45356cf08a22e5e1eafa13406', 1);

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `automation`
--
ALTER TABLE `automation`
  ADD PRIMARY KEY (`automation_id`);

--
-- Klíče pro tabulku `dashboard`
--
ALTER TABLE `dashboard`
  ADD PRIMARY KEY (`dashboard_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subdevice_id` (`subdevice_id`);

--
-- Klíče pro tabulku `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`device_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Klíče pro tabulku `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `records`
--
ALTER TABLE `records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `device_id` (`subdevice_id`);

--
-- Klíče pro tabulku `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Klíče pro tabulku `scenes`
--
ALTER TABLE `scenes`
  ADD PRIMARY KEY (`scene_id`);

--
-- Klíče pro tabulku `subdevices`
--
ALTER TABLE `subdevices`
  ADD PRIMARY KEY (`subdevice_id`),
  ADD KEY `device_id` (`device_id`);

--
-- Klíče pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `automation`
--
ALTER TABLE `automation`
  MODIFY `automation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;
--
-- AUTO_INCREMENT pro tabulku `dashboard`
--
ALTER TABLE `dashboard`
  MODIFY `dashboard_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT pro tabulku `devices`
--
ALTER TABLE `devices`
  MODIFY `device_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT pro tabulku `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(13) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT pro tabulku `records`
--
ALTER TABLE `records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=352338;
--
-- AUTO_INCREMENT pro tabulku `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT pro tabulku `scenes`
--
ALTER TABLE `scenes`
  MODIFY `scene_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT pro tabulku `subdevices`
--
ALTER TABLE `subdevices`
  MODIFY `subdevice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `dashboard`
--
ALTER TABLE `dashboard`
  ADD CONSTRAINT `dashboard_ibfk_2` FOREIGN KEY (`subdevice_id`) REFERENCES `subdevices` (`subdevice_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `dashboard_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `records`
--
ALTER TABLE `records`
  ADD CONSTRAINT `records_ibfk_1` FOREIGN KEY (`subdevice_id`) REFERENCES `subdevices` (`subdevice_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Omezení pro tabulku `subdevices`
--
ALTER TABLE `subdevices`
  ADD CONSTRAINT `subdevices_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`device_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
