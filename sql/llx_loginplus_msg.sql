-- Copyright (C) 2022 ProgiSeize <contact@progiseize.fr>
--
-- This program and files/directory inner it is free software: you can 
-- redistribute it and/or modify it under the terms of the 
-- GNU Affero General Public License (AGPL) as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU AGPL for more details.
--
-- You should have received a copy of the GNU AGPL
-- along with this program.  If not, see <https://www.gnu.org/licenses/agpl-3.0.html>.



CREATE TABLE IF NOT EXISTS llx_loginplus_msg(
  rowid int NOT NULL AUTO_INCREMENT,
  label varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  message text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  destinataire json DEFAULT NULL,
  is_read json NOT NULL,
  author int NOT NULL,
  date_creation datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  author_maj int NOT NULL DEFAULT '0',
  tms datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  force_view tinyint(1) NOT NULL DEFAULT '0',
  date_expiration datetime DEFAULT NULL,
  nb_view int NOT NULL DEFAULT '0',
  entity int NOT NULL,
  PRIMARY KEY (rowid)
) ENGINE=innodb DEFAULT CHARSET=utf8;