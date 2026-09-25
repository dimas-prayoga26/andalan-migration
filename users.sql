-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 23 Sep 2026 pada 16.05
-- Versi server: 8.0.46-cll-lve
-- Versi PHP: 8.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `rnbmanag_production_siap_andalan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_telegram_verified` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `company_id`, `username`, `phone`, `email`, `business_email`, `email_token`, `password_token`, `email_verified_at`, `phone_verified_at`, `last_login_at`, `password`, `is_active`, `remember_token`, `deleted_at`, `created_at`, `updated_at`, `is_telegram_verified`) VALUES
('025DBE37-2ED2-8001-B927-B1E3219F3ECF', '0231B00C-ED68-8254-9CD5-27BC46FC6539', 'Rifka', '083836574377', 'rifkafebriza456@gmail.com', 'rifkafebriza456@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$nnZ4p/K.eiNgHr6tLl5QquP6USyc9YHYykc5.tYZS2rqDmvIe03T6', 1, 'ZdxXKZ44qyJW4BowWFBJJw1cFioFb8MEUQHgGfyV3jawRAtKvNl692rOcctp', NULL, '2026-02-10 02:41:49', '2026-07-05 21:50:38', 0),
('076E9555-67DA-8BDE-ADE2-F4BAEB9E0ACA', 'C0325496-EC01-84DF-99B3-199027BF80ED', 'Rikhardo', '0833333221', 'abirikhardo@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$M55lu2hInUGUFVe6gCfK5OlK59gAx13qZv3hwhr4GROc/p5JyqDoq', 1, 'FUHxLHlzYhn6ODPZKb4THUu5EtKgH58vxz8c5N2mFFpkE6QafTKo7368VDna', NULL, '2026-09-10 10:04:49', '2026-09-10 10:04:49', 0),
('0A69BA99-4D1C-86EE-88DB-F29C65A6E594', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'tiko', '089622666250', 'tikoramadhan47@gmail.com', 'tikoramadhan47@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$0ljcSk78I2CMLbFn3mqMp.j/bLsFK/Eiz37PzvHFRAw2eN4qZvNxu', 0, NULL, NULL, '2024-02-15 03:16:01', '2025-05-27 02:00:42', 0),
('2809878C-82B4-8BA1-9D29-6B755E3CCCBD', '0231B00C-ED68-8254-9CD5-27BC46FC6539', 'Syarif', '082219329645', 'syarifhidayatullah.040203@gmail.com', 'syarifhidayatullah.040203@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$nzMqlQ2JSBLds2Ox.sksS.3JOfyleUVO0wQDdBmqdrT4Q/Imtqmk.', 1, 'KncG6cU1jfNXQLUmR2oH9WVtAYpOFcX8zxgKr2XZ1sN3OFX3beUWSO0WpB3x', NULL, '2025-07-07 02:59:02', '2026-07-05 21:49:47', 0),
('409AF458-5C52-83F1-B22A-7DC1E33946E7', '0F068713-5419-8F49-9EBC-487AE8173711', 'Dedy', '087870537741', 'dedystwn.interior@gmail.com', 'dedystwn.interior@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$pDh/XtEeXanlhLWiDWVRM.soReue9xXtMIUlAtU51lEAE1Px.vy96', 0, 'Wvz0WnskwZLz5xvXsYV0HDsZ9thHuy6oJHNuQcl1lW72jReuiC2AfvoCHjcc', NULL, '2026-06-04 02:00:32', '2026-09-21 01:59:22', 0),
('4A657DAF-1E01-8BA9-BB90-C9C7CED0BFEF', '97A28979-0971-809C-99DF-13418951D399', 'fahmil', '081617551747', 'fahmil@andalanbersama.com', 'fahmil@andalanbersama.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$UaBDOzghObP/Ztdj9fKxCe9bjCcqVeo6Mh4oMt.JxHyk7yms6fA0O', 1, NULL, NULL, '2023-02-23 04:59:21', '2026-07-14 01:27:35', 0),
('4EC4F35C-E048-862A-89F5-3B281A3CFD84', '97A28979-0971-809C-99DF-13418951D399', 'Fian', '083114616272', 'aarissubakti@gmail.com', 'aarissubakti@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$5mg/Gcb4X9ltXs9RnAGSr.3KSohG3K.YQ6hG5SAZg9ySLbj1O/N6a', 0, NULL, NULL, '2026-04-21 04:32:23', '2026-09-02 02:56:27', 0),
('57DF23B1-A7DF-81DD-9682-69092EDEAA4D', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'mevia', '0895379099110', 'diktanamira@gmail.com', 'diktanamira@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$Y1m0GZFnQ9Vx9.F4xXhO6.9rp.ZL2jVQcL3XFgSPdc7lHqFvQNi2O', 1, '27u5jreNnhCU0U6K4rogJwbKXslzS7UhvaTDXfoF7r2MWHolwvurLS8ONzjV', NULL, '2025-04-28 02:52:02', '2025-04-28 02:52:02', 0),
('5AC134B6-2DDA-8FE8-9675-3AC651FF6B2D', '97A28979-0971-809C-99DF-13418951D399', 'arya', '085777275964', 'aryapardomuan@gmail.com', 'aryapardomuan@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$nVM8hAVIp2iCT4CIpPId9eYWuWDnk.qg8deFKLJTNFvZPUjZ3Dc2a', 1, 'UENpFU0wGAac70MC3AE4bKada2jzH7SoSLgkZN1W8CeLAxmVL3jkeujkapKf', NULL, '2023-02-23 06:58:14', '2026-07-05 23:40:54', 0),
('5C631CD3-DC9A-80AC-BD1D-4AB5F12D9A8F', '97A28979-0971-809C-99DF-13418951D399', 'Shiddiq', '085714420450', 'mnshiddiq.01@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$ZuaRPcqMqOEcc27WYVp5pebkxFlkqiUsTKlS75v2cgj..LOWLkawO', 1, 'PCfiG7bIrnQcrcG7O0cVIRO9krwrTBAkIi44sqVUYfzC5N9khtSoexL6zGsd', NULL, '2026-07-06 18:14:35', '2026-08-24 09:51:53', 0),
('5EFE6D35-E751-86D8-ACE3-05DC2C46D32A', 'C0325496-EC01-84DF-99B3-199027BF80ED', 'rosid', '0835555245908', 'rosid@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$O3S61/x5ku9w7nJb37Z6AO2vnjY4QW/b8OSbEv5wdb2xew0uzUuk2', 0, 'qPVrMAKPChyQPaYShDG8FI8AWENxBw0UK73zXtYJX9lDZGcsgGVUYgP8AFwL', NULL, '2026-07-30 08:42:17', '2026-09-02 02:55:40', 0),
('6112E43B-6FE8-8B76-9623-F8AF28D58C51', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'hilmi.ulwan', NULL, 'hilmi.ulwan@andalanbersama.com', 'hilmi.ulwan@andalanbersama.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$fpkzLrwBuVeGk671WY2FS.jes8EYzuFEJH5R8AvAzoFg9W8oKkh1W', 1, NULL, NULL, '2026-07-03 00:38:12', '2026-08-11 06:49:48', 0),
('6A4AB129-4740-8853-B550-FBD2E3286BA7', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'basith', '085540670617', 'wbasithalif@gmail.com', 'wbasithalif@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$3WWuaBQarkcpb8hMD9DudOZWQi44Zo6uth80Ojl4CueAjprYtmeL6', 0, NULL, NULL, '2024-02-15 03:13:32', '2025-05-27 02:00:34', 0),
('6C951925-E929-8963-A778-F1B92E43CB5E', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'lukman', '0811132324', 'lukman@rnbmanagement.com', 'lukman@rnbmanagement.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$84v.o.YxoHi8UPHR9tuNiuy5T2hg7i7HHxY7k6DWM/QytoRRxkhvm', 1, NULL, NULL, '2023-02-23 04:54:27', '2026-07-30 09:31:33', 0),
('8698A7E9-355D-8E52-A3EA-F4E87638825E', '0F068713-5419-8F49-9EBC-487AE8173711', 'andini', '0', 'andin@andalanbersama.com', 'andin@andalanbersama.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$TaYwwGw7Cpda/M5NQHnfRucUBtczIyItyAA5Pwqm/wyiRVhoOOYRq', 0, NULL, NULL, '2023-02-23 06:40:32', '2023-02-23 06:41:51', 0),
('892717C4-8FE3-8B34-AB72-262397E5E20A', '0F068713-5419-8F49-9EBC-487AE8173711', 'reyhanapratama', '081276637530', 'im.reyhanapratama@gmail.com', 'im.reyhanapratama@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$ungoPp6A1g8oN1z0CMvCxOacHWnh7XgmsQF7QDLhe6LADgZp8l.Je', 0, NULL, NULL, '2023-02-23 06:59:42', '2023-05-03 02:26:48', 0),
('97B9F93D-F793-8B90-AA47-E133590727F6', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'wildan', '085740867490', 'wildanharipratama19@gmail.com', 'wildanharipratama19@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$o9RBRne5BhejwulzVICFSuSYvIZmxke1MX6hk/RStgH8HkDcg.hHi', 0, NULL, NULL, '2023-05-24 02:00:47', '2023-11-26 03:07:35', 0),
('A630207E-A834-8F18-ABAF-0E7B2BB45596', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'rully.priyatno', NULL, 'rully.priyatno@andalanbersama.com', 'rully.priyatno@andalanbersama.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$4JNBIUCtyttad3s0WaaJLewCcuWUMLNh/sss8PrzdiLIdQoBPgqte', 0, NULL, NULL, '2026-07-03 00:38:12', '2026-09-16 01:39:20', 0),
('A835C03F-6042-8FBB-B655-734E78507E3F', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'erlin', '085161185010', 'halloerlin@gmail.com', 'halloerlin@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$BkbcLPK5Hua1yeLqjz3VHOgRnFU2us5976x5.eWhUN3YcQEwCsRNC', 1, 'pcjyNc9pA1iVxCaamTwo1gdRyKv799yPDpmucaa8VhnnHUbm1sPPtoGnh2Wd', NULL, '2026-01-21 06:31:56', '2026-09-16 03:27:23', 0),
('A86BA6A2-B08D-8372-8D03-44F1583A49E4', '0231B00C-ED68-8254-9CD5-27BC46FC6539', 'Dimas', '083123555526', 'dimas.prayoga260403@gmail.com', 'dimas.prayoga260403@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$kg1W8srUy94FAR/7zMDXuuF753eol8/2TmwqpLpMVeufI7tMU0dwm', 1, 'RdGClb0jjxgpxJkwT6dNqNcVzik4hcwkRtJlDOuj2MRGxJxBoFwuSP5kx3gy', NULL, '2026-04-20 02:58:11', '2026-07-05 21:51:14', 0),
('AF59059D-C64C-88F9-8526-988429B8AE3C', 'C0325496-EC01-84DF-99B3-199027BF80ED', 'Leonie', '0895361249937', 'leonieputri7@gmail.com', 'leonieputri7@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$/6GxvwGZdexfN38FOyC7DOI7DVcraOpCUwVs.ln/Z4ZBj.aCYqgTq', 1, 'utEpK3eGnsKoYpH0SSZsAAWHz1rwOsgZQP2vP6lZgGiwbcpMS3hnXz1bbpLS', NULL, '2024-06-19 01:49:02', '2026-07-05 23:46:14', 0),
('B2F34634-271F-8BD5-8A7F-260ED410E3BC', '0231B00C-ED68-8254-9CD5-27BC46FC6539', 'testing', '089786546781', 'testing@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Bf5wggaLAtKVg6Bve/IlyuWSgNINs6aj84G9rY1Htt9Af22TtRqq6', 0, NULL, NULL, '2026-07-31 04:25:23', '2026-07-31 04:27:01', 0),
('B3752048-AAF8-8DE0-8DC8-070D6F26DE3C', 'C0325496-EC01-84DF-99B3-199027BF80ED', 'muhammad', '082377410912', 'nasrulaja306@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$Y.PWr/okmS/1HQiewewf9uSIF8cR0GP7S/whfRxpe/Qn81fvLMN/e', 1, 'UQgqAuLXJzpaM9xiXTrJWIfhdT3U0m1PaqNloyFkIPfapbX50M9vSHuwV4un', NULL, '2026-08-31 03:51:35', '2026-09-15 06:59:33', 0),
('B45C33C3-ACEE-893F-8470-F664CF301CC8', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'superadmin', NULL, 'superadmin@andalanbersama.com', 'superadmin@andalanbersama.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$r1S9aZrHzGVeu86L3bXGSe/KkfVHOKw31vyTLTBy2eq84nfeOtDTG', 1, '7X67tim6OPiosqPOvHNUN29NDChAD3JK3xcAkuStWPhpO6YxPC8bJuIYmEhz', NULL, '2026-07-03 00:38:13', '2026-07-03 00:38:13', 0),
('B6777EC9-AA39-87CE-8FA0-F14F545B930C', '97A28979-0971-809C-99DF-13418951D399', 'Aira', '0895343302983', 'airarizqi22@gmail.com', 'airarizqi22@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$YiOM/f4n8JRTT0ACkWYJLOpEQPKyiqez6JDQQcfnMHpFhyueQqMoC', 0, NULL, NULL, '2026-06-02 10:04:37', '2026-07-05 23:43:26', 0),
('CB7E07B6-0AAF-8705-ACB7-27090E624B5E', '0231B00C-ED68-8254-9CD5-27BC46FC6539', 'syafiq', '081218399696', 'msyafiq.dev@gmail.com', 'msyafiq.dev@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$euBw2lfWbDk8iaFaZi1czuHSiybfJcC9nNUa0nePO4j75LzWpMoDG', 1, NULL, NULL, '2023-02-23 06:56:24', '2026-07-05 21:49:04', 0),
('CC5BB54B-D4D7-876E-A834-89E7F4AD84B6', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'fadil', '0999999999999', 'fadil@andalanbersama.com', 'fadil@andalanbersama.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$L8WmcMAwTRyjMJA7S8fKveMYDwi4OdYjTO9Dgo/LGqd1DSz07iY8.', 0, NULL, NULL, '2023-02-23 04:56:24', '2025-05-22 09:07:54', 0),
('DD9AC1FD-9769-8841-AF7A-A65AEE864352', 'A788DCE1-FDB0-83C9-9D8A-5B3FD0C894D9', 'Rafi', '083444426789', 'dimas@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$VyiLT3yuwVxvC393CEUVGOCiMbVwDsWRU1tJYudNH9PivDMnPO1jq', 1, NULL, NULL, '2026-07-30 12:43:34', '2026-08-06 09:06:04', 0),
('DECD37AE-F934-858B-9CC5-03CCA0AB1119', '0F068713-5419-8F49-9EBC-487AE8173711', 'Arum', '082377437201', 'arumkusumawati98@gmail.com', 'arumkusumawati98@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$pBVFW5vnc2x44lli0iAsqe2bI4eUaVaMrULLmwYp7MkjB11UDvPiy', 1, 'Jfu1fj8up7cd0F9TWuFhwYL31dJNJ66YVd1v2ZJMfCwyB4cpM9USsk0TrcWu', NULL, '2026-04-02 02:22:38', '2026-07-05 21:53:04', 0),
('E3148927-7021-8E52-97C7-0A7605F13FD9', '8F7C7D59-FA35-899C-BE1E-C95E4CAA280B', 'fuadfahrudin', '087731122287', 'fuadmfahrudin@gmail.com', 'fuadmfahrudin@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$fyNm4Fu99TUURBL4v71Bm.v1EAoy7qiUOvaRQJqJQCauCKnLKRYx.', 1, '4hKgzxMnGtadrKzFGsZSBf2HscfQwb6SsmBVQik63OEJJB2iwndqwGoO6Pdq', NULL, '2025-05-22 09:20:59', '2026-07-14 01:28:00', 0),
('F71C5EC1-711E-850B-8ADE-995A425CA55C', '0F068713-5419-8F49-9EBC-487AE8173711', 'yudistira', '08997885557', '23twentythreebike@gmail.com', '23twentythreebike@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$MbcsXXWNdczDULxg3qeCKekU6anLa1K8a5CQiqdQJH2l8U5rRLGy2', 0, NULL, NULL, '2023-02-23 07:01:02', '2023-04-04 02:47:40', 0),
('F992F2CB-FD96-8B15-B59D-D048BDD9E093', '97A28979-0971-809C-99DF-13418951D399', 'Ucup', '08986857280', 'abasyamanyusuf1999@gmail.com', 'abasyamanyusuf1999@gmail.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$cjpQxv9p8NTXlh/FlmdAFe4532hC.u45U7ID59C8uJyU329VJApyq', 1, NULL, NULL, '2026-01-23 04:39:10', '2026-07-05 23:41:43', 0),
('FF20B3EE-98D7-8FFD-A5CF-6AF6DF9BF3A2', '0F068713-5419-8F49-9EBC-487AE8173711', 'rexy', '0895359048161', 'rexy@andalanbersama.com', 'rexy@andalanbersama.com', NULL, NULL, NULL, NULL, NULL, '$2y$12$X.0JmK6E9us.Q/uyTydh8.7BMyZsO2oVmzTUnQj0K9yVZfHXE69y6', 1, NULL, NULL, '2023-02-23 06:51:46', '2026-07-14 01:27:01', 0);

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_business_email_unique` (`business_email`),
  ADD KEY `users_company_id_foreign` (`company_id`);

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
