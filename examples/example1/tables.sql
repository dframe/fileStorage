-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Czas generowania: 04 Cze 2017, 15:50
-- Wersja serwera: 5.7.16
-- Wersja PHP: 7.0.13-1~dotdeb+8.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `files`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `files`
--

CREATE TABLE `files` (
  `file_id` int(11) NOT NULL,
  `file_adapter` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_mime` varchar(127) NOT NULL,
  `file_metadata` varchar(5000) NOT NULL,
  `last_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `files_cache`
--

CREATE TABLE `file_cache` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `file_cache_path` varchar(255) NOT NULL,
  `file_cache_mime` varchar(127) NOT NULL,
  `file_cache_metadata` varchar(5000) NOT NULL,
  `last_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD UNIQUE KEY `file_path` (`file_path`),
  ADD UNIQUE KEY `file_adapter` (`file_adapter`,`file_path`,`file_mime`),
  ADD KEY `id` (`file_id`);

--
-- Indexes for table `files_cache`
--
ALTER TABLE `files_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_cache_path` (`file_cache_path`,`file_cache_mime`),
  ADD KEY `file_id` (`file_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT dla tabeli `files`
--
ALTER TABLE `files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT dla tabeli `files_cache`
--
ALTER TABLE `file_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
