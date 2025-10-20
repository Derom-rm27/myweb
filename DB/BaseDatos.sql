/* =========================================================
   BASE COMPLETA myweb
   Incluye esquema original y nuevas tablas de niveles/roles
   ========================================================= */

CREATE DATABASE IF NOT EXISTS myweb
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;
USE myweb;

SET FOREIGN_KEY_CHECKS=0;

-- =========================================================
-- 1) ESQUEMA ORIGINAL
-- =========================================================

DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  usersId      INT(9) NOT NULL AUTO_INCREMENT,
  grupoId      INT(9) NOT NULL,
  nombres      VARCHAR(150) NOT NULL,
  users        VARCHAR(20) NOT NULL,
  clave        VARCHAR(120) NOT NULL,
  nivel        TINYINT UNSIGNED NOT NULL,
  estado       TINYINT(1) NOT NULL DEFAULT 1,
  email        VARCHAR(100) DEFAULT NULL,
  perfil       VARCHAR(150) DEFAULT NULL,
  fechaCreada  DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usersId),
  UNIQUE KEY uk_usuarios_users (users),
  KEY idx_usuarios_email (email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO usuarios (grupoId, nombres, users, clave, nivel, estado, email, perfil, fechaCreada)
VALUES (1, 'Super Admin', 'root', 'admin', 1, 1, 'admin@local', '', NOW());

INSERT INTO usuarios (grupoId, nombres, users, clave, nivel, estado, email, perfil, fechaCreada)
VALUES (1, 'Editora Noticias', 'news_editor', 'editor123', 3, 1, 'news@example.com', '', NOW())
ON DUPLICATE KEY UPDATE nivel=VALUES(nivel), estado=VALUES(estado);

DROP TABLE IF EXISTS grupos;
CREATE TABLE grupos (
  grupoId      INT(9) NOT NULL AUTO_INCREMENT,
  usersId      INT(9) NOT NULL,
  nombreGrupo  VARCHAR(255) DEFAULT NULL,
  fechaInicio  DATE NOT NULL,
  fechaFinal   DATE NOT NULL,
  PRIMARY KEY (grupoId)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO grupos (usersId, nombreGrupo, fechaInicio, fechaFinal)
VALUES (1, 'Administrador Financiero Portable', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR));

DROP TABLE IF EXISTS banner;
CREATE TABLE banner (
  idBanner   INT(9) NOT NULL AUTO_INCREMENT,
  usersId    INT(9) NOT NULL,
  Titulo     VARCHAR(250) DEFAULT NULL,
  Describir  VARCHAR(250) DEFAULT NULL,
  Enlace     VARCHAR(250) DEFAULT NULL,
  Imagen     VARCHAR(100) NOT NULL,
  estado     TINYINT(1) NOT NULL DEFAULT 0,
  fecha      DATETIME NOT NULL,
  PRIMARY KEY (idBanner)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS creabaner;
CREATE TABLE creabaner (
  idCreaBan   INT(9) NOT NULL AUTO_INCREMENT,
  idWeb       INT(9) NOT NULL,
  nombre      VARCHAR(250) DEFAULT NULL,
  codigoCall  VARCHAR(250) DEFAULT NULL,
  codigo      TEXT NOT NULL,
  usersId     INT(9) NOT NULL,
  PRIMARY KEY (idCreaBan)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP PROCEDURE IF EXISTS Acceder;
DELIMITER $$
CREATE PROCEDURE Acceder(IN Usuario VARCHAR(100), IN Claves VARCHAR(200))
BEGIN
    DECLARE rpta VARCHAR(20) DEFAULT NULL;
    DECLARE IdGrupo INT DEFAULT 0;
    DECLARE IdUser INT DEFAULT 0;

    SELECT usersId, grupoId INTO IdUser, IdGrupo 
    FROM usuarios 
    WHERE users = Usuario AND clave = Claves AND estado = 1;

    IF IdGrupo = 0 THEN
        SELECT 'No Existe' AS usersId;
    ELSE
        SELECT usersId INTO rpta 
        FROM grupos 
        WHERE 0 < DATEDIFF(fechaFinal, NOW()) AND grupoId = IdGrupo;

        IF rpta IS NOT NULL THEN
            SELECT usersId, grupoId, nombres, users, nivel  
            FROM usuarios 
            WHERE usersId = IdUser 
            LIMIT 1;
        ELSE 
            SELECT 'No Existe' AS usersId;
        END IF;
    END IF;
END$$
DELIMITER ;

-- =========================================================
-- 2) NUEVAS TABLAS
-- =========================================================

-- Niveles
DROP TABLE IF EXISTS niveles;
CREATE TABLE niveles (
  nivel       TINYINT UNSIGNED PRIMARY KEY,
  codigo      VARCHAR(40) NOT NULL UNIQUE,
  nombre      VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO niveles (nivel, codigo, nombre, descripcion) VALUES
  (1,'SUPER','Superusuario','Acceso total y puede dar permisos'),
  (2,'BANNER_ONLY','Solo Banners','Puede insertar banners'),
  (3,'NEWS_ONLY','Solo Noticias','Puede insertar noticias')
ON DUPLICATE KEY UPDATE codigo=VALUES(codigo);

-- Recursos
DROP TABLE IF EXISTS recursos;
CREATE TABLE recursos (
  idRecurso   SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo      VARCHAR(50) NOT NULL UNIQUE,
  nombre      VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Acciones
DROP TABLE IF EXISTS acciones;
CREATE TABLE acciones (
  idAccion    TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo      VARCHAR(50) NOT NULL UNIQUE,
  nombre      VARCHAR(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Permisos por nivel
DROP TABLE IF EXISTS permisos_nivel;
CREATE TABLE permisos_nivel (
  nivel      TINYINT UNSIGNED NOT NULL,
  idRecurso  SMALLINT UNSIGNED NOT NULL,
  idAccion   TINYINT UNSIGNED NOT NULL,
  permitido  TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (nivel, idRecurso, idAccion)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Overrides por usuario
DROP TABLE IF EXISTS user_permisos;
CREATE TABLE user_permisos (
  usersId   INT(9) NOT NULL,
  recurso   VARCHAR(50) NOT NULL,
  accion    VARCHAR(50) NOT NULL,
  permitido TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (usersId, recurso, accion)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Noticias
DROP TABLE IF EXISTS noticias;
CREATE TABLE noticias (
  idNoticia  INT(9) NOT NULL AUTO_INCREMENT,
  usersId    INT(9) NOT NULL,
  titulo     VARCHAR(250) NOT NULL,
  cuerpo     TEXT NOT NULL,
  imagen     VARCHAR(150) DEFAULT NULL,
  enlace     VARCHAR(500) DEFAULT NULL,
  estado     TINYINT(1) NOT NULL DEFAULT 1,
  fecha      DATETIME NOT NULL DEFAULT NOW(),
  PRIMARY KEY (idNoticia)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Registro de visitas al panel
DROP TABLE IF EXISTS registro_visitas;
CREATE TABLE registro_visitas (
  visitaId     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usersId      INT(9) NOT NULL,
  nivel        TINYINT UNSIGNED NOT NULL,
  dispositivo  VARCHAR(50) NOT NULL,
  navegador    VARCHAR(120) NOT NULL,
  ip           VARCHAR(45) NOT NULL DEFAULT '',
  user_agent   TEXT NOT NULL,
  fecha_visita DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (visitaId),
  KEY idx_registro_visitas_fecha (fecha_visita),
  KEY idx_registro_visitas_usuario (usersId)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- =========================================================
-- 3) SEED RECURSOS, ACCIONES Y PERMISOS
-- =========================================================

INSERT INTO acciones (codigo, nombre) VALUES
  ('VIEW','Ver'), ('CREATE','Crear'), ('UPDATE','Actualizar'),
  ('DELETE','Eliminar'), ('GRANT','Gestionar permisos')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

INSERT INTO recursos (codigo, nombre) VALUES
  ('DASHBOARD','Panel'),
  ('USERS','Usuarios'),
  ('BANNERS','Banners'),
  ('NEWS','Noticias')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);

-- SUPER (nivel 1): todo
INSERT IGNORE INTO permisos_nivel (nivel, idRecurso, idAccion, permitido)
SELECT 1, r.idRecurso, a.idAccion, 1 FROM recursos r CROSS JOIN acciones a;

-- NIVEL 2: BANNERS {VIEW, CREATE}
INSERT IGNORE INTO permisos_nivel (nivel, idRecurso, idAccion, permitido)
SELECT 2, r.idRecurso, a.idAccion, 1
FROM recursos r JOIN acciones a ON a.codigo IN ('VIEW','CREATE')
WHERE r.codigo='BANNERS';

-- NIVEL 3: NEWS {VIEW, CREATE}
INSERT IGNORE INTO permisos_nivel (nivel, idRecurso, idAccion, permitido)
SELECT 3, r.idRecurso, a.idAccion, 1
FROM recursos r JOIN acciones a ON a.codigo IN ('VIEW','CREATE')
WHERE r.codigo='NEWS';

-- =========================================================
-- 4) VISTAS
-- =========================================================

DROP VIEW IF EXISTS v_permisos_usuario;
CREATE VIEW v_permisos_usuario AS
SELECT u.usersId, u.users, u.nivel, r.codigo AS recurso, a.codigo AS accion, p.permitido
FROM usuarios u
JOIN permisos_nivel p ON p.nivel=u.nivel
JOIN recursos r ON r.idRecurso=p.idRecurso
JOIN acciones a ON a.idAccion=p.idAccion;

DROP VIEW IF EXISTS v_permisos_efectivos;
CREATE VIEW v_permisos_efectivos AS
-- overrides positivos
SELECT u.usersId, u.users, u.nivel, up.recurso, up.accion, up.permitido
FROM usuarios u
JOIN user_permisos up ON up.usersId=u.usersId
WHERE up.permitido=1
UNION
-- permisos por nivel donde no hay override
SELECT u.usersId, u.users, u.nivel, r.codigo, a.codigo, p.permitido
FROM usuarios u
JOIN permisos_nivel p ON p.nivel=u.nivel
JOIN recursos r ON r.idRecurso=p.idRecurso
JOIN acciones a ON a.idAccion=p.idAccion
LEFT JOIN user_permisos up ON up.usersId=u.usersId AND up.recurso=r.codigo AND up.accion=a.codigo
WHERE IFNULL(up.permitido,-1)=-1
UNION
-- overrides negativos
SELECT u.usersId, u.users, u.nivel, up.recurso, up.accion, up.permitido
FROM usuarios u
JOIN user_permisos up ON up.usersId=u.usersId
WHERE up.permitido=0;

-- =========================================================
-- 5) PROCEDIMIENTOS
-- =========================================================

-- RegistrarSelf: solo niveles 2 o 3
DROP PROCEDURE IF EXISTS RegistrarSelf;
DELIMITER $$
CREATE PROCEDURE RegistrarSelf(
  IN pNombres VARCHAR(150),
  IN pUsuario VARCHAR(20),
  IN pClave   VARCHAR(120),
  IN pEmail   VARCHAR(100),
  IN pNivel   TINYINT
)
BEGIN
  IF pNivel NOT IN (2,3) THEN
    SELECT 'ERROR' AS estado, 'Solo se permite registrarse en niveles 2 o 3' AS mensaje;
  ELSEIF EXISTS (SELECT 1 FROM usuarios WHERE users=pUsuario) THEN
    SELECT 'ERROR' AS estado, 'Nombre de usuario ya existe' AS mensaje;
  ELSEIF (pEmail IS NOT NULL AND pEmail<>'' AND EXISTS (SELECT 1 FROM usuarios WHERE email=pEmail)) THEN
    SELECT 'ERROR' AS estado, 'Email ya registrado' AS mensaje;
  ELSE
    INSERT INTO usuarios (grupoId,nombres,users,clave,nivel,estado,email,perfil,fechaCreada)
    VALUES (1,pNombres,pUsuario,pClave,pNivel,1,pEmail,'',NOW());
    SELECT 'OK' AS estado, LAST_INSERT_ID() AS usersId, pNivel AS nivel;
  END IF;
END$$
DELIMITER ;

-- RegistrarAdmin: solo super (nivel 1)
DROP PROCEDURE IF EXISTS RegistrarAdmin;
DELIMITER $$
CREATE PROCEDURE RegistrarAdmin(
  IN pAdminId INT,
  IN pNombres VARCHAR(150),
  IN pUsuario VARCHAR(20),
  IN pClave   VARCHAR(120),
  IN pEmail   VARCHAR(100),
  IN pNivel   TINYINT
)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM usuarios WHERE usersId=pAdminId AND nivel=1 AND estado=1) THEN
    SELECT 'ERROR' AS estado, 'Solo nivel 1 puede crear usuarios' AS mensaje;
  ELSEIF pNivel NOT IN (1,2,3) THEN
    SELECT 'ERROR' AS estado, 'Nivel inválido' AS mensaje;
  ELSEIF EXISTS (SELECT 1 FROM usuarios WHERE users=pUsuario) THEN
    SELECT 'ERROR' AS estado, 'Nombre de usuario ya existe' AS mensaje;
  ELSEIF (pEmail IS NOT NULL AND pEmail<>'' AND EXISTS (SELECT 1 FROM usuarios WHERE email=pEmail)) THEN
    SELECT 'ERROR' AS estado, 'Email ya registrado' AS mensaje;
  ELSE
    INSERT INTO usuarios (grupoId,nombres,users,clave,nivel,estado,email,perfil,fechaCreada)
    VALUES (1,pNombres,pUsuario,pClave,pNivel,1,pEmail,'',NOW());
    SELECT 'OK' AS estado, LAST_INSERT_ID() AS usersId, pNivel AS nivel;
  END IF;
END$$
DELIMITER ;

-- CambiarNivelUsuario
DROP PROCEDURE IF EXISTS CambiarNivelUsuario;
DELIMITER $$
CREATE PROCEDURE CambiarNivelUsuario(
  IN pAdminId INT,
  IN pUserId  INT,
  IN pNuevo   TINYINT
)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM usuarios WHERE usersId=pAdminId AND nivel=1 AND estado=1) THEN
    SELECT 'ERROR' AS estado, 'Solo nivel 1 puede cambiar niveles' AS mensaje;
  ELSEIF pNuevo NOT IN (1,2,3) THEN
    SELECT 'ERROR' AS estado, 'Nivel inválido' AS mensaje;
  ELSE
    UPDATE usuarios SET nivel=pNuevo WHERE usersId=pUserId;
    SELECT 'OK' AS estado, pUserId AS usersId, pNuevo AS nivel;
  END IF;
END$$
DELIMITER ;

-- ConcederPermisoUsuario
DROP PROCEDURE IF EXISTS ConcederPermisoUsuario;
DELIMITER $$
CREATE PROCEDURE ConcederPermisoUsuario(
  IN pAdminId INT,
  IN pUserId  INT,
  IN pRecurso VARCHAR(50),
  IN pAccion  VARCHAR(50)
)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM usuarios WHERE usersId=pAdminId AND nivel=1 AND estado=1) THEN
    SELECT 'ERROR' AS estado, 'Solo nivel 1 puede conceder permisos' AS mensaje;
  ELSEIF NOT EXISTS (SELECT 1 FROM recursos WHERE codigo=pRecurso) THEN
    SELECT 'ERROR' AS estado, 'Recurso desconocido' AS mensaje;
  ELSEIF NOT EXISTS (SELECT 1 FROM acciones WHERE codigo=pAccion) THEN
    SELECT 'ERROR' AS estado, 'Acción desconocida' AS mensaje;
  ELSE
    INSERT INTO user_permisos (usersId,recurso,accion,permitido)
    VALUES (pUserId,pRecurso,pAccion,1)
    ON DUPLICATE KEY UPDATE permitido=1;
    SELECT 'OK' AS estado, pUserId AS usersId, pRecurso AS recurso, pAccion AS accion, 1 AS permitido;
  END IF;
END$$
DELIMITER ;

-- RevocarPermisoUsuario
DROP PROCEDURE IF EXISTS RevocarPermisoUsuario;
DELIMITER $$
CREATE PROCEDURE RevocarPermisoUsuario(
  IN pAdminId INT,
  IN pUserId  INT,
  IN pRecurso VARCHAR(50),
  IN pAccion  VARCHAR(50)
)
BEGIN
  IF NOT EXISTS (SELECT 1 FROM usuarios WHERE usersId=pAdminId AND nivel=1 AND estado=1) THEN
    SELECT 'ERROR' AS estado, 'Solo nivel 1 puede revocar permisos' AS mensaje;
  ELSEIF NOT EXISTS (SELECT 1 FROM recursos WHERE codigo=pRecurso) THEN
    SELECT 'ERROR' AS estado, 'Recurso desconocido' AS mensaje;
  ELSEIF NOT EXISTS (SELECT 1 FROM acciones WHERE codigo=pAccion) THEN
    SELECT 'ERROR' AS estado, 'Acción desconocida' AS mensaje;
  ELSE
    INSERT INTO user_permisos (usersId,recurso,accion,permitido)
    VALUES (pUserId,pRecurso,pAccion,0)
    ON DUPLICATE KEY UPDATE permitido=0;
    SELECT 'OK' AS estado, pUserId AS usersId, pRecurso AS recurso, pAccion AS accion, 0 AS permitido;
  END IF;
END$$
DELIMITER ;

SET FOREIGN_KEY_CHECKS=1;
