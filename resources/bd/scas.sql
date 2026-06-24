/*
 Navicat Premium Data Transfer

 Source Server         : CAS
 Source Server Type    : MariaDB
 Source Server Version : 120302
 Source Host           : localhost:3307
 Source Schema         : scas

 Target Server Type    : MariaDB
 Target Server Version : 120302
 File Encoding         : 65001

 Date: 23/06/2026 16:46:38
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for armas
-- ----------------------------
DROP TABLE IF EXISTS `armas`;
CREATE TABLE `armas`  (
  `id_arma` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion_arma` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `sigla_a` varchar(35) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_arma`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 24 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of armas
-- ----------------------------
INSERT INTO `armas` VALUES (1, 'CABALLERIA', 'CAB.');
INSERT INTO `armas` VALUES (2, 'INFANTERIA', 'INF.');
INSERT INTO `armas` VALUES (3, 'ARTILLERIA', 'ART.');
INSERT INTO `armas` VALUES (4, 'LOGISTICA', 'LOG.');
INSERT INTO `armas` VALUES (5, 'INGENIERIA', 'ING.');
INSERT INTO `armas` VALUES (6, 'DEM', 'DEM');
INSERT INTO `armas` VALUES (7, 'DIPLOM.EST.MAYOR AE.', 'DEMA');
INSERT INTO `armas` VALUES (8, 'COMUNICACIONES', 'COM.');
INSERT INTO `armas` VALUES (9, 'NO REGISTRA', 'N.R.');
INSERT INTO `armas` VALUES (10, 'ABOG.', 'ABOGADA');
INSERT INTO `armas` VALUES (11, 'SERVICIOS', 'SERV');
INSERT INTO `armas` VALUES (12, 'DIM', 'DIM.');
INSERT INTO `armas` VALUES (13, 'EMPLEADO CIVIL', 'E.C.');
INSERT INTO `armas` VALUES (14, 'DAEN.', 'DAEN.');
INSERT INTO `armas` VALUES (15, 'NO IDENTIFICADA', 'N.I.');
INSERT INTO `armas` VALUES (16, 'MUSICA', 'MUS.');
INSERT INTO `armas` VALUES (17, 'TOPOGRAFO', 'TOP.');
INSERT INTO `armas` VALUES (18, 'TECNICO', 'TEC.');
INSERT INTO `armas` VALUES (19, 'INTENDENCIA', 'INT.');
INSERT INTO `armas` VALUES (20, 'MATERIAL BELICO', 'MB');
INSERT INTO `armas` VALUES (21, 'MOTORES', 'MOT');
INSERT INTO `armas` VALUES (22, 'SANIDAD', 'SAN');
INSERT INTO `armas` VALUES (23, 'MECANICA AVIACION', 'MECAV');

-- ----------------------------
-- Table structure for cache
-- ----------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`) USING BTREE,
  INDEX `cache_expiration_index`(`expiration`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cache
-- ----------------------------
INSERT INTO `cache` VALUES ('laravel-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:19:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"dashboard.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"usuarios.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:14:\"usuarios.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"usuarios.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:15:\"usuarios.estado\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:17:\"usuarios.password\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:9:\"roles.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:11:\"roles.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:12:\"roles.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:14:\"roles.usuarios\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"permisos.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:16:\"permisos.asignar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:13:\"socios.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:10:\"socios.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:12:\"socios.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:15:\"socios.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:17:\"socios.revincular\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:18:\"socios.informacion\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:15:\"socios.reportes\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:2:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"Administrador\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:17:\"Operador-Sistemas\";s:1:\"c\";s:3:\"web\";}}}', 1782307041);

-- ----------------------------
-- Table structure for cache_locks
-- ----------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`) USING BTREE,
  INDEX `cache_locks_expiration_index`(`expiration`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cache_locks
-- ----------------------------

-- ----------------------------
-- Table structure for conta_subcuentas
-- ----------------------------
DROP TABLE IF EXISTS `conta_subcuentas`;
CREATE TABLE `conta_subcuentas`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cod_tipo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `estado` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AC',
  `id_socio` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_codigo`(`codigo`) USING BTREE,
  INDEX `idx_id_socio`(`id_socio`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of conta_subcuentas
-- ----------------------------
INSERT INTO `conta_subcuentas` VALUES (1, '99999', 'Droguett Vargas Jose Faisal', 'SOCIO', 'AC', 5, '2026-06-19 14:53:16', '2026-06-19 14:53:16');
INSERT INTO `conta_subcuentas` VALUES (2, '27311', 'CALCINA PEREYRA PEDRO', 'SOCIO', 'AC', 6, '2026-06-19 15:03:34', '2026-06-23 18:55:45');
INSERT INTO `conta_subcuentas` VALUES (3, '909090', 'MARIACA MEALLA LEIDY MABEL', 'SOCIO', 'AC', 7, '2026-06-23 15:31:39', '2026-06-23 17:24:47');
INSERT INTO `conta_subcuentas` VALUES (4, '00024607', 'THE DUCK DISNEY DONALD', 'SOCIO', 'AC', 8, '2026-06-23 15:39:39', '2026-06-23 15:39:39');

-- ----------------------------
-- Table structure for diplomados
-- ----------------------------
DROP TABLE IF EXISTS `diplomados`;
CREATE TABLE `diplomados`  (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `abrev` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of diplomados
-- ----------------------------
INSERT INTO `diplomados` VALUES (1, 'NO REGISTRA', 'NR');
INSERT INTO `diplomados` VALUES (2, 'DIP.ALTOS EST.NAL.', 'DAEN');
INSERT INTO `diplomados` VALUES (3, 'DIP. ESTADO MAYOR', 'DEM.');
INSERT INTO `diplomados` VALUES (4, 'DIP.INGENIERIA MIL.', 'DIM.');
INSERT INTO `diplomados` VALUES (5, 'D.ESC.PERF.SOF.SGTOS', 'DEPSS');
INSERT INTO `diplomados` VALUES (6, 'DIP.ESC.PERF.MUSICA', 'DEPSSM');
INSERT INTO `diplomados` VALUES (7, 'DIPLOM.EST.MAY.AER.', 'DEMA.');
INSERT INTO `diplomados` VALUES (8, 'AUX.ESTADO MAYOR', 'AEM');
INSERT INTO `diplomados` VALUES (9, 'OO.EST.MAYOR ESPEC.', 'OEME');
INSERT INTO `diplomados` VALUES (10, 'DIP.EST.MAYOR ESP.', 'DEME.');

-- ----------------------------
-- Table structure for dominios
-- ----------------------------
DROP TABLE IF EXISTS `dominios`;
CREATE TABLE `dominios`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `dominio` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `abrev` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `Descripcion` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `dominio`(`dominio`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 70 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dominios
-- ----------------------------
INSERT INTO `dominios` VALUES (1, 'MESES', 'ENE', 'ENERO');
INSERT INTO `dominios` VALUES (2, 'MESES', 'FEB', 'FEBRERO');
INSERT INTO `dominios` VALUES (3, 'MESES', 'MAR', 'MARZO');
INSERT INTO `dominios` VALUES (4, 'MESES', 'ABR', 'ABRIL');
INSERT INTO `dominios` VALUES (5, 'MESES', 'MAY', 'MAYO');
INSERT INTO `dominios` VALUES (6, 'MESES', 'JUN', 'JUNIO');
INSERT INTO `dominios` VALUES (7, 'MESES', 'JUL', 'JULIO');
INSERT INTO `dominios` VALUES (8, 'MESES', 'AGO', 'AGOSTO');
INSERT INTO `dominios` VALUES (9, 'MESES', 'SEP', 'SEPTIEMBRE');
INSERT INTO `dominios` VALUES (10, 'MESES', 'OCT', 'OCTUBRE');
INSERT INTO `dominios` VALUES (11, 'MESES', 'NOV', 'NOVIEMBRE');
INSERT INTO `dominios` VALUES (12, 'MESES', 'DIC', 'DICIEMBRE');
INSERT INTO `dominios` VALUES (13, 'DEPARTAMENTOS', 'LP', 'LA PAZ');
INSERT INTO `dominios` VALUES (14, 'DEPARTAMENTOS', 'CB', 'COCHABAMBA');
INSERT INTO `dominios` VALUES (15, 'DEPARTAMENTOS', 'BE', 'BENI');
INSERT INTO `dominios` VALUES (16, 'DEPARTAMENTOS', 'OR', 'ORURO');
INSERT INTO `dominios` VALUES (17, 'DEPARTAMENTOS', 'PA', 'PANDO');
INSERT INTO `dominios` VALUES (18, 'DEPARTAMENTOS', 'PO', 'POTOSI');
INSERT INTO `dominios` VALUES (19, 'DEPARTAMENTOS', 'SC', 'SANTA CRUZ');
INSERT INTO `dominios` VALUES (20, 'DEPARTAMENTOS', 'SU', 'SUCRE');
INSERT INTO `dominios` VALUES (21, 'DEPARTAMENTOS', 'TA', 'TARIJA');
INSERT INTO `dominios` VALUES (22, 'ECIVIL', 'SO', 'SOLTERO/A');
INSERT INTO `dominios` VALUES (23, 'ECIVIL', 'CA', 'CASADO/A');
INSERT INTO `dominios` VALUES (24, 'ECIVIL', 'DI', 'DIVORCIADO/A');
INSERT INTO `dominios` VALUES (25, 'ECIVIL', 'VI', 'VIUDO/A');
INSERT INTO `dominios` VALUES (26, 'ECIVIL', 'CO', 'CONCUBINO/A');
INSERT INTO `dominios` VALUES (27, 'SEXO', 'M', 'MASCULINO');
INSERT INTO `dominios` VALUES (28, 'SEXO', 'F', 'FEMENINO');
INSERT INTO `dominios` VALUES (29, 'PARENTESCO', 'AB', 'ABUELO/A');
INSERT INTO `dominios` VALUES (30, 'PARENTESCO', 'PA', 'PADRE/MADRE');
INSERT INTO `dominios` VALUES (31, 'PARENTESCO', 'HE', 'HERMANO/A');
INSERT INTO `dominios` VALUES (32, 'PARENTESCO', 'HI', 'HIJO/A');
INSERT INTO `dominios` VALUES (33, 'PARENTESCO', 'NI', 'NIETO/A');
INSERT INTO `dominios` VALUES (34, 'PARENTESCO', 'ES', 'ESPOSO/A');
INSERT INTO `dominios` VALUES (35, 'PARENTESCO', 'CO', 'CONYUGUE');
INSERT INTO `dominios` VALUES (36, 'MONEDA', 'U', 'DOLARES');
INSERT INTO `dominios` VALUES (37, 'MONEDA', 'B', 'BOLIVIANOS');
INSERT INTO `dominios` VALUES (38, 'GARANTE', '1', 'SI');
INSERT INTO `dominios` VALUES (39, 'GARANTE', '0', 'NO');
INSERT INTO `dominios` VALUES (40, 'PARENTESCO', 'EN', 'ENTENADO/A');
INSERT INTO `dominios` VALUES (41, 'PARENTESCO', 'SO', 'SOBRINO/NA');
INSERT INTO `dominios` VALUES (42, 'PARENTESCO', 'PR', 'PRIMO/MA');
INSERT INTO `dominios` VALUES (43, 'DEPOSITOS', '010', 'Certificados Obligatorios');
INSERT INTO `dominios` VALUES (44, 'DEPOSITOS', '020', 'Certificados Voluntarios');
INSERT INTO `dominios` VALUES (45, 'DEPOSITOS', '030', 'Certificados Obligatorios/Voluntario');
INSERT INTO `dominios` VALUES (46, 'DEPOSITOS', 'CU', 'Cuotas');
INSERT INTO `dominios` VALUES (47, 'DEPOSITOS', 'AM', 'Amortizacion');
INSERT INTO `dominios` VALUES (48, 'DEPOSITOS', 'PT', 'Pago total');
INSERT INTO `dominios` VALUES (49, 'DEPOSITOS', 'MO', 'Moras');
INSERT INTO `dominios` VALUES (50, 'DEVOLUCIONES', '110', 'Devolucion aporte en proceso');
INSERT INTO `dominios` VALUES (51, 'DEVOLUCIONES', '120', 'Devolucion aporte Completada');
INSERT INTO `dominios` VALUES (52, 'DEVOLUCIONES', '130', 'Devolucion capitalizacion en proceso');
INSERT INTO `dominios` VALUES (53, 'DEVOLUCIONES', '140', 'Devolucion capitalizacion completada');
INSERT INTO `dominios` VALUES (54, 'AREAS', 'CA', 'Cartera');
INSERT INTO `dominios` VALUES (55, 'AREAS', 'CO', 'Contabilidad');
INSERT INTO `dominios` VALUES (56, 'AREAS', 'SI', 'Sistemas');
INSERT INTO `dominios` VALUES (57, 'JURIDICA', 'RD', 'Resolucion de Directorio CAS');
INSERT INTO `dominios` VALUES (58, 'JURIDICA', 'RA', 'Resolucion Administrativa CAS');
INSERT INTO `dominios` VALUES (59, 'JURIDICA', 'AF', 'Resoluciones AFCOOP');
INSERT INTO `dominios` VALUES (60, 'AREAS', 'CC', 'Cartera/Contabilidad');
INSERT INTO `dominios` VALUES (61, 'AREAS', 'CS', 'Cartera/Sistemas');
INSERT INTO `dominios` VALUES (62, 'AREAS', 'CS', 'Contabilidad/Sistemas');
INSERT INTO `dominios` VALUES (63, 'DISTRIBUCION', 'CF', 'Obligaciones con los Asociados');
INSERT INTO `dominios` VALUES (64, 'DISTRIBUCION', 'EP', 'Excedentes de Percepcion');
INSERT INTO `dominios` VALUES (65, 'CERTIFICADOS', 'AO', 'Certificado de Aportacion Obligatorio');
INSERT INTO `dominios` VALUES (66, 'CERTIFICADOS', 'AV', 'Certificado de Aportacion Voluntario');
INSERT INTO `dominios` VALUES (67, 'CERTIFICADOS', 'OV', 'Certificado de Aportacion Obligatorio y Voluntario');
INSERT INTO `dominios` VALUES (68, 'PARENTESCO', 'TI', 'TIO/A');
INSERT INTO `dominios` VALUES (69, 'PARENTESCO', 'NR', 'NO REGISTRA');

-- ----------------------------
-- Table structure for escalafon
-- ----------------------------
DROP TABLE IF EXISTS `escalafon`;
CREATE TABLE `escalafon`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `abrev` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `descripcion`(`descripcion`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 8 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of escalafon
-- ----------------------------
INSERT INTO `escalafon` VALUES (1, 'Of. Armas', NULL);
INSERT INTO `escalafon` VALUES (2, 'Sub. Of. Armas', NULL);
INSERT INTO `escalafon` VALUES (3, 'Of. De Musica', NULL);
INSERT INTO `escalafon` VALUES (4, 'Sub. Sarg De Musica', NULL);
INSERT INTO `escalafon` VALUES (5, 'Of. de Servicio', NULL);
INSERT INTO `escalafon` VALUES (6, 'Sub. Sarg De Servicio', NULL);
INSERT INTO `escalafon` VALUES (7, 'Empleados Civiles', 'EECC');

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for fuerzas
-- ----------------------------
DROP TABLE IF EXISTS `fuerzas`;
CREATE TABLE `fuerzas`  (
  `id_fuerza` int(11) NOT NULL AUTO_INCREMENT,
  `fuerza` varchar(35) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `sigla_f` varchar(35) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_fuerza`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 6 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of fuerzas
-- ----------------------------
INSERT INTO `fuerzas` VALUES (1, 'EJERCITO DE BOLIVIA', 'EJTO');
INSERT INTO `fuerzas` VALUES (2, 'MINISTERIO DEFENSA', 'MINDEFEN');
INSERT INTO `fuerzas` VALUES (3, 'CMDO.FF.AA. NACION', 'COMANJEF');
INSERT INTO `fuerzas` VALUES (4, 'FZA.AEREA BOLIVIANA', 'FZA.AEREA');
INSERT INTO `fuerzas` VALUES (5, 'ARMADA BOLIVIANA', 'FZA.NAVA');

-- ----------------------------
-- Table structure for grados
-- ----------------------------
DROP TABLE IF EXISTS `grados`;
CREATE TABLE `grados`  (
  `id_grado` int(5) NOT NULL AUTO_INCREMENT,
  `grado` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `sigla_g` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_grado`) USING BTREE,
  INDEX `id_grado`(`id_grado`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 68 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of grados
-- ----------------------------
INSERT INTO `grados` VALUES (1, 'GRAL. FZA.', 'GRAL. FZ');
INSERT INTO `grados` VALUES (2, 'ALMTE.', 'ALMTE.');
INSERT INTO `grados` VALUES (3, 'GRAL.DIV.', 'GRAL.DIV');
INSERT INTO `grados` VALUES (4, 'V.ALMTE.', 'V.ALMTE.');
INSERT INTO `grados` VALUES (5, 'GRAL.BRIG.', 'GRAL.BRI');
INSERT INTO `grados` VALUES (6, 'C.ALMTE.', 'C.ALMTE.');
INSERT INTO `grados` VALUES (7, 'CNL.', 'CNL.');
INSERT INTO `grados` VALUES (8, 'C.N.', 'C.N.');
INSERT INTO `grados` VALUES (9, 'TCNL.', 'TCNL.');
INSERT INTO `grados` VALUES (10, 'C.F.', 'C.F.');
INSERT INTO `grados` VALUES (11, 'MY.', 'MY.');
INSERT INTO `grados` VALUES (12, 'C.C.', 'C.C.');
INSERT INTO `grados` VALUES (13, 'CAP.', 'CAP.');
INSERT INTO `grados` VALUES (14, 'T.N.', 'T.N.');
INSERT INTO `grados` VALUES (15, 'TTE.', 'TTE.');
INSERT INTO `grados` VALUES (16, 'T.F.', 'T.F.');
INSERT INTO `grados` VALUES (17, 'SBTTE.', 'SBTTE.');
INSERT INTO `grados` VALUES (18, 'ALF.', 'ALF.');
INSERT INTO `grados` VALUES (19, 'SOF.MTRE.', 'SOF.MTRE');
INSERT INTO `grados` VALUES (20, 'SOF.MY.', 'SOF.MY.');
INSERT INTO `grados` VALUES (21, 'SOF.1RO.', 'SOF.1RO.');
INSERT INTO `grados` VALUES (22, 'SOF.2DO.', 'SOF.2DO.');
INSERT INTO `grados` VALUES (23, 'SOF.INL.', 'SOF.INL.');
INSERT INTO `grados` VALUES (24, 'SGTO.1RO.', 'SGTO.1RO');
INSERT INTO `grados` VALUES (25, 'SGTO.2DO.', 'SGTO.2DO');
INSERT INTO `grados` VALUES (26, 'SGTO.INL.', 'SGTO.INL');
INSERT INTO `grados` VALUES (27, 'CADETE', 'CADETE');
INSERT INTO `grados` VALUES (28, 'ALUMNO', 'ALUMNO');
INSERT INTO `grados` VALUES (29, 'PROF. I', 'PROF. I');
INSERT INTO `grados` VALUES (30, 'PROF. II', 'PROF. II');
INSERT INTO `grados` VALUES (31, 'PROF. III', 'PROF. II');
INSERT INTO `grados` VALUES (32, 'PROF. IV', 'PROF. IV');
INSERT INTO `grados` VALUES (33, 'PROF. V', 'PROF. V');
INSERT INTO `grados` VALUES (34, 'TECN. I', 'TECN. I');
INSERT INTO `grados` VALUES (35, 'TECN. II', 'TECN. II');
INSERT INTO `grados` VALUES (36, 'TECN. III', 'TECN. II');
INSERT INTO `grados` VALUES (37, 'TECN. IV', 'TECN. IV');
INSERT INTO `grados` VALUES (38, 'TECN.V', 'TECN.V');
INSERT INTO `grados` VALUES (39, 'ADM.I', 'ADM.I');
INSERT INTO `grados` VALUES (40, 'ADM. II', 'ADM. II');
INSERT INTO `grados` VALUES (41, 'ADM. III', 'ADM. III');
INSERT INTO `grados` VALUES (42, 'ADM. IV', 'ADM. IV');
INSERT INTO `grados` VALUES (43, 'ADM. V', 'ADM. V');
INSERT INTO `grados` VALUES (44, 'AP.ADM.I', 'AP.ADM.I');
INSERT INTO `grados` VALUES (45, 'AP.ADM.II', 'AP.ADM.I');
INSERT INTO `grados` VALUES (46, 'AP.ADM.III', 'AP.ADM.I');
INSERT INTO `grados` VALUES (47, 'AP.ADM.IV', 'AP.ADM.I');
INSERT INTO `grados` VALUES (48, 'AP.ADM.V', 'AP.ADM.V');
INSERT INTO `grados` VALUES (49, 'ING.DAEN.', 'ING.DAEN');
INSERT INTO `grados` VALUES (50, 'ING.', 'ING.');
INSERT INTO `grados` VALUES (51, 'ARQ', 'ARQ');
INSERT INTO `grados` VALUES (52, 'LIC.', 'LIC.');
INSERT INTO `grados` VALUES (53, 'DR.', 'DR.');
INSERT INTO `grados` VALUES (54, 'DRA.', 'DRA.');
INSERT INTO `grados` VALUES (55, 'CONT.', 'CONT.');
INSERT INTO `grados` VALUES (56, 'TECN.SUP.', 'TECN.SUP');
INSERT INTO `grados` VALUES (57, 'MONS.', 'MONS.');
INSERT INTO `grados` VALUES (58, 'PROF', 'PROF');
INSERT INTO `grados` VALUES (59, 'SR.', 'SR.');
INSERT INTO `grados` VALUES (60, 'SRA.', 'SRA.');
INSERT INTO `grados` VALUES (61, 'SRTA.', 'SRTA.');
INSERT INTO `grados` VALUES (62, 'V.COM', 'V.COM');
INSERT INTO `grados` VALUES (63, 'TECNICO 2A', 'TEC. 2A');
INSERT INTO `grados` VALUES (64, 'TECNICO 3B', 'TEC. 3B');
INSERT INTO `grados` VALUES (65, 'AUXILIAR A', 'AUX. A');
INSERT INTO `grados` VALUES (66, 'AUXILIAR B', 'AUX.B');
INSERT INTO `grados` VALUES (67, 'NO REGISTRA', 'N.R.');

-- ----------------------------
-- Table structure for job_batches
-- ----------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cancelled_at` int(11) NULL DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of job_batches
-- ----------------------------

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED NULL DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `jobs_queue_index`(`queue`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of jobs
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` VALUES (4, '2026_06_15_184042_create_permission_tables', 2);
INSERT INTO `migrations` VALUES (5, '2026_06_15_184522_alter_users_table_for_cas', 3);

-- ----------------------------
-- Table structure for model_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions`  (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`) USING BTREE,
  INDEX `model_has_permissions_model_id_model_type_index`(`model_id`, `model_type`) USING BTREE,
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of model_has_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for model_has_roles
-- ----------------------------
DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles`  (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`) USING BTREE,
  INDEX `model_has_roles_model_id_model_type_index`(`model_id`, `model_type`) USING BTREE,
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of model_has_roles
-- ----------------------------
INSERT INTO `model_has_roles` VALUES (1, 'App\\Models\\User', 1);
INSERT INTO `model_has_roles` VALUES (1, 'App\\Models\\User', 4);
INSERT INTO `model_has_roles` VALUES (3, 'App\\Models\\User', 4);
INSERT INTO `model_has_roles` VALUES (2, 'App\\Models\\User', 6);

-- ----------------------------
-- Table structure for password_reset_tokens
-- ----------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens`  (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_reset_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `permissions_name_guard_name_unique`(`name`, `guard_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of permissions
-- ----------------------------
INSERT INTO `permissions` VALUES (1, 'dashboard.ver', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (2, 'usuarios.ver', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (3, 'usuarios.crear', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (4, 'usuarios.editar', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (5, 'usuarios.estado', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (6, 'usuarios.password', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (7, 'roles.ver', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (8, 'roles.crear', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (9, 'roles.editar', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (10, 'roles.usuarios', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (11, 'permisos.ver', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (12, 'permisos.asignar', 'web', '2026-06-17 13:28:21', '2026-06-17 13:28:21');
INSERT INTO `permissions` VALUES (13, 'socios.editar', 'web', '2026-06-17 16:04:53', '2026-06-17 16:13:58');
INSERT INTO `permissions` VALUES (14, 'socios.ver', 'web', '2026-06-17 18:45:51', '2026-06-17 18:45:51');
INSERT INTO `permissions` VALUES (15, 'socios.crear', 'web', '2026-06-17 18:45:52', '2026-06-17 18:45:52');
INSERT INTO `permissions` VALUES (16, 'socios.eliminar', 'web', '2026-06-17 18:45:52', '2026-06-17 18:45:52');
INSERT INTO `permissions` VALUES (17, 'socios.revincular', 'web', '2026-06-17 18:45:52', '2026-06-17 18:45:52');
INSERT INTO `permissions` VALUES (18, 'socios.informacion', 'web', '2026-06-17 18:45:52', '2026-06-17 18:45:52');
INSERT INTO `permissions` VALUES (19, 'socios.reportes', 'web', '2026-06-17 18:45:52', '2026-06-17 18:45:52');

-- ----------------------------
-- Table structure for residencias
-- ----------------------------
DROP TABLE IF EXISTS `residencias`;
CREATE TABLE `residencias`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_socio` int(10) NOT NULL,
  `departamento` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `ciudad` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `zona` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `calle` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `nro` varchar(8) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `estado` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `correo` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `formularioSolicitud` char(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL COMMENT 'FA= formulario de afiliacion, FI=formulario de incorporacion , OB= Observado AFCOOP, DN= formulario de solicitud de Devolucion Nominal',
  `afiliacionAfcoop` char(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL COMMENT 'CA= formulario de cuadro de afiliacion AFCOOP',
  `fotocopiaCarnet` char(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL COMMENT 'FC= Fotocopias de Carnet',
  `resolucion` int(11) NOT NULL,
  `idlog_coc` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of residencias
-- ----------------------------
INSERT INTO `residencias` VALUES (2, 4, 'OR', 'ORURO', 'EL BATEON', 'SANTA CRUZ', '16', '455234543', 'AC', 'olkerchoque123@gmail.com', 'FA', 'NO', 'NO', 117, 0);
INSERT INTO `residencias` VALUES (3, 5, 'LP', 'la paz', 'MIRAFLORES', 'HANS KUNDT', '1550', '79577730', 'AC', 'josedrgttv30@gmail.com', 'FI', 'NO', 'SI', 117, 0);
INSERT INTO `residencias` VALUES (4, 6, 'CB', 'QUILLACOLLO', 'EL BATEON', 'AV DEL EJERCITO', '16', '23232323', 'AC', 'olkerchoque123@gmail.com', 'FA', 'NO', 'NO', 135, 0);
INSERT INTO `residencias` VALUES (5, 7, 'LP', 'LA PAZ', 'MIRAFLORES', 'HANS KUNDT ESQUINA COSTA RICA', '1550', '77790230', 'AC', 'leidymabel1@gmail.com', 'FI', 'NO', 'NO', 112, 0);
INSERT INTO `residencias` VALUES (6, 8, 'LP', 'EL ALTO', 'ALTO CHUA LUMA BAJO', 'SANTA CRUZ', '16', '23232323', 'AC', 'migloa@gmail.com', 'FA', 'NO', 'NO', 115, 0);

-- ----------------------------
-- Table structure for resoluciones_juridica
-- ----------------------------
DROP TABLE IF EXISTS `resoluciones_juridica`;
CREATE TABLE `resoluciones_juridica`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `num` int(11) NULL DEFAULT NULL,
  `gestion` int(4) NULL DEFAULT NULL,
  `area` int(11) NULL DEFAULT NULL,
  `adjunto` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `estado` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `tipo` int(11) NULL DEFAULT NULL,
  `fecha_resolucion` date NULL DEFAULT NULL,
  `idlog` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 215 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_spanish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of resoluciones_juridica
-- ----------------------------
INSERT INTO `resoluciones_juridica` VALUES (1, 'RESOLUCION ADMINISTRATIVA AFCOOP 16 ASOCIADOS', 1564, 2019, 54, 'cd84134ebcd0be994d6cdbcde3b90492.pdf', 'AC', 59, '2019-10-21', 340249);
INSERT INTO `resoluciones_juridica` VALUES (2, 'RESOLUCION ADMINISTRATIVA AFCOOP 9 ASOCIADOS', 319, 2020, 54, '7a326ed7f8edf9f92aef5b4b79158d56.pdf', 'AC', 59, '2020-09-11', 340248);
INSERT INTO `resoluciones_juridica` VALUES (3, 'RESOLUCION DE 276 NUEVOS ASOCIADOS ', 888, 2021, 54, '288f6ceab490693a3db897f5cc96ef42.pdf', 'AC', 59, '2021-07-19', 314160);
INSERT INTO `resoluciones_juridica` VALUES (4, 'RESOLUCION 909 NUEVOS ASOCIADOS', 920, 2021, 54, '54d052369d90b0f703e8a9b533c81703.pdf', 'AC', 59, '2021-07-22', 314161);
INSERT INTO `resoluciones_juridica` VALUES (5, 'RESOLUCION 297 NUEVOS ASOCIADOS', 1058, 2021, 54, '7d44a027b396a3681fb19d9591f61d5d.pdf', 'AC', 59, '2021-08-12', 314162);
INSERT INTO `resoluciones_juridica` VALUES (6, 'RESOLUCION 48 NUEVOS ASOCIADOS', 1207, 2021, 54, '7d8126b07291159848d516cb5dac1775.pdf', 'AC', 59, '2021-09-06', 314163);
INSERT INTO `resoluciones_juridica` VALUES (7, 'REGISTRA LA ADMISIÃ³N DE TRESCIENTOS TREINTA Y TRES ASOCIADOS Y ASOCIADAS A LA COOPERATIVA.  ', 157, 2022, 62, '93a5e62ac68072c1b6b2ffe0bb49fbb2.pdf', 'AC', 59, '2022-02-01', 372916);
INSERT INTO `resoluciones_juridica` VALUES (8, 'REGISTRA LA ADMISIÃ³N DE 184 NUEVOS ASOCIADOS A LA COOPERATIVA', 196, 2022, 62, '7624898515d9c1194a07a31d5818e949.pdf', 'AC', 59, '2022-02-07', 372917);
INSERT INTO `resoluciones_juridica` VALUES (9, 'RESOLUCIÃ³N AFCOOP DEL CONSEJO DE ADMINISTRACIÃ³N Y COMITÃ© DE VIGILANCIA', 1126, 2021, 62, '11f9ebe435e4bee96e78ba80a60fcb11.pdf', 'AC', 59, '2021-08-24', 372945);
INSERT INTO `resoluciones_juridica` VALUES (10, 'RESOLUCIÃ³N AFCOOP INGRESO DE 9 NUEVOS ASOCIADOS', 319, 2020, 62, 'ba7e96ce2ee4f89c4c3ac0d71c27105c.pdf', 'AC', 59, '2020-09-11', 372946);
INSERT INTO `resoluciones_juridica` VALUES (11, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 01/21', 1, 2021, 62, 'b52b157915162108c6104a9a94e7969a.pdf', 'AC', 58, '2021-02-16', 372947);
INSERT INTO `resoluciones_juridica` VALUES (12, 'RESOLUCION DEL COMITE DE CREDITOS 02/21', 2, 2021, 62, '8adc21bc5aba6858a26ee4df3bed47e4.pdf', 'AC', 58, '2021-02-16', 372948);
INSERT INTO `resoluciones_juridica` VALUES (13, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 03/21', 3, 2021, 62, '5cfc35a1c39e543f5912d21bb11923a4.pdf', 'AC', 0, '2021-02-16', 372949);
INSERT INTO `resoluciones_juridica` VALUES (14, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 03/21 ', 3, 0, 0, '', 'AC', 0, '0000-00-00', 372950);
INSERT INTO `resoluciones_juridica` VALUES (15, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 03/21', 3, 2021, 62, 'a4e389daa89175f5f305b80189094cbc.pdf', 'AC', 58, '2021-02-16', 372951);
INSERT INTO `resoluciones_juridica` VALUES (16, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 04/21', 4, 2021, 62, 'b40a6b7599126ee7bc9bc0dbc91fad3d.pdf', 'AC', 58, '2021-02-16', 372964);
INSERT INTO `resoluciones_juridica` VALUES (17, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 05/21', 5, 2021, 62, '645ad700864cf08d2d0a49126cbf6ef6.pdf', 'AC', 0, '2021-03-23', 372965);
INSERT INTO `resoluciones_juridica` VALUES (18, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 05/21', 5, 2021, 62, '94fcfb1c8277ab33e9d9d97c62929291.pdf', 'AC', 58, '2021-03-23', 372966);
INSERT INTO `resoluciones_juridica` VALUES (19, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 06/21 ', 6, 2021, 62, 'c6c62fa4dba86502876cc450c8227c9e.pdf', 'AC', 58, '2021-04-01', 372967);
INSERT INTO `resoluciones_juridica` VALUES (20, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 07/21', 7, 2021, 62, 'ffe682f89d8e71b2b6c89e714de26fb6.pdf', 'AC', 58, '2021-03-23', 372968);
INSERT INTO `resoluciones_juridica` VALUES (21, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 08/21', 8, 2021, 62, 'fe250df829e67e09e66d7222b7647f81.pdf', 'AC', 58, '2021-03-30', 372969);
INSERT INTO `resoluciones_juridica` VALUES (22, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 09/21', 9, 2021, 62, '26374af77225d37aed280dba2d45b45d.pdf', 'AC', 58, '2021-04-06', 372970);
INSERT INTO `resoluciones_juridica` VALUES (23, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 10/21', 10, 2021, 62, '116ae3cc2d7a4b1ab383382eb0efdf3f.pdf', 'AC', 58, '2021-04-09', 372971);
INSERT INTO `resoluciones_juridica` VALUES (24, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 11', 11, 2021, 62, '442b11ab023b30e09c3430ffc01e896d.pdf', 'AC', 58, '2021-04-09', 385577);
INSERT INTO `resoluciones_juridica` VALUES (25, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS', 12, 2021, 62, '86d4e193172cde137b5fecb993b22f8d.pdf', 'AC', 58, '2021-05-09', 385579);
INSERT INTO `resoluciones_juridica` VALUES (26, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 13/21', 13, 2021, 62, '45ae8f1454f95daec846adb27bef494e.pdf', 'AC', 58, '2021-06-08', 385580);
INSERT INTO `resoluciones_juridica` VALUES (27, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 14/2021', 14, 2021, 62, 'e7e437f99aad2874d084ce944a962539.pdf', 'AC', 58, '2021-06-22', 385581);
INSERT INTO `resoluciones_juridica` VALUES (28, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 15/21', 15, 2021, 62, '80be0f83679ade9d481843698cf91ca2.pdf', 'AC', 58, '2021-04-23', 385582);
INSERT INTO `resoluciones_juridica` VALUES (29, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 16/21', 16, 2021, 62, '12bf224962743d8da2ebd82926c01aa9.pdf', 'AC', 58, '2021-05-25', 385583);
INSERT INTO `resoluciones_juridica` VALUES (30, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 17/21', 17, 2021, 62, '05299fa092d4b6e091b9e207014a094d.pdf', 'AC', 58, '2021-05-25', 385584);
INSERT INTO `resoluciones_juridica` VALUES (31, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 18/21 ', 18, 2021, 62, 'be3e9b8a32f8d78c381a89d5aeebadc1.pdf', 'AC', 58, '2021-09-07', 385585);
INSERT INTO `resoluciones_juridica` VALUES (32, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 19/21', 19, 2021, 62, '56c354cedc2b61ca6df103c8c0777d5f.pdf', 'AC', 58, '2021-09-07', 385586);
INSERT INTO `resoluciones_juridica` VALUES (33, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 20/21', 20, 2021, 62, '0a5dbc1ca4344151b78b5d2fe8440454.pdf', 'AC', 58, '2021-06-08', 385587);
INSERT INTO `resoluciones_juridica` VALUES (34, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 21/21', 21, 2021, 62, '0325385852fb03024158be671833794c.pdf', 'AC', 58, '2021-10-05', 385588);
INSERT INTO `resoluciones_juridica` VALUES (35, 'RESOLUCIÃ³N DE COMITÃ© DE CRÃ©DITOS 22/21 ', 22, 2021, 62, 'fd88c0c967e0a2e85aa6b6a815d49b0d.pdf', 'AC', 58, '2021-10-08', 385589);
INSERT INTO `resoluciones_juridica` VALUES (36, 'RESOLUCIÃ³N DE COMITÃ© DE CRÃ©DITOS 23/21', 23, 2021, 62, '1bd7a4f2fb60ff73e3aa781e3a6315a0.pdf', 'AC', 58, '2021-11-12', 385590);
INSERT INTO `resoluciones_juridica` VALUES (37, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS NÂ°24/21', 24, 2021, 62, '23bd0c53241b350c814741f3e79f10e3.pdf', 'AC', 58, '2021-06-08', 385591);
INSERT INTO `resoluciones_juridica` VALUES (38, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS NÂ° 25/21', 25, 2021, 62, 'cb5d6f8bd79e024ebf3e9aae4c81aada.pdf', 'AC', 58, '2021-06-08', 385592);
INSERT INTO `resoluciones_juridica` VALUES (39, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS NÂ°26/21', 26, 2021, 62, 'dc79b498f141fd3ea4356304264de8c7.pdf', 'AC', 58, '2021-09-06', 385593);
INSERT INTO `resoluciones_juridica` VALUES (40, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 27/2021', 27, 2021, 62, '7019a38b91738fd9cecf8da0cb5ccdfd.pdf', 'AC', 0, '2021-10-04', 385594);
INSERT INTO `resoluciones_juridica` VALUES (41, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 27', 27, 2021, 62, '726b86c596e7d6bdb05ff56b16725043.pdf', 'AC', 58, '2021-10-04', 385595);
INSERT INTO `resoluciones_juridica` VALUES (42, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 28/21', 28, 2021, 62, '69d13b1ed844ec78f572bb0c5f85e651.pdf', 'AC', 58, '2021-12-06', 385596);
INSERT INTO `resoluciones_juridica` VALUES (43, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS NÂ° 29/21', 29, 2021, 62, '52d50b830ca5217194673546f44fba59.pdf', 'AC', 58, '2021-11-30', 385598);
INSERT INTO `resoluciones_juridica` VALUES (44, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS 30/21', 30, 2021, 62, '9d3447c53a5a1c821c5dec18769dc28d.pdf', 'AC', 0, '2021-12-31', 385600);
INSERT INTO `resoluciones_juridica` VALUES (45, 'RESOLUCIÃ³N DEL COMITÃ© DE CRÃ©DITOS NÂ° 30/31', 30, 2021, 62, 'b2d81f0055e6c66caf052c5901286ebf.pdf', 'AC', 58, '2021-12-31', 385601);
INSERT INTO `resoluciones_juridica` VALUES (46, 'RESOLUCIÃ³N ADMINISTRATIVA 01/20 CAS', 1, 2020, 61, '2da3ef3f12ccacc48c89ffae1d33b45a.pdf', 'AC', 58, '2020-02-11', 420512);
INSERT INTO `resoluciones_juridica` VALUES (47, 'RESOLUCIÃ³N 02 CAS', 2, 2020, 62, '3bf1b7781cd405fe5b9af2781b356bd1.pdf', 'AC', 58, '2020-02-28', 420513);
INSERT INTO `resoluciones_juridica` VALUES (48, 'RESOLUCIÃ³N ADMINISTRATIVA 03/20', 3, 2020, 62, 'ff0664a51e61b5c211577a39f6f8900c.pdf', 'AC', 58, '2020-03-10', 420514);
INSERT INTO `resoluciones_juridica` VALUES (49, 'RESOLUCIÃ³N ADMINISTRATIVA 04/20 CAS', 4, 2020, 62, 'bd46e35be5232d45539e4f41b811cb9a.pdf', 'AC', 58, '2020-03-17', 420515);
INSERT INTO `resoluciones_juridica` VALUES (50, 'RESOLUCIÃ³N ADMINISTRATIVA 05A-20 CAS ', 5, 2020, 62, '6898eef66befeca3c32b64f9faf1db27.pdf', 'AC', 58, '2020-05-26', 420517);
INSERT INTO `resoluciones_juridica` VALUES (51, 'RESOLUCIÃ³N ADMINISTRATIVA 06/20', 6, 2020, 62, '2d248df19dce8474189d425c3bebcf4a.pdf', 'AC', 58, '2020-01-30', 420520);
INSERT INTO `resoluciones_juridica` VALUES (52, 'RESOLUCIÃ³N ADMINISTRATIVA 07/20', 7, 2020, 62, 'cced787b9db52b43e3a44eaae4e10962.pdf', 'AC', 58, '2020-09-03', 420522);
INSERT INTO `resoluciones_juridica` VALUES (53, 'RESOLUCIÃ³N ADMINISTRATIVA 08/20', 8, 2020, 62, 'fbefeb36cccfe2b6dbeebeceff977bdb.pdf', 'AC', 58, '2020-02-25', 420523);
INSERT INTO `resoluciones_juridica` VALUES (54, 'RESOLUCIÃ³N ADMINISTRATIVA 09/20 CAS', 9, 2020, 62, '70b790bfdeb88ed98c3c654001344f8c.pdf', 'AC', 58, '2020-04-30', 420524);
INSERT INTO `resoluciones_juridica` VALUES (55, 'RESOLUCIÃ³N ADMINISTRATIVA 10/2020', 10, 2020, 62, '4c8efbc47b2b07dedaa27a2736ac19bf.pdf', 'AC', 58, '2020-05-29', 420525);
INSERT INTO `resoluciones_juridica` VALUES (56, 'RESOLUCIÃ³N ADMINISTRATIVA 11/20 CAS', 11, 2020, 62, 'ebc0038fbcde76b90f435d3667d1b649.pdf', 'AC', 58, '2020-05-28', 420545);
INSERT INTO `resoluciones_juridica` VALUES (57, 'RESOLUCIÃ³N ADMINISTRATIVA 12/20 CAS', 12, 2020, 62, '9193c080a16355b91ef8f54a59142352.pdf', 'AC', 58, '2020-06-30', 420546);
INSERT INTO `resoluciones_juridica` VALUES (58, 'RESOLUCIÃ³N ADMINISTRATIVA 13/20 CAS', 13, 2020, 62, '205d47964c841f6bf8aa7c162cdcc9f4.pdf', 'AC', 58, '2020-07-26', 420547);
INSERT INTO `resoluciones_juridica` VALUES (59, 'RESOLUCIÃ³N ADMINISTRATIVA 14/20 CAS', 14, 2020, 62, 'b1b1dac32634d0653d7f904a22ebcdd9.pdf', 'AC', 58, '2020-08-27', 420548);
INSERT INTO `resoluciones_juridica` VALUES (60, 'RESOLUCIÃ³N ADMINISTRATIVA 15/2020 CAS', 15, 2020, 62, '9121be401ff68314513efb44e71fe72f.pdf', 'AC', 0, '2020-09-25', 420549);
INSERT INTO `resoluciones_juridica` VALUES (61, 'RESOLUCIÃ³N ADMINISTRATIVA 15/20 CAS', 15, 2020, 62, 'bcf3059c7eb0a00069b4f4c9665484b2.pdf', 'AC', 58, '2020-09-25', 420550);
INSERT INTO `resoluciones_juridica` VALUES (62, 'RESOLUCIÃ³N ADMINISTRATIVA 16/20 CAS', 16, 2020, 62, '0c2c76fa1eeaf3bab06137048a81d020.pdf', 'AC', 58, '2020-11-30', 420551);
INSERT INTO `resoluciones_juridica` VALUES (63, 'RESOLUCIÃ³N ADMINISTRATIVA 17/20 CAS', 17, 2020, 62, '5863b75485cebc1f0eb59973a7acd465.pdf', 'AC', 58, '2020-11-30', 420552);
INSERT INTO `resoluciones_juridica` VALUES (64, 'RESOLUCIÃ³N ADMINISTRATIVA 18/2020 CAS', 18, 2020, 62, '1c9a6766d7f3ef5c61fc2a9e74c36788.pdf', 'AC', 0, '0000-00-00', 420553);
INSERT INTO `resoluciones_juridica` VALUES (65, 'RESOLUCIÃ³N ADMINISTRATIVA 18/2020 CAS', 18, 2020, 62, '1e00c5b8ca40b02802578391de2c7175.pdf', 'AC', 58, '2020-12-01', 420554);
INSERT INTO `resoluciones_juridica` VALUES (66, 'RESOLUCIÃ³N ADMINISTRATIVA 19/20 CAS', 19, 2020, 62, 'b21f1d1e565339b541bb5ecbc2795fff.pdf', 'AC', 58, '2020-12-02', 420555);
INSERT INTO `resoluciones_juridica` VALUES (67, 'RESOLUCIÃ³N ADMINISTRATIVA 20/20 CAS', 20, 2020, 62, 'b77046202cbc9a450eef240808c56424.pdf', 'AC', 0, '2020-12-18', 420556);
INSERT INTO `resoluciones_juridica` VALUES (68, 'RESOLUCIÃ³N DE DIRECTORIO CAS 01/20', 1, 2020, 62, 'cd76a33bb625ea7056d741abef4e6fa4.pdf', 'AC', 57, '2020-06-26', 420580);
INSERT INTO `resoluciones_juridica` VALUES (69, 'RESOLUCIÃ³N DE DIRECTORIO CAS 02/20', 2, 2020, 62, '8a1cfe5c519014ab62052f39414fe959.pdf', 'AC', 57, '2020-06-26', 420581);
INSERT INTO `resoluciones_juridica` VALUES (70, 'RESOLUCIÃ³N DE DIRECTORIO CAS 03/20', 3, 2020, 62, '749a7e570a210ae365d32491e9af5708.pdf', 'AC', 57, '2020-06-26', 420584);
INSERT INTO `resoluciones_juridica` VALUES (71, 'RESOLUCIÃ³N DE DIRECTORIO CAS 04/2020', 4, 2020, 62, '511af539aa3ac25a8bff2ae673509bd2.pdf', 'AC', 0, '2020-06-25', 420585);
INSERT INTO `resoluciones_juridica` VALUES (72, 'RESOLUCIÃ³N DE DIRECTORIO CAS 04/20', 4, 2020, 62, '837d424b59098f65e71c38da36ba66a2.pdf', 'AC', 57, '2020-06-25', 420587);
INSERT INTO `resoluciones_juridica` VALUES (73, 'RESOLUCIÃ³N DE DIRECTORIO CAS 05/20', 5, 2020, 62, 'b662825405e4d9313a969453717a760a.pdf', 'AC', 57, '2020-06-26', 420588);
INSERT INTO `resoluciones_juridica` VALUES (74, 'RESOLUCIÃ³N DE DIRECTORIO CAS 05/20', 5, 2020, 62, 'f5a180fc7e5b9ee9f7881d9177b0b9ce.pdf', 'AC', 57, '2020-06-26', 420590);
INSERT INTO `resoluciones_juridica` VALUES (75, 'RESOLUCIÃ³N DE DIRECTORIO 06/20', 6, 2020, 62, '2a6b0342713a4f16552c9ba870985fd7.pdf', 'AC', 57, '2020-06-26', 420591);
INSERT INTO `resoluciones_juridica` VALUES (76, 'RESOLUCIÃ³N DE DIRECTORIO 6A-20', 6, 2020, 62, '90c1e2e6cd5f39f2ccd5bd2107ce1934.pdf', 'AC', 0, '2020-06-26', 420592);
INSERT INTO `resoluciones_juridica` VALUES (77, 'RESOLUCIÃ³N DE DIRECTORIO 06A/20', 6, 2020, 62, '17e4815c6e7b681ec26ae11c909a6e97.pdf', 'AC', 57, '2020-06-26', 420594);
INSERT INTO `resoluciones_juridica` VALUES (78, 'RESOLUCIÃ³N DE DIRECTORIO CAS 07/20', 7, 2020, 62, '9cd956e16081f781ca6c4b26be6d31d4.pdf', 'AC', 57, '2020-10-19', 420595);
INSERT INTO `resoluciones_juridica` VALUES (79, 'RESOLUCIÃ³N DE DIRECTORIO CAS 08/20', 8, 2020, 62, '7449b06acea53b04ec5fbbc6454971e5.pdf', 'AC', 57, '2020-06-26', 420597);
INSERT INTO `resoluciones_juridica` VALUES (80, 'RESOLUCIÃ³N DE DIRECTORIO CAS 09/20', 9, 2020, 62, '00c7ae0ef0ddaeaba017e3cecc3b5c5c.pdf', 'AC', 57, '2020-06-26', 420598);
INSERT INTO `resoluciones_juridica` VALUES (81, 'RESOLUCIÃ³N DE DIRECTORIO CAS 10/20', 10, 2020, 62, '2bc56a270e88eb056de0b3e680d310cd.pdf', 'AC', 57, '2020-06-26', 420599);
INSERT INTO `resoluciones_juridica` VALUES (82, 'RESOLUCIÃ³N DE DIRECTORIO CAS 10A/20', 10, 2020, 62, '7818f4064a4b17994bda4355595d67b3.pdf', 'AC', 57, '2020-06-27', 420600);
INSERT INTO `resoluciones_juridica` VALUES (83, 'RESOLUCIÃ³N DE DIRECTORIO CAS 11/20', 11, 2020, 62, 'c7ef91d2d177fe76c1eff9016ca49031.pdf', 'AC', 57, '2020-06-26', 420601);
INSERT INTO `resoluciones_juridica` VALUES (84, 'RESOLUCIÃ³N DE DIRECTORIO CAS 12/2020', 12, 2020, 62, '3e2e88bae1554369db9388d8f9b285e6.pdf', 'AC', 57, '2020-06-26', 420602);
INSERT INTO `resoluciones_juridica` VALUES (85, 'RESOLUCIÃ³N DE DIRECTORIO CAS 13/20', 13, 2020, 62, 'c44196c311f4b2dae6d49ed14be8a092.pdf', 'AC', 57, '2020-06-26', 420603);
INSERT INTO `resoluciones_juridica` VALUES (86, 'RESOLUCIÃ³N DE DIRECTORIO  CAS 14/20', 14, 2020, 62, '2022056356004aa3e0568442c3b947c5.pdf', 'AC', 57, '2020-06-26', 420604);
INSERT INTO `resoluciones_juridica` VALUES (87, 'RESOLUCIÃ³N DE DIRECTORIO CAS 15/2020', 15, 2020, 62, '71b180f97ce78e213bd4b760fda81551.pdf', 'AC', 57, '2020-06-26', 420605);
INSERT INTO `resoluciones_juridica` VALUES (88, 'RESOLUCIÃ³N DE DIRECTORIO CAS 16/20', 16, 2020, 62, 'e0f0c543c2fea0a9853f3a1d539e916b.pdf', 'AC', 57, '2020-06-26', 420606);
INSERT INTO `resoluciones_juridica` VALUES (89, 'RESOLUCIÃ³N DE DIRECTORIO CAS 17/20', 17, 2020, 62, 'e15bdd252bd1ab681a64b401bb3f0736.pdf', 'AC', 57, '2020-07-30', 420607);
INSERT INTO `resoluciones_juridica` VALUES (90, 'RESOLUCIÃ³N DE DIRECTORIO CAS 18/20', 18, 2020, 62, 'd4c34fca18e97da2fcb4b16fd53e44ff.pdf', 'AC', 57, '2020-08-28', 420608);
INSERT INTO `resoluciones_juridica` VALUES (91, 'RESOLUCIÃ³N DE DIRECTORIO CAS 19/20', 19, 2020, 62, 'cb797a26f8d989b2b72c8054b52f623a.pdf', 'AC', 57, '2020-09-28', 420609);
INSERT INTO `resoluciones_juridica` VALUES (92, 'RESOLUCIÃ³N DE DIRECTORIO CAS 20/20', 20, 2020, 62, '94f251a6e5f778d93aa65f215c614213.pdf', 'AC', 57, '2020-10-28', 420610);
INSERT INTO `resoluciones_juridica` VALUES (93, 'RESOLUCIÃ³N DE DIRECTORIO CAS 21/20', 21, 2020, 62, 'a264aa9332ffd2b444f4f063aa59822f.pdf', 'AC', 57, '2020-11-30', 420611);
INSERT INTO `resoluciones_juridica` VALUES (94, 'RESOLUCIÃ³N DE DIRECTORIO CAS 22/20', 22, 2020, 62, '622554cc7f6e990d90a3bcec80c3dd3a.pdf', 'AC', 57, '2020-12-07', 420612);
INSERT INTO `resoluciones_juridica` VALUES (95, 'RESOLUCIÃ³N DE DIRECTORIO CAS 23/20', 23, 2020, 62, '30f444c3b261b72d3cdde722a14873b6.pdf', 'AC', 57, '2020-12-07', 420613);
INSERT INTO `resoluciones_juridica` VALUES (96, 'RESOLUCIÃ³N DE DIRECTORIO CAS 24/20', 24, 2020, 62, '1e433564f6b22a28a917bd87d39ff970.pdf', 'AC', 57, '2020-12-07', 420614);
INSERT INTO `resoluciones_juridica` VALUES (97, 'RESOLUCIÃ³N DE DIRECTORIO CAS 25/20', 25, 2020, 62, 'f8620bc2185b5b20f301334d185182d4.pdf', 'AC', 57, '2020-12-07', 420615);
INSERT INTO `resoluciones_juridica` VALUES (98, 'RESOLUCIÃ³N ADMINISTRATIVA 661/2022 DE LA AFCOOP QUE REGISTRA LA ADMISIÃ³N DE 111 ASOCIADOS A LA COOPERATIVA DE AHORRO Y CRÃ©DITO DE VÃ­NCULO LABORAL \"APÃ³STOL SANTIAGO\"  ', 661, 2022, 62, '6aee60fe38dd02da5474007349288625.pdf', 'AC', 59, '2022-04-18', 423322);
INSERT INTO `resoluciones_juridica` VALUES (99, 'RESOLUCIÃ³N AFCOOP 796/22 QUE ADMITE LA INCLUSIÃ³N DE 169 NUEVOS ASOCIADOS A LA COOPERATIVA.', 796, 2022, 62, 'ba4d090242968a5724c91b502c269776.pdf', 'AC', 59, '2022-05-06', 423329);
INSERT INTO `resoluciones_juridica` VALUES (100, 'RESOLUCIÃ³N AFCOOP 810/22 QUE INSCRIBE LA PÃ©RDIDA DE CALIDAD DE ASOCIADO POR MUERTE Y RENUNCIA VOLUNTARIA.', 810, 2022, 62, '123fa357451868d96826d82b3fc45fac.pdf', 'AC', 59, '2022-05-10', 423330);
INSERT INTO `resoluciones_juridica` VALUES (101, 'RESOLUCIÃ³N AFCOOP 1128 QUE DISPONE LA PÃ©RDIDA DE CALIDAD DE ASOCIADOS DE 5 CIUDADANOS POR RENUNCIA', 1128, 2022, 62, 'b300ef74f321a19fbaa0f8fd5666c16e.pdf', 'AC', 59, '2022-07-01', 472068);
INSERT INTO `resoluciones_juridica` VALUES (102, 'RESOLUCIÃ³N AFCOOP 1193 QUE DISPONE LA ADMISIÃ³N DE 101 ASOCIADOS Y ASOCIADAS A LA COOPERATIVA APÃ³STOL SANTIAGO', 1193, 2022, 62, '2ce57f7cbb42a7ede47baac7ef1e0f10.pdf', 'AC', 59, '2022-07-12', 472069);
INSERT INTO `resoluciones_juridica` VALUES (103, 'RESOLUCIÃ³N DEL CONSEJO DE ADMINISTRACIÃ³N QUE MARCA LOS CRITERIOS PARA CIERRE DE GESTIÃ³N', 2, 2021, 62, '6bbc3a3d8b3c9cb76ec0634a80dfa50f.pdf', 'AC', 57, '2021-01-01', 472312);
INSERT INTO `resoluciones_juridica` VALUES (104, 'RESOLUCION DEL CONSEJO DE ADMINISTRACION  QUE DETERMINA LA EXPULSION DEL SEÃ±OR NELSON ABRAHAM CORDOVA ANTEZANA', 3, 2021, 62, '289101cca73e411ed8cd76334b99671a.pdf', 'AC', 57, '2021-02-09', 472313);
INSERT INTO `resoluciones_juridica` VALUES (105, 'RESOLUCIÃ³N DEL CONSEJO DE ADMINISTRACIÃ³N QUE AUTORIZA A RECIBIR REFINANCIAMIENTO DEL MES DE MARZO A MAYO  POR MOTIVOS DE COVID 19 SOLAMENTE AL PRÃ©STAMO REGULAR.', 4, 2021, 62, '4852b353532688c5309de000838059af.pdf', 'AC', 57, '2021-02-09', 472314);
INSERT INTO `resoluciones_juridica` VALUES (106, 'RESOLUCIÃ³N DEL CONSEJO DE ADMINISTRACIÃ³N QUE RESUELVE REMITIR EL FALLECIMIENTO Y LOS ANTECEDENTES DEL ASOCIADO GONZALO RENÃ¡N RODRIGUEZ FERNÃ¡NDEZ', 5, 2021, 62, '23b0c611bb4181270a22c5fb096019aa.pdf', 'AC', 57, '2021-03-05', 472315);
INSERT INTO `resoluciones_juridica` VALUES (107, 'RESOLUCIÃ³N DEL CONSEJO DE ADMINISTRACIÃ³N QUE RESUELVE REMITIR EL FALLECIMIENTO DEL ASOCIADO MARIO FLORES LIMACHI', 6, 2021, 62, 'c0262e88e6bb245256b28dd596feb73f.pdf', 'AC', 57, '2021-03-09', 472316);
INSERT INTO `resoluciones_juridica` VALUES (108, 'RESOLUCIÃ³N DEL CONSEJO DE ADMINISTRACIÃ³N QUE RESUELVE AUTORIZAR REFINANCIAMIENTO AL PRÃ©STAMO REGULAR DEL MES DE JUNIO A SEPTIEMBRE 2021 ', 7, 2021, 62, 'a7ffe9aaea89099da03831c4590b3370.pdf', 'AC', 57, '2021-06-19', 472317);
INSERT INTO `resoluciones_juridica` VALUES (109, 'RESOLUCIÃ³N DEL CONSEJO DE ADMINISTRACIÃ³N QUE RESUELVE REMITIR EL FALLECIMIENTO DEL ASOCIADO JESÃºS CALIXTO MAMANI SARDÃ³N', 8, 2021, 62, '46b5c14343c2ef91e2e2ed347d1eb1ae.pdf', 'AC', 57, '2021-04-16', 472318);
INSERT INTO `resoluciones_juridica` VALUES (110, 'RESOLUCIÃ³N AFCOOP 1423 QUE ADMITE EL INGRESO DE 194 NUEVOS ASOCIADOS A LA COOPERATIVA', 1423, 2022, 62, '9d69e59c80788ad58f15988960bccfe7.pdf', 'AC', 59, '2022-08-15', 472325);
INSERT INTO `resoluciones_juridica` VALUES (111, 'RESOLUCIÃ³N AFCOOP 1536/2022 QUE REGISTRA LA ADMISIÃ³N DE 41 ASOCIADOS Y ASOCIADAS  A LA COOPERATIVA DE AHORRO Y CRÃ©DITO DE VÃ­NCULO LABORAL OFICIALES DE CABALLERÃ­A APÃ³STOL SANTIAGO R.L.', 1536, 2022, 62, '774edb25fe20fc16e901bd058d26192e.pdf', 'AC', 59, '2022-09-05', 494157);
INSERT INTO `resoluciones_juridica` VALUES (112, 'RESOLUCIÃ³N AFCOOP 1846 QUE REGISTRA LA  PÃ©RDIDA DE CALIDAD DE ASOCIADOS DE 8 CIUDADANOS', 1846, 2022, 62, '', 'AC', 59, '2022-11-07', 528185);
INSERT INTO `resoluciones_juridica` VALUES (113, 'RESOLUCIÃ³N AFCOOP DE INCLUSIÃ³N DE 121 NUEVOS ASOCIADOS', 1795, 2022, 62, '', 'AC', 0, '0000-00-00', 528186);
INSERT INTO `resoluciones_juridica` VALUES (114, 'RESOLUCIÃ³N AFCOOP DE INCLUSIÃ³N DE 121 NUEVOS ASOCIADOS', 1795, 2022, 62, 'e3f18b8330bc2f601d3a67ee81c0d038.pdf', 'AC', 59, '2022-10-14', 562504);
INSERT INTO `resoluciones_juridica` VALUES (115, 'RESOLUCIÃ³N AFCOOP 2159/22 INCLUSIÃ³N DE 109 NUEVOS ASOCIADOS', 2159, 2022, 62, '4d68d5a36c0356fb5ff3c69b257e6d01.pdf', 'AC', 59, '2022-12-28', 562890);
INSERT INTO `resoluciones_juridica` VALUES (116, 'RESOLUCIÃ³N AFCOOP 075/2023 15 RENUNCIA VOLUNTARIA', 75, 2023, 62, '3c4cc41f33621cfc3222c34b14df9ec3.pdf', 'AC', 59, '2023-01-17', 562894);
INSERT INTO `resoluciones_juridica` VALUES (117, 'REGISTRO DE ADMISIÃ³N DE 78 ASOCIADAS Y ASOCIADOS A LA COOPERATIVA APÃ³STOL SANTIAGO R.L.', 306, 2023, 62, '43e35fb0aeafc79a9bfab79c2ab85de1.pdf', 'AC', 59, '2023-03-07', 597420);
INSERT INTO `resoluciones_juridica` VALUES (118, 'RES CONS. ADM FALLECIMIENTO CARLA VIVIANA RODRÃ­GUEZ CALDERÃ³N', 9, 2021, 62, 'b17d12398d3e895fab4e38834887025b.pdf', 'AC', 57, '2021-05-11', 607154);
INSERT INTO `resoluciones_juridica` VALUES (119, 'RES. CONSEJO DE ADMINISTRACIÃ³N', 10, 2021, 62, 'b8888ca8d2c98274d8d4696aeb5a5f6a.pdf', 'AC', 57, '2021-05-11', 607155);
INSERT INTO `resoluciones_juridica` VALUES (120, 'RES CONSEJO DE ADMINISTRACIÃ³N 11/21 NOMBRA VICEPRESIDENTE A CNL PEDRO ALEJANDRO CASSO ACHÃ¡ ', 11, 2021, 62, '190c691fcb2d2bcb48ba449ce558d322.pdf', 'AC', 57, '2021-05-20', 607156);
INSERT INTO `resoluciones_juridica` VALUES (121, 'RES. CONSEJO DE ADMINISTRACIÃ³N 12/21 CONVOCA A ASAMBLEA ORDINARIA  ', 12, 2021, 62, '5d6f0b50dfcb3de819e6bbfbceb7d8d8.pdf', 'AC', 57, '2021-05-20', 607157);
INSERT INTO `resoluciones_juridica` VALUES (122, 'RES. CONSEJO DE ADMINISTRACIÃ³N 13/21 INCENTIVO BS 100.- POR SESIÃ³N DE CONSEJO. ', 13, 2021, 62, 'b21e841cab9c40c1ccca905e1d30a1ce.pdf', 'AC', 57, '2021-08-13', 607158);
INSERT INTO `resoluciones_juridica` VALUES (123, 'RES. CONSEJO DE ADMINISTRACIÃ³N 14/21 REQUISITOS RETIRO VOLUNTARIO', 14, 2021, 62, '9ae548a5273a7ea1c9c3670f234c0d28.pdf', 'AC', 57, '2021-09-10', 607160);
INSERT INTO `resoluciones_juridica` VALUES (124, 'RES. CONSEJO DE ADMINISTRACIÃ³N 15/21 RENUNCIA DE LUIS ALBERTO PACHECO', 15, 2021, 62, '8f6d0f3a5103a0481faaf618331c9ef6.pdf', 'AC', 0, '2021-09-03', 607161);
INSERT INTO `resoluciones_juridica` VALUES (125, 'RES. CONSEJO DE ADMINISTRACIÃ³N 15/21 RENUNCIA VOLUNTARIA LUIS ALBERTO PACHECO', 15, 2021, 62, '3f251624321cfb635bf5f2847e1e831b.pdf', 'AC', 57, '2021-09-03', 607162);
INSERT INTO `resoluciones_juridica` VALUES (126, 'RES. CONSEJO DE ADMINISTRACIÃ³N 16/21 FALLECIMIENTO DE JOSÃ© LUIS FRÃ­AS CORDERO.', 16, 2021, 62, '04b8682a3180f5427a7f5a69199b4aa9.pdf', 'AC', 57, '2021-11-12', 607163);
INSERT INTO `resoluciones_juridica` VALUES (127, 'RES. CONSEJO DE ADMINISTRACIÃ³N 17/21 PREVISIÃ³N DE 600 MIL PARA ADQUISICIÃ³N DE AMBIENTES', 17, 2021, 62, '40e9a6e18e21691fb99fac9f401a481c.pdf', 'AC', 57, '2021-11-12', 607164);
INSERT INTO `resoluciones_juridica` VALUES (128, 'RES. CONSEJO DE ADMINISTRACIÃ³N 18/21 CIERRE CONTABLE ', 18, 2021, 62, '4ca1c8ee2f0031f6b143cfb39be06ac0.pdf', 'AC', 57, '2021-11-15', 607167);
INSERT INTO `resoluciones_juridica` VALUES (129, 'RES. CONSEJO DE ADMINISTRACIÃ³N 19/21 DISTRIBUCIÃ³N DE TRASPASOS Y PORCENTAJES', 19, 2021, 62, '5d79fbe1895fe5aaa8d60791a5bce355.pdf', 'AC', 57, '2021-11-15', 607168);
INSERT INTO `resoluciones_juridica` VALUES (130, 'RES. CONSEJO DE ADMINISTRACIÃ³N 20/21 CRÃ©DITO HABITACIONAL CONVENIO URUBÃ³', 20, 2021, 62, '1f4175a68629e283b4d8a89aff2588a0.pdf', 'AC', 57, '2021-11-16', 607169);
INSERT INTO `resoluciones_juridica` VALUES (131, 'RES. CONSEJO DE ADMINISTRACIÃ³N 21/21 GASTOS DE DESVINCULACIÃ³N A CARGO DEL CAS EN CASO DE JUBILACIÃ³N.', 21, 2021, 62, 'caccad75c2d2d3d9dd0f568e549fd616.pdf', 'AC', 57, '2021-11-17', 607170);
INSERT INTO `resoluciones_juridica` VALUES (132, 'RES. CONSEJO DE ADMINISTRACIÃ³N 22/21 AUTORIZA EL REVALÃºO TÃ©CNICO', 22, 2021, 62, 'f49e1ad4fa289e5be7fcbd6730970948.pdf', 'AC', 57, '2021-11-18', 607171);
INSERT INTO `resoluciones_juridica` VALUES (133, 'RES. CONSEJO DE ADMINISTRACIÃ³N 23/21 OBLIGACIONES CON LOS ASOCIADOS ', 23, 2021, 62, '6cd74b565e7e613418fa6439e28ed487.pdf', 'AC', 57, '2021-11-24', 607172);
INSERT INTO `resoluciones_juridica` VALUES (134, 'RES. CONSEJO DE ADMINISTRACIÃ³N 24/21 MODIFIACIÃ³N EN CARTERA Y KÃ¡RDEX DE PRÃ©STAMOS.', 24, 2021, 62, '1068120234c92abe17747b48aac6c8f5.pdf', 'AC', 57, '2021-12-31', 607173);
INSERT INTO `resoluciones_juridica` VALUES (135, 'RESOLUCIÃ³N AFCOOP 835/2023 DE PÃ©RDIDA DE CALIDAD DE 19 ASOCIADOS POR RENUNCIA VOLUNTARIA Y 1 POR FALLECIMIENTO', 835, 2023, 62, '600ac8e2494e7bbce953a53a5fda5256.pdf', 'AC', 59, '2023-06-07', 619804);
INSERT INTO `resoluciones_juridica` VALUES (136, 'RESOLUCIÃ³N AFCOOP 925/2023 DE PÃ©RDIDA DE CALIDAD DE ASOCIADOS Y ASOCIADAS DE 15 POR RENUNCIA VOLUNTARIA Y DOS POR FALLECIMIENTO.', 925, 2023, 62, 'c5cfe9fc9b5d72de3310b1d82958ebb3.pdf', 'AC', 59, '2023-06-26', 619807);
INSERT INTO `resoluciones_juridica` VALUES (137, 'RES AFCOOP 1047/23 DE MODIFICACIÃ³N AL ESTATUTO ORGÃ¡NICO', 1047, 2023, 62, '832723a0c1168e03f33d15e7fd723ccd.pdf', 'AC', 59, '2023-07-10', 639464);
INSERT INTO `resoluciones_juridica` VALUES (138, 'RES AFCOOP DE INCLUSIÃ³N DE 164 NUEVOS ASOCIADOS', 1126, 2023, 62, '4c801b20a508375a624616eabd9a2b72.pdf', 'AC', 59, '0000-00-00', 639466);
INSERT INTO `resoluciones_juridica` VALUES (139, 'RES AFCOOP DE PERDIDA DE CALIDAD DE 22 ASOCIADOS POR RENUNCIA VOLUNTARIA Y 1 POR FALLECIMIENTO', 1166, 2023, 62, '30d3a198d79c3a4814dee70338d6203c.pdf', 'AC', 59, '2023-07-25', 639467);
INSERT INTO `resoluciones_juridica` VALUES (140, 'RESOLUCIÃ³N DEL CONSEJO DE ADMINISTRACIÃ³N DE DONACIÃ³N DE MUEBLES AL ESTADO MAYOR', 20, 2022, 62, '0540914583cf9534a206564e58accf5c.pdf', 'AC', 57, '2022-12-30', 645789);
INSERT INTO `resoluciones_juridica` VALUES (141, 'RESOLUCIÃ³N DE DIRECTORIO NÂ°001-22 CONTRATO DE LA EMPRESA ARTE Y CULTURA', 1, 2022, 62, '3217b19b7f27f0c4717d019e642f5e07.pdf', 'AC', 57, '2022-02-21', 645790);
INSERT INTO `resoluciones_juridica` VALUES (142, 'RESOLUCIÃ³N DE DIRECTORIO NO 002/22, IMPLEMENTAR A PARTIR DE 1RO EL PLAN DE CUENTAS CON SU RESPECTIVO MANUAL ', 2, 2022, 61, '5150adb03ab0478fa2014fbca08b4427.pdf', 'AC', 57, '2022-04-22', 645802);
INSERT INTO `resoluciones_juridica` VALUES (143, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NO 01/22, ASIGNAR BS. 30.000 COMO FONDO DE PRÃ©STAMO DE AUXILIO, AUTORIZAR EL DESEMBOLSO DE BS. 7.000 PARA CAJA CHICA, AUTORIZA EL DESEMBOLSO PARA REFRIGERIO DURANTE LA GESTIÃ³N. ', 1, 2022, 62, 'f2c96439933e741e3fe8741ce48f5ddc.pdf', 'AC', 0, '2022-01-07', 645803);
INSERT INTO `resoluciones_juridica` VALUES (144, 'RESOLUCIÃ³N DE CONSEJO ADMINISTRATIVO NÂ° 02/22, IMPLEMENTAR LA NUEVA TABLA DE PRESTAMOS DE EMERGENCIA Y DE CONSUMO.', 2, 2022, 62, 'a382e91bc0ea5677ab8ad1ea0d123838.pdf', 'AC', 57, '2022-03-07', 645804);
INSERT INTO `resoluciones_juridica` VALUES (145, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ° 01/22. ASIGNAR LA SUMA DE BS. 30.000 PARA EL FONDO DE PRÃ©STAMO DE AUXILIO. AUTORIZAR EL DESEMBOLSO DE 70.000 PARA CAJA CHICA. AUTORIZAR EL DESEMBOLSO PARA REFRIGERIO.', 1, 2022, 62, 'e585b51e3b5d5225854faad6a9336d1e.pdf', 'AC', 57, '2022-01-07', 645805);
INSERT INTO `resoluciones_juridica` VALUES (146, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ° 03/22 DE IMPLEMENTAR LA NUEVA TABLA DE PRESTAMOS REGULARES', 3, 2022, 62, 'd56145708913f5f578dae6d52996bd3b.pdf', 'AC', 57, '2022-03-07', 645806);
INSERT INTO `resoluciones_juridica` VALUES (147, 'RESOLUCIÃ³N DE CONSEJO ADMINISTRATIVO NÂ° 04/22 DE APROBACIÃ³N DE 168 NUEVOS ASOCIADOS, LA RENUNCIA DE 18 MIEMBROS POR RENUNCIA Y 2 SON POR CAUSA DE FALLECIMIENTO. ', 4, 2022, 62, '032581bd43d922f942fdc7e25bbb1468.pdf', 'AC', 57, '2022-04-11', 645807);
INSERT INTO `resoluciones_juridica` VALUES (148, 'RESOLUCIÃ³N DEL CONSEJO DE ADMINISTRACIÃ³N NÂ° 05/22 DE REESTRUCTURACIÃ³N Y RECLASIFICACIÃ³N DEL PLAN DE CUENTAS CONTABLE DE LA COOPERATIVA', 5, 2022, 62, 'd261b43876fdd678fe72f5fa0b7b82a6.pdf', 'AC', 57, '2022-04-18', 645808);
INSERT INTO `resoluciones_juridica` VALUES (149, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°06/22 DE REINCORPORACIONES DE ASOCIADOS QUE SE HAYAN DESVINCULADO TOTALMENTE DE LA COOPERATIVA', 6, 2022, 62, '88252b9e4477ae3ef551b3b9001fcac7.pdf', 'AC', 57, '2022-05-03', 645809);
INSERT INTO `resoluciones_juridica` VALUES (150, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°07/22 DE AQUELLOS SOLICITANTES DE AFILIACIÃ³N QUE SE ENCUENTREN EN CALIDAD DE FUNCIONARIOS PÃºBLICOS, INDÃ­QUESE LA NATURALEZA JURÃ­DICA DEL CAS.', 7, 2022, 62, '451c64136c0a5d51471ec0ec3c7aa43b.pdf', 'AC', 57, '2022-05-10', 645823);
INSERT INTO `resoluciones_juridica` VALUES (151, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°08/22 DE APROBACIÃ³N DE 101 NUEVOS ASOCIADOS Y LA PERDIDA DE CALIDAD DE 5 MIEMBROS', 8, 2022, 62, '4bcba0b311b27615828927728ae4643e.pdf', 'AC', 57, '2022-05-30', 645824);
INSERT INTO `resoluciones_juridica` VALUES (152, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°09/22 DE MODIFICACIÃ³N AL FORMULARIO DE LA DENOMINACIÃ³N FONDO VOLUNTARIO SOLIDARIO POR FALLECIMIENTO.', 9, 2022, 62, 'a97dcf51b93685dc7da4c3130cc82781.pdf', 'AC', 57, '2022-07-01', 645825);
INSERT INTO `resoluciones_juridica` VALUES (153, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°10/22 DE QUE LOS REFINANCIAMIENTOS SOLO PUEDEN OTORGARSE POR UNA VEZ Y QUE LA FECHA MÃ¡XIMA ES HASTA EL 29 DE JULIO DE 2022', 10, 2022, 62, 'e53eeaa20a62fe20eaca57fc6f0b093c.pdf', 'AC', 57, '2022-07-01', 645826);
INSERT INTO `resoluciones_juridica` VALUES (154, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°11/22 DE APROBACIÃ³N DE 194 NUEVOS ASOCIADOS A LA COOPERATIVA', 11, 2022, 62, 'eed31ec2f3ebf52a13e5d59201b3cd33.pdf', 'AC', 57, '2022-07-01', 645828);
INSERT INTO `resoluciones_juridica` VALUES (155, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°12/22 DE APROBACIÃ³N DE 194 NUEVOS ASOCIADOS A LA COOPERATIVA', 12, 2022, 62, 'b0626becec494966d7358c1be67f9531.pdf', 'AC', 57, '2022-07-08', 645832);
INSERT INTO `resoluciones_juridica` VALUES (156, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°13/22 DE APROBACIÃ³N DE 41 NUEVOS ASOCIADOS A LA COOPERATIVA', 13, 2022, 62, '44f90586bcceeaaca5333965f636f43e.pdf', 'AC', 57, '2022-07-07', 645833);
INSERT INTO `resoluciones_juridica` VALUES (157, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°14/22 DE EL \nPROYECTO PILOTO HABITACIONAL \"HOGAR DULCE HOGAR\"', 14, 2022, 62, '40d2bdf5f5e13f0426b8ce5cf68a711c.pdf', 'AC', 57, '2022-05-06', 645840);
INSERT INTO `resoluciones_juridica` VALUES (158, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°15/22 DE APROBACIÃ³N DE 121 NUEVOS ASOCIADOS A LA COOPERATIVA', 15, 2022, 62, 'af526dd3c43fdd5d8a1392b52d52e64a.pdf', 'AC', 57, '2022-09-23', 645841);
INSERT INTO `resoluciones_juridica` VALUES (159, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°16/22 DE APROBACIÃ³N DE 109 NUEVOS ASOCIADOS A LA COOPERATIVA ', 16, 2022, 62, '81cfea1d57361a7d436fbbdfd4e8de98.pdf', 'AC', 57, '2022-11-04', 645843);
INSERT INTO `resoluciones_juridica` VALUES (160, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°17/22 DE REALIZAR DE FORMA TÃ©CNICA LOS AJUSTES NECESARIOS AL SISTEMA DE CPI, LA TAZA DE REGULACIÃ³N DE DESVINCULACIÃ³N EN EL REGISTRO DE MÃ³DULO DE CARTERA', 17, 2022, 62, 'cbb3b959f36297208232a3d645600061.pdf', 'AC', 57, '2022-11-04', 645844);
INSERT INTO `resoluciones_juridica` VALUES (161, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°18/22 DE IMPLANTAR EL DESCUENTO DE BS. 3 COMO PAGO ÃºNICO A LAS ASOCIADAS Y ASOCIADOS QUE REALICEN UNA SOLICITUD DE PRÃ©STAMO ', 18, 2022, 62, '3f013a6f94610d4426c34f9c23f733f9.pdf', 'AC', 57, '2022-11-04', 645845);
INSERT INTO `resoluciones_juridica` VALUES (162, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°19/22 DE LA DISTRIBUCIÃ³N EQUITATIVA, SOLIDARIA E INCLUSIVA DE LAS OBLIGACIONES CON LAS ASOCIADAS Y ASOCIADOS AL MES DE NOVIEMBRE DE 2022', 19, 2022, 62, '9ed516f0d1d0440d425f1d53d5d6c219.pdf', 'AC', 57, '2022-12-05', 645846);
INSERT INTO `resoluciones_juridica` VALUES (163, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°21/22 DE AUTORIZACIÃ³N DE LA ADQUISICIÃ³N DEL INMUEBLE PARA OFICINA', 21, 2022, 62, '4862b922da902f97964420a9db7ec039.pdf', 'AC', 57, '2022-12-30', 645847);
INSERT INTO `resoluciones_juridica` VALUES (164, 'RESOLUCIÃ³N DE CONSEJO DE ADMINISTRACIÃ³N NÂ°22/22 DE REALIZAR EL CIERRE CONTABLE DE LA GESTIÃ³N AL 31 DE DICIEMBRE DE 2022', 22, 2022, 62, '3fc86095fd3f7b438269f39e12186894.pdf', 'AC', 57, '2022-12-30', 645848);
INSERT INTO `resoluciones_juridica` VALUES (165, 'RESOLUCIÃ³N ADMINISTRATIVA (COMITÃ© DE CRÃ©DITO) NÂ°01/22 DE APROBACIÃ³N DE SOLICITUDES DE PRESTAMOS REGULARES Y DE EMERGENCIA DE LA RELACIÃ³N NOMINAL DE PRESTAMOS CORRESPONDIENTE A ENERO DE 2022', 1, 2022, 62, 'dd8179c4ba65ec2f741ea9b6bd5a7a00.pdf', 'AC', 58, '2022-01-14', 645849);
INSERT INTO `resoluciones_juridica` VALUES (166, 'RESOLUCIÃ³N ADMINISTRATIVA (COMITÃ© DE CRÃ©DITO) NÂ°02/22 DE APROBACIÃ³N DE SOLICITUDES DE PRESTAMOS REGULARES Y DE EMERGENCIA DE LA RELACIÃ³N NOMINAL DE PRESTAMOS CORRESPONDIENTE A FEBRERO DE 2022', 2, 2022, 62, 'f864f5a35fd1aad2ad88ea3115be35e7.pdf', 'AC', 0, '2022-02-01', 645850);
INSERT INTO `resoluciones_juridica` VALUES (167, 'RESOLUCIÃ³N ADMINISTRATIVA (COMITÃ© DE CRÃ©DITO) NÂ°02/22 DE APROBACIÃ³N DE SOLICITUDES DE PRESTAMOS REGULARES Y DE EMERGENCIA DE LA RELACIÃ³N NOMINAL DE PRESTAMOS CORRESPONDIENTE A FEBRERO DE 2022', 2, 2022, 62, '63f00b03f4f3485b98bee88e0d40c6a0.pdf', 'AC', 58, '2022-02-01', 645851);
INSERT INTO `resoluciones_juridica` VALUES (168, 'RESOLUCIÃ³N DE COMITÃ© DE CRÃ©DITO NÂ°03/22 DE AUTORIZA DE MANERA EXCEPCIONAL EL DESCUENTO POR LA PRIMERA CUOTA SIN LA PRESENTACIÃ³N FÃ­SICA DE LA PAPELETA DE PAGO DEL MES DE ENERO EN FAVOR DE SUBTENIENTE EMERSON VILLANUEVA JALLAZA', 3, 2022, 62, '4fe78e902e8b2b23ba92b3fb58eceb5e.pdf', 'AC', 58, '2022-02-10', 645852);
INSERT INTO `resoluciones_juridica` VALUES (169, 'RESOLUCIÃ³N DE COMITÃ© DE CRÃ©DITO NÂ°04/22 DE APROBACIÃ³N DE SOLICITUDES DE PRÃ©STAMOS REGULARES Y DE EMERGENCIA DE LA RELACIÃ³N NOMINAL DE PRÃ©STAMOS CORRESPONDIENTE A MARZO DE 2022', 4, 2022, 62, '4f3a77bdecabd5e4aced336dec858cf2.pdf', 'AC', 58, '2022-03-01', 645853);
INSERT INTO `resoluciones_juridica` VALUES (170, 'RESOLUCIÃ³N DE COMITÃ© DE CRÃ©DITO NÂ°05/22 DE APROBACIÃ³N DE SOLICITUDES DE PRÃ©STAMOS REGULARES Y DE EMERGENCIA DE LA RELACIÃ³N NOMINAL DE PRÃ©STAMOS CORRESPONDIENTE A ABRIL DE 2022', 5, 2022, 62, '1a3f2b93e7565a2595242fac12f244e7.pdf', 'AC', 58, '2022-04-01', 645854);
INSERT INTO `resoluciones_juridica` VALUES (171, 'RESOLUCIÃ³N DE COMITÃ© DE CRÃ©DITO NÂ°06/22 DE APROBACIÃ³N DE SOLICITUDES DE PRÃ©STAMOS REGULARES Y DE EMERGENCIA DE LA RELACIÃ³N NOMINAL DE PRÃ©STAMOS CORRESPONDIENTE A MAYO DE 2022', 6, 2022, 62, '629b72419f0f98454c7b169a503d8896.pdf', 'AC', 58, '2022-05-03', 645855);
INSERT INTO `resoluciones_juridica` VALUES (172, 'RESOLUCIÃ³N DE COMITÃ© DE CRÃ©DITO NÂ°07/22 DE AUTORIZACIÃ³N DE MANERA EXCEPCIONAL EL DESCUENTO MENSUAL POR EL PRÃ©STAMO DE EMERGENCIA AL SEÃ±OR GASTÃ³N SERRANO RUIZ, DESVINCULADO A LA COOPERATIVA HASTA EL CUMPLIMIENTO DEL PAGO TOTAL.', 7, 2022, 62, '3e3713981b1e106c0444aae2443163f9.pdf', 'AC', 58, '2022-06-08', 645856);
INSERT INTO `resoluciones_juridica` VALUES (173, 'RESOLUCIÃ³N COMITÃ© DE CRÃ©DITO NÂ° 09/22 DE AUTORIZACIÃ³N AL CNL. DAEN JUAN PABLO PINO GUTIÃ©RREZ FUNGIR CON SUS PROPIOS APORTES COMO SEGUNDO GARANTE PARA EFECTUARLE EL DESEMBOLSO POR UN MONTO DE $US. 23.000.- ', 9, 2022, 62, '6b9e83099c314b683d36de56b979df5d.pdf', 'AC', 58, '2022-02-11', 645888);
INSERT INTO `resoluciones_juridica` VALUES (174, 'RESOLUCION COMITE DE CREDITO NÂ°10/22 DE AUTORIZA EL PRESTAMO DE EMERGENCIA Y DESEMBOLSO CON PRIORIDAD A FAVOR DE ANTONIO NELSON ENCINAS BACARREZA POR MOTIVOS DE SALUD', 10, 2022, 62, '1e20201515dc77aca5d1b0a73bcc76ce.pdf', 'AC', 58, '2022-08-11', 645889);
INSERT INTO `resoluciones_juridica` VALUES (175, 'RESOLUCION COMITE DE CREDITO NÂ°11/22 DE APROBAR LA SOLICITUD DE PRESTAMOS REGULARES Y DE EMERGENCIA CORRESPONDIENTE AL MES DE JUNIO DE 2022', 11, 2022, 62, '2305ab0786b421ce73f7e679beb2101b.pdf', 'AC', 58, '2022-06-02', 645890);
INSERT INTO `resoluciones_juridica` VALUES (176, 'RESOLUCION COMITE DE CREDITO NÂ°12/22 DE AUTORIZA EL PRESTAMO DE EMERGENCIA Y EL DESEMBOLSO EN FAVOR DE ARIEL MARCELO RAMOS LUNA POR MOTIVOS DE SALUD', 12, 2022, 62, '0380d45c8a5c20ca02796a55bce755b4.pdf', 'AC', 58, '2022-08-30', 645891);
INSERT INTO `resoluciones_juridica` VALUES (177, 'RESOLUCION COMITE DE CREDITO NÂ°13/22 DE APROBAR LA SOLICITUD DE PRESTAMOS REGULARES Y DE EMERGENCIA CORRESPONDIENTE AL MES DE JULIO DE 2022', 13, 2022, 62, '4ecdff62f3e77fbecdbdfd8952e1e6f2.pdf', 'AC', 58, '2022-07-04', 645892);
INSERT INTO `resoluciones_juridica` VALUES (178, 'RESOLUCION COMITE DE CREDITO NÂ°14/22 DE AUTORIZA AL SOF 2SO DEPSS NESTOR GUACHALLA SURCO SE OTROGA DE FORMA EXCEPCIONAL  PRESTAMO DE EMERGENCIA DE $US. 2000', 14, 2022, 62, 'dc068a5c36ac87aff45afbf747682074.pdf', 'AC', 58, '2022-09-08', 645893);
INSERT INTO `resoluciones_juridica` VALUES (179, 'RESOLUCION COMITE DE CREDITO NÂ°15/22 DE APROBAR LA SOLICITUD DE PRESTAMOS REGULARES Y DE EMERGENCIA CORRESPONDIENTE AL MES DE AGOSTO DE 2022', 15, 2022, 62, 'fe16a50519a8c0790cca2c90132a33fc.pdf', 'AC', 58, '2022-08-01', 645894);
INSERT INTO `resoluciones_juridica` VALUES (180, 'RESOLUCION COMITE DE CREDITO NÂ°16/22 DE AAUTORIZACION AL SEÃ‘OR LUIS FERNANDO ORTIZ ANTEZANA OBTENER UN CREDITO PARALELO COMO PRESTAMO REGULAR EXCEPCIONAL  POR UNICA VEZ', 16, 2022, 0, 'd766234b80d34d6ecc4b70177fbd8e49.pdf', 'AC', 58, '2022-09-19', 645895);
INSERT INTO `resoluciones_juridica` VALUES (181, 'RESOLUCION COMITE DE CREDITO NÂ°16/22 DE AUTORIZAR AL SEÃ‘OR LUIS FERNANDO ORTIZ ANTEZANA OBTENER UN CREDITO PARALELO COMO PRESTAMO REGULAR EXCEPCIONAL POR UNICA VEZ', 16, 2022, 62, '54adc7eb293e260f023174195c12262e.pdf', 'AC', 58, '2022-09-19', 645896);
INSERT INTO `resoluciones_juridica` VALUES (182, 'RESOLUCION COMITE DE CREDITO NÂ°17/22 DE AUTORIZA EL CREDITO HIPOTECARIO DE VIVIENDA DENTRO DEL MARCO DEL CONVENIO EN EL QUE SE DESARROLLA EL PROYECTO PILOTO HOGAR DULCE HOGAR', 17, 2022, 62, '075f5992e30016386be4dacbfec53c1b.pdf', 'AC', 58, '2022-08-19', 645897);
INSERT INTO `resoluciones_juridica` VALUES (183, 'RESOLUCION COMITE DE CREDITO NÂ°18/22 DE APROBAR LA SOLICITUD DE PRESTAMOS REGULARES Y DE EMERGENCIA CORRESPONDIENTE AL MES DE SEPTIEMBRE DE 2022', 18, 2022, 62, 'd7613a7d3b52ffdeb166859b1ea9c91a.pdf', 'AC', 58, '2022-09-02', 645898);
INSERT INTO `resoluciones_juridica` VALUES (184, 'RESOLUCION COMITE DE CREDITO NÂ°19/22 DE AUTORIZA AL SEÃ‘OR IVAN HILARION ALCALA CRESPO COMPENSAR CON SUS APORTES LAS CUOTAS POR PAGAR DEL PRESATMO REGULAR VIGENTE', 19, 2022, 62, 'cb5060d025367bc81da2f7015a7b7810.pdf', 'AC', 58, '2022-10-10', 645899);
INSERT INTO `resoluciones_juridica` VALUES (185, 'RESOLUCION COMITE DE CREDITO NÂ°20/22 DE INSTRUIR AL ENCARGADO DE SISTEMAS AJUSTAR LAS CUOTAS DE LOS PLANES DE PAGO A PARTIR DE LA CUOTA 1 EN ADELANTE', 20, 2022, 62, 'cb87e6fe8b14692ff2d9be5edb177edc.pdf', 'AC', 58, '2022-10-10', 645900);
INSERT INTO `resoluciones_juridica` VALUES (186, 'RESOLUCION COMITE DE CREDITO NÂ°21/22 DE APROBAR LA SOLICITUD DE PRESTAMOS REGULARES Y DE EMERGENCIA CORRESPONDIENTE AL MES DE OCTUBRE DE 2022', 21, 2022, 62, '19652324b01932b24ee9c3439e47f9a4.pdf', 'AC', 58, '2022-10-03', 645901);
INSERT INTO `resoluciones_juridica` VALUES (187, 'RESOLUCION COMITE DE CREDITO NÂ°22/22 DE APROBAR LA SOLICITUD DE PRESTAMOS REGULARES Y DE EMERGENCIA CORRESPONDIENTE AL MES DE NOVIEMBRE DE 2022', 22, 2022, 62, '4d8d6e7d8b2201bb1d1a3cc5d2e6b94a.pdf', 'AC', 58, '2022-11-03', 645902);
INSERT INTO `resoluciones_juridica` VALUES (188, 'RESOLUCION COMITE DE CREDITO NÂ°23/22 DE APROBAR LA SOLICITUD DE PRESTAMOS REGULARES Y DE EMERGENCIA CORRESPONDIENTE AL MES DE DICIEMBRE DE 2022', 23, 0, 0, '', 'AC', 0, '0000-00-00', 645903);
INSERT INTO `resoluciones_juridica` VALUES (189, 'RESOLUCION COMITE DE CREDITO NÂ°23/22 DE APROBAR LA SOLICITUD DE PRESTAMOS REGULARES Y DE EMERGENCIA CORRESPONDIENTE AL MES DE DICIEMBRE DE 2022', 23, 2022, 62, 'd3328240c74326fb3de4e14f194357cd.pdf', 'AC', 58, '2022-12-02', 645904);
INSERT INTO `resoluciones_juridica` VALUES (190, 'RESOLUCION ADMINISTRATIVA NÂ°01/22 DE CONCIDERACION DE  ACEPTACION Y APROBACION DEL PERSONAL AL REGLAMENTO INTERNO ', 1, 2022, 62, '0a1fb1debec8898dca80e9f6768ea6a7.pdf', 'AC', 58, '2022-08-22', 645905);
INSERT INTO `resoluciones_juridica` VALUES (191, 'RESOLUCION ADMINISTRATIVO NÂ°02/22 DE AUTORIZACION DE PRACTICAS EMPRESARIALES ', 2, 2022, 62, '806de07ca50453f51b00baf01f063460.pdf', 'AC', 58, '2022-09-01', 645906);
INSERT INTO `resoluciones_juridica` VALUES (192, 'RESOLUCION ADMINISTRATIVA NÂ°04/22 DE CONCIDERAR LA ACEPTACION Y APROBACION DE LOS ENCARGADOS DEL AREA RESPECTO AL MANUAL DE FUNCIONES ', 4, 2022, 62, 'ff7370679b27b6dc6dc964eba2a39540.pdf', 'AC', 58, '2022-10-17', 645907);
INSERT INTO `resoluciones_juridica` VALUES (193, 'RES AFCOOP DE INCLUSIÃ³N DE 115 NUEVOS ASOCIADOS Y ASOCIADOS A LA COOPERATIVA APÃ³STOL SANTIAGO R.L.', 1262, 2023, 62, '00dcc8718de6de6b866eaa5d38a1a366.pdf', 'AC', 59, '2023-08-04', 648918);
INSERT INTO `resoluciones_juridica` VALUES (194, 'RESOLUCIÃ³N  AFCOOP DE ADMISIÃ³N DE 100 NUEVOS ASOCIADOS', 1670, 23, 62, 'dfd58e4c07acd659ae62862910ca69dc.pdf', 'AC', 59, '2023-10-02', 652057);
INSERT INTO `resoluciones_juridica` VALUES (195, 'RESOLUCIÃ³N AFCOOP DE PÃ©RDIDA DE CALIDAD DE 17 ASOCIADOS DE LA COOPERATIVA ', 1675, 2023, 62, '398faf64f5e1ed5f2990855910186196.pdf', 'AC', 59, '2023-10-03', 652058);
INSERT INTO `resoluciones_juridica` VALUES (196, 'RESOLUCION AFCOOP REGISTRO DE INCLUSION  DE 56 ASOCIADOS DE LA COOPERATIVA', 171, 2024, 62, '7effca0aaf670c11924db9da29d09aa5.pdf', 'AC', 59, '2024-01-31', 687488);
INSERT INTO `resoluciones_juridica` VALUES (197, '', 331, 2024, 0, '3b53291122de8270d14784e5519a9343.pdf', 'AC', 0, '2024-01-31', 748901);
INSERT INTO `resoluciones_juridica` VALUES (198, 'RESOLCUION AFCOOP NUEVOS ASOCIADOS ', 331, 2024, 0, '7b329a3b552a982124e13b122f9aaee1.pdf', 'AC', 0, '2024-03-04', 749373);
INSERT INTO `resoluciones_juridica` VALUES (199, 'RS 331 ', 331, 2024, 56, 'f670d3755e80323cf74f37251dfcb836.pdf', 'AC', 59, '2024-03-04', 784922);
INSERT INTO `resoluciones_juridica` VALUES (200, 'RS 1065 INCLUSION DE ASOCIADOS 84', 0, 2024, 0, '', 'AC', 0, '2024-03-04', 750719);
INSERT INTO `resoluciones_juridica` VALUES (201, 'RESOLCUION 1065 INCLUCION DE NUEVOS ASOCIADOS  87', 1065, 2024, 56, '0e02158b61b6cb9e630f3b7ef1ad2b07.pdf', 'AC', 59, '2024-06-24', 784923);
INSERT INTO `resoluciones_juridica` VALUES (202, 'CERTIFICADO DE INCLUSION DE ASOCIADOS Y ASOCIADAS \nREGISTRADO EN EL REC CON EL NO 02.02.0669\n', 71, 2024, 56, '4deb84692433a593bd82ed18c539263a.pdf', 'AC', 0, '2024-09-23', 750723);
INSERT INTO `resoluciones_juridica` VALUES (203, 'CERTIFICADO DE INCLUSION DE ASOCIADOS (AS)\nREGISTRO EN EL REC CON EL NO 02.02.0669', 117, 2024, 56, '56f83a6c32b3044aa48a34cbcc9cb93c.pdf', 'AC', 59, '2024-09-23', 784924);
INSERT INTO `resoluciones_juridica` VALUES (204, 'PERDIDA POR RETIROS VOLUNTARIOS \nPERDIDA POR FALLECIMIENTO\n', 1888, 2024, 54, 'cd963dca789040d889ecb55825b7c990.pdf', 'AC', 59, '2024-11-22', 783869);
INSERT INTO `resoluciones_juridica` VALUES (205, 'CERTIFICADO DE INCLUCION DE ASOCIADAS Y ASOCIADOS \nNUMERO DE INCLUIDOS 264\nREGISTRADOS EN EL REC CON EL 02.02.0669\n', 0, 2025, 56, '23fc121f7efb2d02b2fa773683818ddd.pdf', 'AC', 0, '2025-03-13', 800667);
INSERT INTO `resoluciones_juridica` VALUES (206, 'CERTIFICADO DE INCLUCION NUEVOS ASOCIADOS (AS) DE FECHA 13 DE MARZO DE 2025', 0, 2025, 0, 'd76728faf7817f5f98ed8fce0a6ffcf6.pdf', 'AC', 0, '2025-03-13', 800668);
INSERT INTO `resoluciones_juridica` VALUES (207, 'CERTIFICADO DE INCLUSION DE ASOCIADOS (AS) DE FECHA 13 DE MARZO DE 2025 ', 0, 2025, 0, '5004913de4d782847b462bfc0f281f25.pdf', 'AC', 59, '2025-03-13', 800669);
INSERT INTO `resoluciones_juridica` VALUES (208, 'INCLUSION DE ASOCIADAS (OS) DE FECHA 13 DE MARZO DE 2025 IA-93', 93, 2025, 54, 'd5cca0ea9c252aea3b59ab7c144d9ae7.pdf', 'AC', 59, '2025-03-13', 800671);
INSERT INTO `resoluciones_juridica` VALUES (209, 'CERTIFICADO DE INCLUSION DE ASOCIADOS IA496/2025', 496, 2025, 0, '6b890f5f349ac4f90b529455f9447ffd.pdf', 'AC', 0, '2025-12-15', 882939);
INSERT INTO `resoluciones_juridica` VALUES (210, 'REGISTRO DE INCLUCION DE 72 NUEVOS ASOCIADOS (AS)', 0, 2025, 56, '7fc0a5d1501b5ac67bc5f5dc1e029fea.pdf', 'AC', 0, '2025-12-15', 882940);
INSERT INTO `resoluciones_juridica` VALUES (211, 'INCLUSION DE  72 NUEVOS ASOCIAODS ', 0, 2025, 54, 'f11b4242d15fbe8b1ddcdcc3a9e59bfe.pdf', 'AC', 0, '2025-12-15', 882941);
INSERT INTO `resoluciones_juridica` VALUES (212, 'NUEVA INCLUSÃ“N 72 ASOCIADOS', 0, 2025, 56, '1ba9a842be3194aea0d006a5f4084a43.pdf', 'AC', 0, '2025-12-12', 882942);
INSERT INTO `resoluciones_juridica` VALUES (213, 'NUEVOS ASOCIADOS', 496, 2025, 56, '82e25ca417b2f0413238f5104ef2df06.pdf', 'AC', 59, '2025-12-12', 882943);
INSERT INTO `resoluciones_juridica` VALUES (214, 'DESVINCULACIÃ“N DE ASOCIADOS', 1808, 2025, 56, 'ba10d9e94a5d19f753f6f919c2f30388.pdf', 'AC', 59, '2025-12-29', 882944);

-- ----------------------------
-- Table structure for role_has_permissions
-- ----------------------------
DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions`  (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`) USING BTREE,
  INDEX `role_has_permissions_role_id_foreign`(`role_id`) USING BTREE,
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of role_has_permissions
-- ----------------------------
INSERT INTO `role_has_permissions` VALUES (1, 1);
INSERT INTO `role_has_permissions` VALUES (2, 1);
INSERT INTO `role_has_permissions` VALUES (3, 1);
INSERT INTO `role_has_permissions` VALUES (4, 1);
INSERT INTO `role_has_permissions` VALUES (5, 1);
INSERT INTO `role_has_permissions` VALUES (6, 1);
INSERT INTO `role_has_permissions` VALUES (7, 1);
INSERT INTO `role_has_permissions` VALUES (8, 1);
INSERT INTO `role_has_permissions` VALUES (9, 1);
INSERT INTO `role_has_permissions` VALUES (10, 1);
INSERT INTO `role_has_permissions` VALUES (11, 1);
INSERT INTO `role_has_permissions` VALUES (12, 1);
INSERT INTO `role_has_permissions` VALUES (13, 1);
INSERT INTO `role_has_permissions` VALUES (14, 1);
INSERT INTO `role_has_permissions` VALUES (15, 1);
INSERT INTO `role_has_permissions` VALUES (16, 1);
INSERT INTO `role_has_permissions` VALUES (17, 1);
INSERT INTO `role_has_permissions` VALUES (18, 1);
INSERT INTO `role_has_permissions` VALUES (19, 1);
INSERT INTO `role_has_permissions` VALUES (1, 3);
INSERT INTO `role_has_permissions` VALUES (2, 3);

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `roles_name_guard_name_unique`(`name`, `guard_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, 'Administrador', 'web', '2026-06-15 18:48:47', '2026-06-15 18:48:47');
INSERT INTO `roles` VALUES (2, 'Secretaria', 'web', '2026-06-17 12:23:39', '2026-06-17 12:23:39');
INSERT INTO `roles` VALUES (3, 'Operador-Sistemas', 'web', '2026-06-17 12:23:52', '2026-06-17 12:24:01');

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sessions_user_id_index`(`user_id`) USING BTREE,
  INDEX `sessions_last_activity_index`(`last_activity`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sessions
-- ----------------------------
INSERT INTO `sessions` VALUES ('udvCYXd1WBhwJivT9De8L4mIo1E8FrAv8GE7IgA3', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQ0RCWElOQm9lTm1lMFFLaDN2UThJWnhWNUdTNVZVQWFXUko5UHNNViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zb2Npb3MtcmVwb3J0ZXMiO3M6NToicm91dGUiO3M6MTU6InNvY2lvcy5yZXBvcnRlcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1782246626);

-- ----------------------------
-- Table structure for socio_institucion
-- ----------------------------
DROP TABLE IF EXISTS `socio_institucion`;
CREATE TABLE `socio_institucion`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_socio` int(10) NOT NULL,
  `papeleta` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `carnet_mil` int(15) NOT NULL,
  `cossmil` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `afil_mes` varchar(3) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `afil_anio` int(4) NOT NULL,
  `anio_prom` date NOT NULL,
  `id_escalafon` int(2) NOT NULL,
  `id_fuerza` int(2) NOT NULL,
  `id_arma` int(2) NOT NULL,
  `id_grado` int(2) NOT NULL,
  `id_diplomado` int(2) NOT NULL,
  `salario` decimal(10, 2) NOT NULL,
  `estado` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `devolAportes` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `devolCapitalizacion` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `idlog_coc` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `papeleta`(`papeleta`) USING BTREE,
  INDEX `id_socio`(`id_socio`) USING BTREE,
  INDEX `id_grado`(`id_grado`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of socio_institucion
-- ----------------------------
INSERT INTO `socio_institucion` VALUES (1, 4, '00077777', 88888, '9999999', 'JUN', 2026, '2007-05-14', 7, 1, 5, 40, 1, 8000.00, 'AC', '', '', 0);
INSERT INTO `socio_institucion` VALUES (2, 5, '99999', 88888, '77777', 'JUN', 2026, '2008-05-02', 7, 1, 5, 40, 1, 8000.00, 'AC', '', '', 0);
INSERT INTO `socio_institucion` VALUES (3, 6, '27311', 2222222, '3333333', 'MAR', 2025, '2009-12-12', 7, 5, 9, 43, 8, 6365.60, 'AC', '', '', 0);
INSERT INTO `socio_institucion` VALUES (4, 7, '909090', 808080, '707070', 'JUL', 2025, '2005-05-12', 1, 3, 9, 39, 2, 7365.60, 'AC', '', '', 0);
INSERT INTO `socio_institucion` VALUES (5, 8, '00024607', 88888, '855201', 'ENE', 2026, '1999-12-12', 7, 5, 16, 18, 1, 6365.60, 'AC', '', '', 0);

-- ----------------------------
-- Table structure for socios
-- ----------------------------
DROP TABLE IF EXISTS `socios`;
CREATE TABLE `socios`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(35) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `paterno` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `materno` varchar(25) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `nro_doc` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL COMMENT 'nro de ci incluyendo el nro complementario',
  `expedido` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `sexo` varchar(1) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `fecha_nac` date NULL DEFAULT NULL,
  `estado_civil` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `foto` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `estado` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `num_correlativo` int(5) NOT NULL DEFAULT 0 COMMENT 'numero correlativo para socios y tener un control en kardex',
  `estado_kardex` char(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL DEFAULT '' COMMENT 'para el estado de kardex KN=kardex desactualizado, KI=kardex impreso o actualizado',
  `mindef` char(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL COMMENT 'BA=baja ministerio de defensa',
  `id_socio_anterior` int(11) NULL DEFAULT NULL,
  `id_socio_origen` int(11) NULL DEFAULT NULL,
  `es_revinculacion` tinyint(1) NOT NULL DEFAULT 0,
  `cantidad_revinculaciones` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_revinculacion` datetime NULL DEFAULT NULL,
  `usuario_revinculacion` int(11) NULL DEFAULT NULL,
  `observacion_revinculacion` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NULL DEFAULT NULL,
  `vinculacion_actual` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_vinculacion_actual`(`vinculacion_actual`) USING BTREE,
  INDEX `idx_id_socio_anterior`(`id_socio_anterior`) USING BTREE,
  INDEX `idx_id_socio_origen`(`id_socio_origen`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of socios
-- ----------------------------
INSERT INTO `socios` VALUES (4, 'OLKER', 'CHOQUE', 'GONZALES', '77777777', 'LP', 'M', '1996-02-11', 'CA', '1781877262_cas_log_dos.png', 'AC', 0, 'AC', 'NO', NULL, NULL, 0, 0, NULL, NULL, NULL, 1);
INSERT INTO `socios` VALUES (5, 'JOSE FAISAL', 'DROGUETT', 'VARGAS', '4273955', 'LP', 'M', '1982-03-27', 'CA', '1781880796_cas.jpeg', 'AC', 0, 'AC', 'NO', NULL, NULL, 0, 0, NULL, NULL, NULL, 1);
INSERT INTO `socios` VALUES (6, 'PEDRO', 'CALCINA', 'PEREYRA', '77777777', 'CB', 'M', '1975-02-15', 'VI', '1781881414_cas_log_dos.png', 'AC', 0, 'AC', 'NO', NULL, NULL, 0, 0, NULL, NULL, NULL, 1);
INSERT INTO `socios` VALUES (7, 'LEIDY MABEL', 'MARIACA', 'MEALLA', '6135131', 'LP', 'F', '1984-05-27', 'VI', '1782228699_login.png', 'AC', 0, 'AC', 'NO', NULL, NULL, 0, 0, NULL, NULL, NULL, 1);
INSERT INTO `socios` VALUES (8, 'DONALD', 'THE DUCK', 'DISNEY', '232323232323232', 'LP', 'M', '1984-05-27', 'SO', '1782229179_login.png', 'AC', 0, 'AC', 'NO', NULL, NULL, 0, 0, NULL, NULL, NULL, 1);

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_email_unique`(`email`) USING BTREE,
  UNIQUE INDEX `users_username_unique`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', 'Administrador del Sistema', 'admin@cas.local', NULL, '$2y$12$ipRmY12rvXFv/PEzC4U2CuB5x5nyHmW8YOTZ4Ik9jNDCymGJuDhsK', 'ACTIVO', '2026-06-23 20:08:01', NULL, '2026-06-15 18:48:47', '2026-06-23 20:08:01');
INSERT INTO `users` VALUES (4, 'jose.droguett', 'Jose Faisal Droguett Vargas', 'josedrgttv30@gmail.com', NULL, '$2y$12$BYataApllG1gNz7hqLriauAMvumuRiEJSwhYonEpRW8d4WjsPyJT6', 'ACTIVO', NULL, NULL, '2026-06-16 18:39:52', '2026-06-16 20:28:35');
INSERT INTO `users` VALUES (5, 'luis.perez', 'Luisito Perez Gonzales', 'rrhh@gmail.com', NULL, '$2y$12$CXLcMKqhsLQOfTd3ARFmeOAEd2TOK4vrMAoa3w62dF8MbuavWa.9O', 'ACTIVO', '2026-06-17 14:40:08', NULL, '2026-06-16 18:54:04', '2026-06-17 14:40:08');
INSERT INTO `users` VALUES (6, 'araceli.machaca', 'Araceli Machaca Hilari', 'rrhh22@gmail.com', NULL, '$2y$12$8EokXakw0Gjn0z3IY80kQeH/ps8jPljpoAiufBftESWeByP5lUOKS', 'ACTIVO', '2026-06-17 14:41:41', NULL, '2026-06-17 13:14:14', '2026-06-17 14:41:41');

SET FOREIGN_KEY_CHECKS = 1;
