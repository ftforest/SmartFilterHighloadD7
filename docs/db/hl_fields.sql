SELECT ENTITY_ID, FIELD_NAME, USER_TYPE_ID, MANDATORY, MULTIPLE
FROM b_user_field
WHERE ENTITY_ID IN ('HLBLOCK_6', 'HLBLOCK_7', 'HLBLOCK_8', 'HLBLOCK_9', 'HLBLOCK_10')
ORDER BY ENTITY_ID, SORT;

CREATE TABLE `b_user_field` (
                                `ID` int NOT NULL,
                                `ENTITY_ID` varchar(50) DEFAULT NULL,
                                `FIELD_NAME` varchar(50) DEFAULT NULL,
                                `USER_TYPE_ID` varchar(50) DEFAULT NULL,
                                `XML_ID` varchar(255) DEFAULT NULL,
                                `SORT` int DEFAULT NULL,
                                `MULTIPLE` char(1) NOT NULL DEFAULT 'N',
                                `MANDATORY` char(1) NOT NULL DEFAULT 'N',
                                `SHOW_FILTER` char(1) NOT NULL DEFAULT 'N',
                                `SHOW_IN_LIST` char(1) NOT NULL DEFAULT 'Y',
                                `EDIT_IN_LIST` char(1) NOT NULL DEFAULT 'Y',
                                `IS_SEARCHABLE` char(1) NOT NULL DEFAULT 'N',
                                `SETTINGS` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `b_user_field`
--

INSERT INTO `b_user_field` (`ID`, `ENTITY_ID`, `FIELD_NAME`, `USER_TYPE_ID`, `XML_ID`, `SORT`, `MULTIPLE`, `MANDATORY`, `SHOW_FILTER`, `SHOW_IN_LIST`, `EDIT_IN_LIST`, `IS_SEARCHABLE`, `SETTINGS`) VALUES
(1, 'BLOG_POST', 'UF_BLOG_POST_DOC', 'file', 'UF_BLOG_POST_DOC', 100, 'Y', 'N', 'N', 'N', 'Y', 'Y', 'a:0:{}'),
(2, 'BLOG_COMMENT', 'UF_BLOG_COMMENT_DOC', 'file', 'UF_BLOG_COMMENT_DOC', 100, 'Y', 'N', 'N', 'N', 'Y', 'Y', 'a:0:{}'),
(3, 'BLOG_POST', 'UF_BLOG_POST_URL_PRV', 'url_preview', 'UF_BLOG_POST_URL_PRV', 100, 'N', 'N', 'N', 'N', 'Y', 'Y', 'a:0:{}'),
(4, 'BLOG_COMMENT', 'UF_BLOG_COMM_URL_PRV', 'url_preview', 'UF_BLOG_COMM_URL_PRV', 100, 'N', 'N', 'N', 'N', 'Y', 'Y', 'a:0:{}'),
(5, 'BLOG_POST', 'UF_GRATITUDE', 'integer', 'UF_GRATITUDE', 100, 'N', 'N', 'N', 'N', 'Y', 'N', 'a:0:{}'),
(6, 'FORUM_MESSAGE', 'UF_FORUM_MES_URL_PRV', 'url_preview', 'UF_FORUM_MES_URL_PRV', 100, 'N', 'N', 'N', 'N', 'Y', 'N', 'a:0:{}'),
(7, 'FORUM_MESSAGE', 'UF_TASK_COMMENT_TYPE', 'integer', 'UF_TASK_COMMENT_TYPE', 100, 'N', 'N', 'N', 'N', 'Y', 'N', 'a:0:{}'),
(8, 'HLBLOCK_1', 'UF_XML_ID', 'string', 'XML_ID', 100, 'N', 'Y', 'S', 'Y', 'Y', 'N', 'a:6:{s:4:\"SIZE\";i:16;s:4:\"ROWS\";i:1;s:6:\"REGEXP\";s:15:\"/^[0-9]{1,16}$/\";s:10:\"MIN_LENGTH\";i:0;s:10:\"MAX_LENGTH\";i:0;s:13:\"DEFAULT_VALUE\";s:0:\"\";}'),
(9, 'HLBLOCK_1', 'UF_NAME', 'string', 'NAME', 200, 'N', 'Y', 'S', 'Y', 'Y', 'N', 'a:6:{s:4:\"SIZE\";i:100;s:4:\"ROWS\";i:1;s:6:\"REGEXP\";s:0:\"\";s:10:\"MIN_LENGTH\";i:1;s:10:\"MAX_LENGTH\";i:255;s:13:\"DEFAULT_VALUE\";s:0:\"\";}'),
(10, 'PRODUCT', 'UF_PRODUCT_GROUP', 'hlblock', 'MARKING_CODE_GROUP', 100, 'N', 'N', 'S', 'Y', 'Y', 'N', 'a:5:{s:7:\"DISPLAY\";s:4:\"LIST\";s:11:\"LIST_HEIGHT\";i:1;s:10:\"HLBLOCK_ID\";i:1;s:10:\"HLFIELD_ID\";i:9;s:13:\"DEFAULT_VALUE\";s:0:\"\";}'),
(41, 'HLBLOCK_6', 'UF_PRODUCT_ID', 'integer', '', 100, 'N', 'Y', 'N', 'Y', 'Y', 'N', 'a:4:{s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";i:0;s:9:\"MAX_VALUE\";i:0;s:13:\"DEFAULT_VALUE\";N;}'),
(42, 'HLBLOCK_6', 'UF_VALUE_ID', 'integer', '', 100, 'N', 'Y', 'N', 'Y', 'Y', 'N', 'a:4:{s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";i:0;s:9:\"MAX_VALUE\";i:0;s:13:\"DEFAULT_VALUE\";N;}'),
(43, 'HLBLOCK_6', 'UF_VALUE', 'string', '', 100, 'N', 'N', 'N', 'Y', 'Y', 'N', 'a:6:{s:4:\"SIZE\";i:255;s:4:\"ROWS\";i:1;s:6:\"REGEXP\";s:0:\"\";s:10:\"MIN_LENGTH\";i:0;s:10:\"MAX_LENGTH\";i:0;s:13:\"DEFAULT_VALUE\";s:0:\"\";}'),
(44, 'HLBLOCK_7', 'UF_PRODUCT_ID', 'integer', '', 100, 'N', 'Y', 'N', 'Y', 'Y', 'N', 'a:4:{s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";i:0;s:9:\"MAX_VALUE\";i:0;s:13:\"DEFAULT_VALUE\";N;}'),
(45, 'HLBLOCK_7', 'UF_VALUE_ID', 'integer', '', 100, 'N', 'Y', 'N', 'Y', 'Y', 'N', 'a:4:{s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";i:0;s:9:\"MAX_VALUE\";i:0;s:13:\"DEFAULT_VALUE\";N;}'),
(46, 'HLBLOCK_7', 'UF_VALUE', 'string', '', 100, 'N', 'N', 'N', 'Y', 'Y', 'N', 'a:6:{s:4:\"SIZE\";i:255;s:4:\"ROWS\";i:1;s:6:\"REGEXP\";s:0:\"\";s:10:\"MIN_LENGTH\";i:0;s:10:\"MAX_LENGTH\";i:0;s:13:\"DEFAULT_VALUE\";s:0:\"\";}'),
(47, 'HLBLOCK_10', 'UF_PRODUCT_ID', 'integer', '', 100, 'N', 'Y', 'N', 'Y', 'Y', 'N', 'a:4:{s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";i:0;s:9:\"MAX_VALUE\";i:0;s:13:\"DEFAULT_VALUE\";N;}'),
(48, 'HLBLOCK_10', 'UF_VALUE_ID', 'integer', '', 100, 'N', 'Y', 'N', 'Y', 'Y', 'N', 'a:4:{s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";i:0;s:9:\"MAX_VALUE\";i:0;s:13:\"DEFAULT_VALUE\";N;}'),
(49, 'HLBLOCK_10', 'UF_VALUE', 'string', '', 100, 'N', 'N', 'N', 'Y', 'Y', 'N', 'a:6:{s:4:\"SIZE\";i:255;s:4:\"ROWS\";i:1;s:6:\"REGEXP\";s:0:\"\";s:10:\"MIN_LENGTH\";i:0;s:10:\"MAX_LENGTH\";i:0;s:13:\"DEFAULT_VALUE\";s:0:\"\";}'),
(50, 'HLBLOCK_8', 'UF_PRODUCT_ID', 'integer', '', 100, 'N', 'Y', 'N', 'Y', 'Y', 'N', 'a:4:{s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";i:0;s:9:\"MAX_VALUE\";i:0;s:13:\"DEFAULT_VALUE\";N;}'),
(51, 'HLBLOCK_8', 'UF_VALUE_ID', 'integer', '', 100, 'N', 'Y', 'N', 'Y', 'Y', 'N', 'a:4:{s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";i:0;s:9:\"MAX_VALUE\";i:0;s:13:\"DEFAULT_VALUE\";N;}'),
(52, 'HLBLOCK_8', 'UF_VALUE', 'string', '', 100, 'N', 'N', 'N', 'Y', 'Y', 'N', 'a:6:{s:4:\"SIZE\";i:255;s:4:\"ROWS\";i:1;s:6:\"REGEXP\";s:0:\"\";s:10:\"MIN_LENGTH\";i:0;s:10:\"MAX_LENGTH\";i:0;s:13:\"DEFAULT_VALUE\";s:0:\"\";}'),
(53, 'HLBLOCK_9', 'UF_VALUE', 'string', '', 100, 'N', 'N', 'N', 'Y', 'Y', 'N', 'a:6:{s:4:\"SIZE\";i:255;s:4:\"ROWS\";i:1;s:6:\"REGEXP\";s:0:\"\";s:10:\"MIN_LENGTH\";i:0;s:10:\"MAX_LENGTH\";i:0;s:13:\"DEFAULT_VALUE\";s:0:\"\";}'),
(54, 'HLBLOCK_9', 'UF_PRODUCT_ID', 'integer', '', 100, 'N', 'Y', 'N', 'Y', 'Y', 'N', 'a:4:{s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";i:0;s:9:\"MAX_VALUE\";i:0;s:13:\"DEFAULT_VALUE\";N;}'),
(55, 'HLBLOCK_9', 'UF_VALUE_ID', 'integer', '', 100, 'N', 'Y', 'N', 'Y', 'Y', 'N', 'a:4:{s:4:\"SIZE\";i:20;s:9:\"MIN_VALUE\";i:0;s:9:\"MAX_VALUE\";i:0;s:13:\"DEFAULT_VALUE\";N;}');
