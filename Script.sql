
DROP TABLE IF EXISTS `sm_encuestas`;
DROP TABLE IF EXISTS `sm_encuestas_excel`;

TRUNCATE TABLE `sm_encuestas`;
TRUNCATE TABLE `sm_encuestas_excel`;


CREATE TABLE `sm_encuestas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_encuesta` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_respuestas` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_colector` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `codigo` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `canal` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT


CREATE TABLE `sm_encuestas_excel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `canal` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `propietario` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT









